<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TokenRedirectController;
use Illuminate\Support\Facades\Route;

// Ruta principal - redirige al login
Route::get('/', function () {
    return redirect('/survey/encuesta-de-favorabilidad-alcaldia-de-bucaramanga-BU3aPT');
});

// Rutas de autenticación
Route::get('/HZlflogiis', [AuthController::class, 'showLogin'])->name('login');
Route::post('/HZlflogiis', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta de generación automática de tokens (/t/)
Route::get('/t/{slug}', [TokenRedirectController::class, 'redirect'])->name('token.redirect');

// Rutas públicas de encuestas
Route::get('/survey/{slug}', [SurveyController::class, 'show'])->name('surveys.show');
Route::post('/survey/{slug}/vote', [SurveyController::class, 'vote'])
    ->middleware('prevent.duplicate.vote')
    ->name('surveys.vote');
Route::get('/survey/{slug}/thanks', [SurveyController::class, 'thanks'])->name('surveys.thanks');
Route::get('/survey/{slug}/finished', [SurveyController::class, 'finished'])->name('surveys.finished');
Route::match(['get', 'post'], '/survey/{slug}/check-vote', [SurveyController::class, 'checkVote'])->name('surveys.check-vote');

// Rutas del administrador (protegidas)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de encuestas
    Route::resource('surveys', AdminSurveyController::class);
    Route::post('/surveys/{survey}/publish', [AdminSurveyController::class, 'publish'])->name('surveys.publish');
    Route::post('/surveys/{survey}/unpublish', [AdminSurveyController::class, 'unpublish'])->name('surveys.unpublish');
    Route::post('/surveys/{survey}/finish', [AdminSurveyController::class, 'finish'])->name('surveys.finish');
    Route::post('/surveys/{survey}/unfinish', [AdminSurveyController::class, 'unfinish'])->name('surveys.unfinish');
    Route::delete('/surveys/{survey}/reset', [AdminSurveyController::class, 'reset'])->name('surveys.reset');
    Route::get('/surveys/{survey}/edit-votes', [AdminSurveyController::class, 'editVotes'])->name('surveys.votes.edit');
    Route::put('/surveys/{survey}/update-votes', [AdminSurveyController::class, 'updateVotes'])->name('surveys.votes.update');

    // Gestión de tokens
    Route::get('/surveys/{survey}/tokens', [TokenController::class, 'index'])->name('surveys.tokens.index');
    Route::post('/surveys/{survey}/tokens/generate', [TokenController::class, 'generate'])->name('surveys.tokens.generate');
    Route::get('/surveys/{survey}/tokens/export', [TokenController::class, 'export'])->name('surveys.tokens.export');
    Route::get('/surveys/{survey}/tokens/analytics', [TokenController::class, 'analytics'])->name('surveys.tokens.analytics');
    Route::delete('/surveys/{survey}/tokens/{token}', [TokenController::class, 'destroy'])->name('surveys.tokens.destroy');
    Route::post('/surveys/{survey}/tokens/bulk-delete', [TokenController::class, 'bulkDelete'])->name('surveys.tokens.bulk-delete');
});

