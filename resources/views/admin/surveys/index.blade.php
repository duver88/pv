@extends('layouts.admin')

@section('title', 'Gestión de Encuestas')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 fw-bold">Gestión de Encuestas</h1>
            <p class="text-muted">Administra todas tus encuestas</p>
        </div>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Encuesta
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        @if($surveys->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Encuesta</th>
                            <th>Estado</th>
                            <th>Votos</th>
                            <th>Link Público</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surveys as $survey)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $survey->title }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-question-circle"></i> {{ $survey->questions->count() }} preguntas
                                    </small>
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
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-graph-up"></i> {{ $survey->votes_count }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg"></i> Ver Encuesta
                                    </a>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.surveys.show', $survey) }}"
                                           class="btn btn-outline-info" title="Resultados">
                                            <i class="bi bi-bar-chart"></i>
                                        </a>
                                        <a href="{{ route('admin.surveys.edit', $survey) }}"
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($survey->is_active)
                                            <form method="POST" action="{{ route('admin.surveys.unpublish', $survey) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Despublicar">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.surveys.publish', $survey) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Publicar">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}"
                                              class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta encuesta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body text-center py-5">
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
@endsection
