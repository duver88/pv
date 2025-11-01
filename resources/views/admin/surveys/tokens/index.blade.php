@extends('layouts.admin')

@section('title', 'Gestión de Tokens - ' . $survey->title)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">
                <i class="bi bi-key-fill text-primary"></i> Gestión de Tokens
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.show', $survey) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la encuesta
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Tokens</p>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <i class="bi bi-key-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Pendientes</p>
                            <h3 class="mb-0">{{ number_format($stats['pending']) }}</h3>
                        </div>
                        <i class="bi bi-clock-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Usados</p>
                            <h3 class="mb-0">{{ number_format($stats['used']) }}</h3>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Intentos Múltiples</p>
                            <h3 class="mb-0">{{ number_format($stats['multiple_attempts']) }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- URLs para Facebook Ads -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="bi bi-facebook text-success"></i> URLs para Facebook Ads (Generación Automática)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="bi bi-lightbulb-fill text-warning"></i> <strong>Recomendado:</strong> Usa estas URLs en tus anuncios de Facebook. Cada persona que entre obtendrá automáticamente un token único.
                    </p>

                    <!-- URL Básica -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-link-45deg"></i> URL Básica (tracking orgánico)
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly
                                   value="{{ url('/t/' . $survey->public_slug) }}"
                                   id="url-basic">
                            <button class="btn btn-outline-success" type="button"
                                    onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}', 'url-basic-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-basic-btn">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL para Facebook Ads -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-facebook"></i> URL para Facebook Ads
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly
                                   value="{{ url('/t/' . $survey->public_slug) }}?source=facebook-ads"
                                   id="url-facebook">
                            <button class="btn btn-outline-primary" type="button"
                                    onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}?source=facebook-ads', 'url-facebook-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-facebook-btn">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL para Instagram Ads -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-instagram"></i> URL para Instagram Ads
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly
                                   value="{{ url('/t/' . $survey->public_slug) }}?source=instagram-ads"
                                   id="url-instagram">
                            <button class="btn btn-outline-danger" type="button"
                                    onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}?source=instagram-ads', 'url-instagram-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-instagram-btn">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL con Campaña Personalizada -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-tag-fill"></i> URL con Campaña Personalizada
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly
                                   value="{{ url('/t/' . $survey->public_slug) }}?source=facebook-ads&campaign_id=mi-campana-2025"
                                   id="url-campaign">
                            <button class="btn btn-outline-info" type="button"
                                    onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}?source=facebook-ads&campaign_id=mi-campana-2025', 'url-campaign-btn')">
                                <i class="bi bi-clipboard"></i> <span id="url-campaign-btn">Copiar</span>
                            </button>
                        </div>
                        <small class="text-muted">Personaliza <code>campaign_id=</code> con el nombre de tu campaña</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>¿Cómo funciona?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Cada visitante que entre con estos links obtiene un <strong>token único automático</strong></li>
                            <li>No necesitas pre-generar tokens manualmente</li>
                            <li>Los tokens se rastrean por <code>source</code> y <code>campaign_id</code> en Analytics</li>
                            <li>Puedes ver cuántos votos vienen de cada fuente en la sección de Analíticas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear-fill"></i> Acciones de Tokens
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Generar Tokens -->
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#generateTokensModal">
                                <i class="bi bi-plus-circle"></i> Generar Tokens Manualmente
                            </button>
                            <small class="text-muted d-block mt-2">Para QR codes, emails, etc.</small>
                        </div>

                        <!-- Exportar Tokens -->
                        <div class="col-md-4">
                            <a href="{{ route('admin.surveys.tokens.export', $survey) }}" class="btn btn-success w-100">
                                <i class="bi bi-download"></i> Exportar Tokens Pendientes
                            </a>
                            <small class="text-muted d-block mt-2">Descarga archivo .txt con URLs</small>
                        </div>

                        <!-- Ver Analíticas -->
                        <div class="col-md-4">
                            <a href="{{ route('admin.surveys.tokens.analytics', $survey) }}" class="btn btn-info w-100">
                                <i class="bi bi-bar-chart"></i> Ver Analíticas
                            </a>
                            <small class="text-muted d-block mt-2">Tokens por fuente y estado</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Eliminación Masiva -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="mb-0">
                        <i class="bi bi-trash-fill text-danger"></i> Eliminación Masiva
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Eliminar tokens por estado. Esta acción no se puede deshacer.</p>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens pendientes?')">
                                @csrf
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="bi bi-trash"></i> Eliminar Pendientes ({{ number_format($stats['pending']) }})
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens usados?')">
                                @csrf
                                <input type="hidden" name="status" value="used">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-trash"></i> Eliminar Usados ({{ number_format($stats['used']) }})
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.surveys.tokens.bulk-delete', $survey) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar TODOS los tokens expirados?')">
                                @csrf
                                <input type="hidden" name="status" value="expired">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> Eliminar Expirados ({{ number_format($stats['expired']) }})
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Tokens -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Lista de Tokens
            </h5>
        </div>
        <div class="card-body p-0">
            @if($tokens->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 300px;">Token</th>
                                <th>Fuente</th>
                                <th>Campaign ID</th>
                                <th>Estado</th>
                                <th>Intentos</th>
                                <th>Usado</th>
                                <th>Creado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokens as $token)
                                <tr>
                                    <td>
                                        <code class="text-muted small">{{ Str::limit($token->token, 20) }}</code>
                                        @if($token->status === 'pending')
                                            <button class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('{{ url('/t/' . $survey->slug . '?token=' . $token->token) }}')" title="Copiar URL">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        @endif
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
                                    <td>
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
                                    <td>
                                        @if($token->vote_attempts > 1)
                                            <span class="badge bg-danger">
                                                {{ $token->vote_attempts }} intentos
                                            </span>
                                        @else
                                            <span class="text-muted">{{ $token->vote_attempts }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->used_at)
                                            <small class="text-muted">{{ $token->used_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $token->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.surveys.tokens.destroy', [$survey, $token]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este token?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-key text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay tokens generados aún.</p>
                </div>
            @endif
        </div>
        @if($tokens->hasPages())
            <div class="card-footer">
                {{ $tokens->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Generar Tokens -->
<div class="modal fade" id="generateTokensModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.surveys.tokens.generate', $survey) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Generar Tokens
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Cantidad de Tokens *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               min="1" max="1000000" value="100" required>
                        <small class="text-muted">Máximo: 1,000,000 tokens</small>
                    </div>

                    <div class="mb-3">
                        <label for="source" class="form-label">Fuente *</label>
                        <input type="text" class="form-control" id="source" name="source"
                               placeholder="Ej: facebook-ads, whatsapp, email" required>
                        <small class="text-muted">Identifica el origen de estos tokens</small>
                    </div>

                    <div class="mb-3">
                        <label for="campaign_id" class="form-label">Campaign ID (Opcional)</label>
                        <input type="text" class="form-control" id="campaign_id" name="campaign_id"
                               placeholder="Ej: campaña-2024-Q1">
                        <small class="text-muted">ID de la campaña para analíticas</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Nota:</strong> Los tokens se generarán de forma masiva y podrás exportarlos luego.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Generar Tokens
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, buttonId) {
    navigator.clipboard.writeText(text).then(function() {
        if (buttonId) {
            const btnSpan = document.getElementById(buttonId);
            const originalText = btnSpan.textContent;
            btnSpan.textContent = '¡Copiado!';
            setTimeout(() => {
                btnSpan.textContent = originalText;
            }, 2000);
        } else {
            alert('URL copiada al portapapeles!');
        }
    }, function(err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar la URL');
    });
}
</script>
@endsection
