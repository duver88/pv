<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyToken;
use Illuminate\Http\Request;

class TokenRedirectController extends Controller
{
    public function redirect(Request $request, string $slug)
    {
        $survey = Survey::where('slug', $slug)->firstOrFail();

        // Si ya tiene un token en la URL, redirigir directamente a la encuesta
        if ($request->has('token')) {
            return redirect()->route('surveys.show', ['slug' => $slug, 'token' => $request->token]);
        }

        // Generar un nuevo token automáticamente
        $token = SurveyToken::create([
            'survey_id' => $survey->id,
            'token' => SurveyToken::generateToken(),
            'source' => $request->query('source', 'organic'),
            'campaign_id' => $request->query('campaign_id'),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Redirigir a la encuesta con el token generado
        return redirect()->route('surveys.show', [
            'slug' => $slug,
            'token' => $token->token
        ]);
    }
}
