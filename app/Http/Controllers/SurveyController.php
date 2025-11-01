<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyToken;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Buscar la encuesta sin filtrar por is_active primero
        $survey = Survey::where('slug', $slug)
            ->with('questions.options')
            ->firstOrFail();

        // Incrementar contador de visitas usando sesión para evitar múltiples conteos
        $sessionKey = 'survey_viewed_' . $survey->id;
        if (!session()->has($sessionKey)) {
            $survey->incrementViews();
            session()->put($sessionKey, true);
        }

        // Si la encuesta está terminada, redirigir a resultados finales
        if ($survey->is_finished) {
            return redirect()->route('surveys.finished', $survey->slug);
        }

        // Si la encuesta no está activa, mostrar mensaje
        if (!$survey->is_active) {
            return view('surveys.inactive', compact('survey'));
        }

        // OBLIGAR EL USO DE TOKENS: Si no hay token en la URL, redirigir a /t/{slug}
        $tokenString = $request->query('token');
        if (!$tokenString) {
            return redirect()->route('token.redirect', $slug);
        }

        // Verificar que el token existe y obtener su estado
        $tokenRecord = SurveyToken::where('token', $tokenString)
            ->where('survey_id', $survey->id)
            ->first();

        // Si el token no existe, redirigir a /t/{slug} para generar uno nuevo
        if (!$tokenRecord) {
            return redirect()->route('token.redirect', $slug);
        }

        // Si el token ya fue usado, redirigir a página de agradecimiento
        if ($tokenRecord->status === 'used') {
            return redirect()->route('surveys.thanks', $survey->slug);
        }

        // Verificar si ya votó (solo por fingerprint para permitir múltiples usuarios en la misma red)
        $fingerprint = $request->cookie('survey_fingerprint');

        $hasVoted = false;
        if ($fingerprint) {
            $hasVoted = Vote::where('survey_id', $survey->id)
                ->where('fingerprint', $fingerprint)
                ->exists();
        }

        // Si ya votó, redirigir a la página de agradecimiento con resultados
        if ($hasVoted) {
            return redirect()->route('surveys.thanks', $survey->slug);
        }

        // Pasar el token a la vista
        $token = $tokenString;

        return view('surveys.show', compact('survey', 'hasVoted', 'token'));
    }

    public function vote(Request $request, $slug)
    {
        $survey = Survey::where('slug', $slug)->firstOrFail();

        // Verificar que la encuesta esté activa
        if (!$survey->is_active) {
            return redirect()->route('surveys.show', $slug)
                ->with('error', 'Esta encuesta no está disponible para votar en este momento.');
        }

        $validated = $request->validate([
            'answers' => 'required|array|min:1|max:50',
            'answers.*' => 'required|exists:question_options,id',
            'fingerprint' => 'required|string|max:100',
            'token' => 'nullable|string|max:100',
            'device_data' => 'nullable|array',
            'device_data.user_agent' => 'nullable|string|max:500',
            'device_data.platform' => 'nullable|string|max:100',
            'device_data.screen_resolution' => 'nullable|string|max:50',
            'device_data.hardware_concurrency' => 'nullable|integer',
        ]);

        // Validar que las respuestas correspondan a preguntas de esta encuesta
        foreach ($validated['answers'] as $questionId => $optionId) {
            $validOption = \App\Models\QuestionOption::where('id', $optionId)
                ->whereHas('question', function($q) use ($survey, $questionId) {
                    $q->where('survey_id', $survey->id)
                      ->where('id', $questionId);
                })
                ->exists();

            if (!$validOption) {
                abort(422, 'Respuesta inválida detectada.');
            }
        }

        $ipAddress = $request->ip();
        $fingerprint = $request->input('fingerprint') ?? Str::random(40);
        $deviceData = $request->input('device_data', []);
        $tokenString = $request->input('token');

        // ===================================================================
        // VALIDACIÓN DE TOKEN (si existe)
        // ===================================================================
        $tokenRecord = null;
        $isValidToken = false;

        if ($tokenString) {
            $tokenRecord = SurveyToken::where('token', $tokenString)
                ->where('survey_id', $survey->id)
                ->first();

            if ($tokenRecord) {
                $tokenRecord->incrementAttempt();

                if ($tokenRecord->isValid()) {
                    $isValidToken = true;
                }
            }
        }

        // ===================================================================
        // SISTEMA DE DETECCIÓN DE FRAUDE - SOLO POR FINGERPRINT
        // ===================================================================
        // Nota: NO bloqueamos por IP para permitir múltiples votantes en la misma red
        // (oficinas, universidades, familias con WiFi compartido, etc.)

        // VERIFICACIÓN POR FINGERPRINT EXACTO (Única validación)
        $exactMatch = Vote::where('survey_id', $survey->id)
            ->where('fingerprint', $fingerprint)
            ->exists();

        if ($exactMatch) {
            return back()->with('error', 'Ya has votado en esta encuesta. Solo se permite un voto por dispositivo.');
        }

        try {
            DB::beginTransaction();

            foreach ($validated['answers'] as $questionId => $optionId) {
                Vote::create([
                    'survey_id' => $survey->id,
                    'question_id' => $questionId,
                    'question_option_id' => $optionId,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                    'user_agent' => $deviceData['user_agent'] ?? null,
                    'platform' => $deviceData['platform'] ?? null,
                    'screen_resolution' => $deviceData['screen_resolution'] ?? null,
                    'hardware_concurrency' => $deviceData['hardware_concurrency'] ?? null,
                ]);
            }

            // Si hay un token válido, marcarlo como usado
            if ($tokenRecord && $isValidToken) {
                $tokenRecord->markAsUsed(
                    $fingerprint,
                    $request->userAgent() ?? ''
                );
            }

            DB::commit();

            // SIEMPRE mostrar mensaje de éxito al usuario
            $response = redirect()->route('surveys.thanks', $survey->slug)
                ->with('success', '¡Gracias por tu participación!');

            // Establecer MÚLTIPLES cookies para máxima persistencia
            return $response
                ->cookie('survey_fingerprint', $fingerprint, 525600) // 1 año
                ->cookie('device_fingerprint', $fingerprint, 525600) // 1 año
                ->cookie('survey_' . $survey->id . '_voted', 'true', 525600) // Cookie específica de encuesta
                ->cookie('survey_' . $survey->id . '_fp', $fingerprint, 525600); // Fingerprint por encuesta

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar tu voto: ' . $e->getMessage());
        }
    }

    public function thanks($slug)
    {
        $survey = Survey::where('slug', $slug)
            ->with(['questions.options' => function($query) {
                $query->withCount('votes');
            }])
            ->firstOrFail();

        // Si la encuesta está terminada, redirigir a la página de resultados finales
        if ($survey->is_finished) {
            return redirect()->route('surveys.finished', $survey->slug);
        }

        // Si la encuesta está despublicada/inactiva, mostrar página de inactiva
        if (!$survey->is_active) {
            return view('surveys.inactive', compact('survey'));
        }

        // Verificar si se deben mostrar resultados
        $showResults = $survey->show_results;
        $totalVotes = 0;
        $statistics = [];

        if ($showResults) {
            // Calcular estadísticas generales
            $totalVotes = Vote::where('survey_id', $survey->id)
                ->distinct('fingerprint')
                ->count('fingerprint');

            // Si no hay votos por fingerprint, contar por IP
            if ($totalVotes == 0) {
                $totalVotes = Vote::where('survey_id', $survey->id)
                    ->distinct('ip_address')
                    ->count('ip_address');
            }

            // Preparar datos para los gráficos
            foreach ($survey->questions as $question) {
                $questionStats = [
                    'question' => $question->question_text,
                    'type' => $question->question_type,
                    'options' => [],
                    'total_responses' => 0
                ];

                $totalQuestionVotes = $question->options->sum('votes_count');
                $questionStats['total_responses'] = $totalQuestionVotes;

                foreach ($question->options as $option) {
                    $percentage = $totalQuestionVotes > 0
                        ? round(($option->votes_count / $totalQuestionVotes) * 100, 1)
                        : 0;

                    $questionStats['options'][] = [
                        'text' => $option->option_text,
                        'votes' => $option->votes_count,
                        'percentage' => $percentage,
                        'color' => $option->color ?? null
                    ];
                }

                $statistics[] = $questionStats;
            }
        }

        return view('surveys.thanks', compact('survey', 'totalVotes', 'statistics', 'showResults'));
    }

    public function finished($slug)
    {
        $survey = Survey::where('slug', $slug)
            ->where('is_finished', true)
            ->with(['questions.options' => function($query) {
                $query->withCount('votes');
            }])
            ->firstOrFail();

        // Calcular estadísticas generales
        $uniqueVoters = Vote::where('survey_id', $survey->id)
            ->distinct('ip_address')
            ->count('ip_address');

        // El total de votos es la suma de votos de la primera pregunta
        $firstQuestion = $survey->questions->first();
        $totalVotes = $firstQuestion ? $firstQuestion->options->sum('votes_count') : 0;

        // Preparar datos para los gráficos
        $statistics = [];
        foreach ($survey->questions as $question) {
            $questionStats = [
                'question' => $question->question_text,
                'type' => $question->question_type,
                'options' => [],
                'total_responses' => 0
            ];

            $totalQuestionVotes = $question->options->sum('votes_count');
            $questionStats['total_responses'] = $totalQuestionVotes;

            foreach ($question->options as $option) {
                $percentage = $totalQuestionVotes > 0
                    ? round(($option->votes_count / $totalQuestionVotes) * 100, 1)
                    : 0;

                $questionStats['options'][] = [
                    'text' => $option->option_text,
                    'votes' => $option->votes_count,
                    'percentage' => $percentage,
                    'color' => $option->color ?? null
                ];
            }

            $statistics[] = $questionStats;
        }

        return view('surveys.finished', compact('survey', 'uniqueVoters', 'totalVotes', 'statistics'));
    }

    public function checkVote($slug)
    {
        $survey = Survey::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $fingerprint = request()->input('fingerprint');

        $hasVoted = false;
        if ($fingerprint) {
            $hasVoted = Vote::where('survey_id', $survey->id)
                ->where('fingerprint', $fingerprint)
                ->exists();
        }

        return response()->json(['has_voted' => $hasVoted]);
    }
}

