<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function show($slug)
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

        // Verificar si ya votó (solo por fingerprint para permitir múltiples usuarios en la misma red)
        $fingerprint = request()->cookie('survey_fingerprint');

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

        return view('surveys.show', compact('survey', 'hasVoted'));
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

        // ===================================================================
        // SISTEMA ULTRA-REFORZADO DE DETECCIÓN DE FRAUDE
        // ===================================================================

        // 1. VERIFICACIÓN POR FINGERPRINT EXACTO (Prioridad máxima)
        $exactMatch = Vote::where('survey_id', $survey->id)
            ->where('fingerprint', $fingerprint)
            ->exists();

        if ($exactMatch) {
            return back()->with('error', 'Ya has votado en esta encuesta. Solo se permite un voto por dispositivo.');
        }

        // 2. VERIFICACIÓN POR IP + CARACTERÍSTICAS DEL DISPOSITIVO (Ultra estricto)
        $votesFromSameIP = Vote::where('survey_id', $survey->id)
            ->where('ip_address', $ipAddress)
            ->get();

        if ($votesFromSameIP->isNotEmpty()) {
            $currentUserAgent = $deviceData['user_agent'] ?? '';
            $currentPlatform = $deviceData['platform'] ?? '';
            $currentResolution = $deviceData['screen_resolution'] ?? '';
            $currentCPU = $deviceData['hardware_concurrency'] ?? 0;

            foreach ($votesFromSameIP as $vote) {
                $deviceSimilarity = 0;

                // User agent similar (mismo navegador/versión)
                if ($vote->user_agent && $currentUserAgent) {
                    similar_text($vote->user_agent, $currentUserAgent, $percent);
                    if ($percent > 95) $deviceSimilarity += 50; // Casi idéntico
                    elseif ($percent > 85) $deviceSimilarity += 40; // Muy similar
                    elseif ($percent > 70) $deviceSimilarity += 25; // Similar
                }

                // Misma plataforma (Windows, Mac, Linux, Android, iOS)
                if ($vote->platform == $currentPlatform && !empty($currentPlatform)) {
                    $deviceSimilarity += 20;
                }

                // Misma resolución de pantalla (muy específico del dispositivo)
                if ($vote->screen_resolution == $currentResolution && !empty($currentResolution)) {
                    $deviceSimilarity += 25;
                }

                // Mismo número de núcleos CPU (característico del procesador)
                if ($vote->hardware_concurrency == $currentCPU && $currentCPU > 0) {
                    $deviceSimilarity += 20;
                }

                // CRITERIO MÁS ESTRICTO: Si el dispositivo es muy similar (>60%), BLOQUEAR
                // Esto significa que aunque use incógnito o borre cookies, si las características
                // del hardware son las mismas, se detecta como el mismo dispositivo
                if ($deviceSimilarity > 60) {
                    return back()->with('error',
                        'Ya se ha registrado un voto desde este dispositivo. ' .
                        'Solo se permite un voto por dispositivo, independientemente del navegador o modo de navegación utilizado. ' .
                        'Si consideras que esto es un error, contacta al administrador.'
                    );
                }
            }
        }

        // 3. VERIFICACIÓN ADICIONAL: Bloqueo por características únicas del dispositivo
        // Aunque tenga IP diferente, si el fingerprint tiene características muy específicas
        $fingerprintPrefix = substr($fingerprint, 0, 20); // Primeros 20 caracteres del hash

        $similarFingerprints = Vote::where('survey_id', $survey->id)
            ->where('fingerprint', 'LIKE', $fingerprintPrefix . '%')
            ->where('fingerprint', '!=', $fingerprint)
            ->count();

        if ($similarFingerprints > 0) {
            return back()->with('error',
                'Se ha detectado un patrón similar a un voto previo desde este dispositivo. ' .
                'Por seguridad, no se permite votar nuevamente.'
            );
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

            DB::commit();

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

        return view('surveys.thanks', compact('survey', 'totalVotes', 'statistics'));
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

