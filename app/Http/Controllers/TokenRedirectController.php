<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenRedirectController extends Controller
{
    public function redirect(Request $request, string $publicSlug)
    {
        // Buscar encuesta por public_slug (ofuscado)
        $survey = Survey::where('public_slug', $publicSlug)->firstOrFail();

        // ========================================================================
        // SISTEMA DE POOL DE TOKENS: Usar tokens pre-generados del pool
        // ========================================================================

        // Intentar asignar un token disponible del pool (con bloqueo para evitar condiciones de carrera)
        DB::beginTransaction();

        try {
            // Buscar un token pendiente disponible (lockForUpdate para evitar asignación duplicada)
            $token = SurveyToken::where('survey_id', $survey->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            // Si NO hay tokens disponibles, redirigir sin token (será rechazado)
            if (!$token) {
                DB::commit();

                // Redirigir a la encuesta sin token - mostrará mensaje de agradecimiento
                // pero no permitirá votar
                return redirect()->route('surveys.show', [
                    'publicSlug' => $publicSlug
                ])->with('warning', 'No hay tokens disponibles en este momento. Por favor, intenta más tarde.');
            }

            // OPCIONAL: Actualizar información del token (source, campaign_id) si se proporcionan
            // Esto permite rastrear de dónde viene cada visitante
            $source = $request->query('source');
            $campaignId = $request->query('campaign_id');

            if ($source && $token->source === 'manual') {
                $token->source = $source;
            }
            if ($campaignId && !$token->campaign_id) {
                $token->campaign_id = $campaignId;
            }

            $token->user_agent = $request->userAgent();
            $token->save();

            DB::commit();

            // Redirigir a la encuesta con el token asignado del pool
            return redirect()->route('surveys.show', [
                'publicSlug' => $publicSlug,
                'token' => $token->token
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // En caso de error, redirigir sin token
            return redirect()->route('surveys.show', [
                'publicSlug' => $publicSlug
            ])->with('error', 'Ocurrió un error al procesar tu solicitud.');
        }
    }
}
