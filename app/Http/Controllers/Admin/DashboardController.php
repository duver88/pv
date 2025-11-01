<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Vote;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Solo contar votos que tienen survey_token_id (votos válidos con tokens)
        $surveys = Survey::withCount(['votes' => function ($query) {
            $query->whereNotNull('survey_token_id');
        }])->latest()->get();

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('is_active', true)->count();
        $totalVotes = Vote::whereNotNull('survey_token_id')->count();
        $uniqueVoters = Vote::whereNotNull('survey_token_id')->distinct('fingerprint')->count();

        return view('admin.dashboard', compact(
            'surveys',
            'totalSurveys',
            'activeSurveys',
            'totalVotes',
            'uniqueVoters'
        ));
    }
}
