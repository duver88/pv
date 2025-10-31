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
        $surveys = Survey::withCount('votes')
            ->latest()
            ->get();

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('is_active', true)->count();
        $totalVotes = Vote::count();
        $uniqueVoters = Vote::distinct('ip_address')->count();

        return view('admin.dashboard', compact(
            'surveys',
            'totalSurveys',
            'activeSurveys',
            'totalVotes',
            'uniqueVoters'
        ));
    }
}
