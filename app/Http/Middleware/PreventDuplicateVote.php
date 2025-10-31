<?php

namespace App\Http\Middleware;

use App\Models\Vote;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateVote
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $surveyId = $request->route('slug');

        if (!$surveyId) {
            return $next($request);
        }

        $ipAddress = $request->ip();
        $fingerprint = $request->cookie('survey_fingerprint') ?? $request->input('fingerprint');
        $userAgent = $request->header('User-Agent');

        // Rate limiting: máximo 3 intentos de voto por IP cada 10 minutos
        $key = 'vote_attempt:' . $ipAddress;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', 'Demasiados intentos. Por favor espera ' . ceil($seconds / 60) . ' minutos.');
        }

        // Detectar bots comunes por User-Agent
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python-requests',
            'postman', 'insomnia', 'http', 'scrape', 'harvest'
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                abort(403, 'Acceso denegado.');
            }
        }

        // Verificar User-Agent vacío (sospechoso)
        if (empty($userAgent)) {
            abort(403, 'Acceso denegado.');
        }

        // Verificar honeypot (campo oculto que los bots llenan)
        if ($request->filled('website') || $request->filled('url_field')) {
            abort(403, 'Acceso denegado.');
        }

        // Verificar si ya votó por IP
        $hasVotedByIp = Vote::where('survey_id', function($query) use ($surveyId) {
            $query->select('id')
                  ->from('surveys')
                  ->where('slug', $surveyId)
                  ->limit(1);
        })
        ->where('ip_address', $ipAddress)
        ->exists();

        // Verificar si ya votó por fingerprint (si existe)
        $hasVotedByFingerprint = false;
        if ($fingerprint) {
            $hasVotedByFingerprint = Vote::where('survey_id', function($query) use ($surveyId) {
                $query->select('id')
                      ->from('surveys')
                      ->where('slug', $surveyId)
                      ->limit(1);
            })
            ->where('fingerprint', $fingerprint)
            ->exists();
        }

        if ($hasVotedByIp || $hasVotedByFingerprint) {
            RateLimiter::hit($key, 600); // 10 minutos

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Ya has votado en esta encuesta.',
                    'already_voted' => true
                ], 403);
            }

            return redirect()->back()->with('error', 'Ya has votado en esta encuesta.');
        }

        // Incrementar contador de intentos
        RateLimiter::hit($key, 600);

        return $next($request);
    }
}
