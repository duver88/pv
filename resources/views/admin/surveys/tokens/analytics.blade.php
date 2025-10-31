@extends('layouts.admin')

@section('title', 'Analíticas de Tokens - ' . $survey->title)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">
                <i class="bi bi-bar-chart-fill text-primary"></i> Analíticas de Tokens
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.tokens.index', $survey) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Tokens
        </a>
    </div>

    <!-- Tokens por Fuente y Estado -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-pie-chart-fill"></i> Distribución por Fuente y Estado
            </h5>
        </div>
        <div class="card-body">
            @if($tokensBySource->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fuente</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalTokens = $tokensBySource->sum('count');
                                $grouped = $tokensBySource->groupBy('source');
                            @endphp
                            @foreach($grouped as $source => $tokens)
                                @php
                                    $sourceTotal = $tokens->sum('count');
                                @endphp
                                @foreach($tokens as $token)
                                    <tr>
                                        @if($loop->first)
                                            <td rowspan="{{ $tokens->count() }}">
                                                <strong class="text-primary">{{ $source }}</strong>
                                                <br>
                                                <small class="text-muted">Total: {{ number_format($sourceTotal) }}</small>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if($token->status === 'pending')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock"></i> Pendiente
                                                </span>
                                            @elseif($token->status === 'used')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Usado
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Expirado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ number_format($token->count) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $percentage = $totalTokens > 0 ? ($token->count / $totalTokens) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar
                                                    @if($token->status === 'pending') bg-warning
                                                    @elseif($token->status === 'used') bg-success
                                                    @else bg-danger
                                                    @endif"
                                                     role="progressbar"
                                                     style="width: {{ $percentage }}%">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay datos de tokens disponibles.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Tokens Sospechosos (Múltiples Intentos) -->
    <div class="card shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i> Tokens Sospechosos
                <span class="badge bg-danger ms-2">{{ $suspiciousTokens->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Tokens con múltiples intentos de votación (posible compartición o fraude)</p>

            @if($suspiciousTokens->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 300px;">Token</th>
                                <th>Fuente</th>
                                <th>Campaign ID</th>
                                <th class="text-center">Intentos</th>
                                <th>Estado</th>
                                <th>Primer Uso</th>
                                <th>Último Intento</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suspiciousTokens as $token)
                                <tr class="
                                    @if($token->vote_attempts > 5) table-danger
                                    @elseif($token->vote_attempts > 3) table-warning
                                    @endif
                                ">
                                    <td>
                                        <code class="small">{{ Str::limit($token->token, 20) }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $token->source }}</span>
                                    </td>
                                    <td>
                                        @if($token->campaign_id)
                                            <span class="badge bg-info">{{ $token->campaign_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6">
                                            {{ $token->vote_attempts }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($token->status === 'used')
                                            <span class="badge bg-success">Usado</span>
                                        @elseif($token->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @else
                                            <span class="badge bg-danger">Expirado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->used_at)
                                            <small>{{ $token->used_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->last_attempt_at)
                                            <small>{{ $token->last_attempt_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->ip_address)
                                            <code class="small">{{ $token->ip_address }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>Interpretación:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Amarillo:</strong> Tokens con 3-5 intentos (posible compartición)</li>
                        <li><strong>Rojo:</strong> Tokens con más de 5 intentos (muy sospechoso)</li>
                    </ul>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                    <p class="text-success mt-3">¡Excelente! No hay tokens sospechosos detectados.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Actividad Reciente
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Últimos 50 tokens que han intentado votar</p>

            @if($recentActivity->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 250px;">Token</th>
                                <th>Fuente</th>
                                <th>Campaign ID</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Intentos</th>
                                <th>Último Intento</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $token)
                                <tr>
                                    <td>
                                        <code class="small">{{ Str::limit($token->token, 18) }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $token->source }}</span>
                                    </td>
                                    <td>
                                        @if($token->campaign_id)
                                            <span class="badge bg-info">{{ $token->campaign_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($token->status === 'used')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i>
                                            </span>
                                        @elseif($token->status === 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock"></i>
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($token->vote_attempts > 1)
                                            <span class="badge bg-warning text-dark">{{ $token->vote_attempts }}</span>
                                        @else
                                            <span class="text-muted">{{ $token->vote_attempts }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $token->last_attempt_at->diffForHumans() }}
                                        </small>
                                        <br>
                                        <small class="text-muted">{{ $token->last_attempt_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($token->ip_address)
                                            <code class="small">{{ $token->ip_address }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-activity text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay actividad reciente.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
