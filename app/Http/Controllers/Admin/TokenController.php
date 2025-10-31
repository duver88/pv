<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyToken;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Survey $survey)
    {
        $tokens = $survey->tokens()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total' => $survey->tokens()->count(),
            'pending' => $survey->tokens()->where('status', 'pending')->count(),
            'used' => $survey->tokens()->where('status', 'used')->count(),
            'expired' => $survey->tokens()->where('status', 'expired')->count(),
            'multiple_attempts' => $survey->tokens()->where('vote_attempts', '>', 1)->count(),
        ];

        return view('admin.surveys.tokens.index', compact('survey', 'tokens', 'stats'));
    }

    public function generate(Request $request, Survey $survey)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000000',
            'source' => 'required|string|max:255',
            'campaign_id' => 'nullable|string|max:255',
        ]);

        $quantity = $request->quantity;
        $batchSize = 1000;
        $totalBatches = ceil($quantity / $batchSize);

        // Generar todos los tokens únicos primero
        $allTokens = [];
        for ($i = 0; $i < $quantity; $i++) {
            do {
                $token = \Illuminate\Support\Str::random(32);
            } while (isset($allTokens[$token]));

            $allTokens[$token] = true;
        }

        $tokenKeys = array_keys($allTokens);
        $now = now();

        // Insertar en lotes
        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $offset = $batch * $batchSize;
            $batchTokens = array_slice($tokenKeys, $offset, $batchSize);

            $insertData = [];
            foreach ($batchTokens as $token) {
                $insertData[] = [
                    'survey_id' => $survey->id,
                    'token' => $token,
                    'source' => $request->source,
                    'campaign_id' => $request->campaign_id,
                    'status' => 'pending',
                    'vote_attempts' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            SurveyToken::insert($insertData);
        }

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', "Se generaron " . number_format($quantity) . " tokens exitosamente.");
    }

    public function export(Survey $survey)
    {
        $tokens = $survey->tokens()->where('status', 'pending')->get();

        $baseUrl = url("/t/{$survey->slug}");

        $content = $tokens->map(function ($token) use ($baseUrl) {
            return $baseUrl . '?token=' . $token->token;
        })->implode("\n");

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=tokens-{$survey->slug}.txt");
    }

    public function destroy(Survey $survey, SurveyToken $token)
    {
        $token->delete();

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', 'Token eliminado exitosamente.');
    }

    public function bulkDelete(Request $request, Survey $survey)
    {
        $request->validate([
            'status' => 'required|in:pending,used,expired',
        ]);

        $deleted = $survey->tokens()->where('status', $request->status)->delete();

        return redirect()
            ->route('admin.surveys.tokens.index', $survey)
            ->with('success', "Se eliminaron {$deleted} tokens con estado '{$request->status}'.");
    }

    public function analytics(Survey $survey)
    {
        $tokensBySource = $survey->tokens()
            ->selectRaw('source, status, COUNT(*) as count')
            ->groupBy('source', 'status')
            ->get();

        $suspiciousTokens = $survey->tokens()
            ->where('vote_attempts', '>', 1)
            ->orderBy('vote_attempts', 'desc')
            ->limit(100)
            ->get();

        $recentActivity = $survey->tokens()
            ->whereNotNull('last_attempt_at')
            ->orderBy('last_attempt_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.surveys.tokens.analytics', compact(
            'survey',
            'tokensBySource',
            'suspiciousTokens',
            'recentActivity'
        ));
    }
}
