@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h2 fw-bold">Dashboard</h1>
        <p class="text-muted">Resumen general del sistema de encuestas</p>
    </div>

    <!-- Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-gradient rounded p-3">
                            <i class="bi bi-clipboard-data text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 small">Total Encuestas</p>
                            <h3 class="mb-0">{{ $totalSurveys }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-gradient rounded p-3">
                            <i class="bi bi-check-circle text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 small">Encuestas Activas</p>
                            <h3 class="mb-0">{{ $activeSurveys }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-gradient rounded p-3">
                            <i class="bi bi-chat-dots text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 small">Total Votos</p>
                            <h3 class="mb-0">{{ $totalVotes }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-gradient rounded p-3">
                            <i class="bi bi-people text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted mb-1 small">Votantes Únicos</p>
                            <h3 class="mb-0">{{ $uniqueVoters }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de encuestas -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold">Encuestas Recientes</h5>
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Encuesta
            </a>
        </div>
        <div class="card-body p-0">
            @if($surveys->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Encuesta</th>
                                <th>Estado</th>
                                <th>Votos</th>
                                <th>Preguntas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surveys as $survey)
                                <tr>
                                    <td>
                                        <strong>{{ $survey->title }}</strong>
                                        @if($survey->description)
                                            <br><small class="text-muted">{{ Str::limit($survey->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($survey->is_active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Activa
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info">{{ $survey->votes_count }}</span></td>
                                    <td><span class="badge bg-primary">{{ $survey->questions->count() }}</span></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-outline-info" title="Ver Resultados">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                            <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay encuestas</h5>
                    <p class="text-muted">Comienza creando una nueva encuesta.</p>
                    <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Nueva Encuesta
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
