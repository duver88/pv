@extends('layouts.admin')

@section('title', 'Resultados - ' . $survey->title)

@section('content')
<div class="container-fluid px-3 px-lg-4 py-4">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="h2 fw-bold mb-2">{{ $survey->title }}</h1>
                @if($survey->description)
                    <p class="text-muted mb-0">{{ $survey->description }}</p>
                @endif
            </div>
            <div class="btn-group flex-wrap" role="group">
                <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-link-45deg"></i> <span class="d-none d-md-inline">Ver Pública</span>
                </a>
                <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Editar</span>
                </a>
                <a href="{{ route('admin.surveys.tokens.index', $survey) }}" class="btn btn-info">
                    <i class="bi bi-key-fill"></i> <span class="d-none d-md-inline">Tokens</span>
                </a>
                <a href="{{ route('admin.surveys.votes.edit', $survey) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> <span class="d-none d-md-inline">Editar Votos</span>
                </a>
                @if($survey->is_finished)
                    <form action="{{ route('admin.surveys.unfinish', $survey) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-arrow-counterclockwise"></i> <span class="d-none d-md-inline">Reactivar</span>
                        </button>
                    </form>
                    <a href="{{ route('surveys.finished', $survey->public_slug) }}" target="_blank" class="btn btn-dark">
                        <i class="bi bi-eye-fill"></i> <span class="d-none d-md-inline">Ver Resultados</span>
                    </a>
                @else
                    <button type="button" class="btn btn-dark" onclick="confirmFinish()">
                        <i class="bi bi-check-circle"></i> <span class="d-none d-md-inline">Terminar</span>
                    </button>
                @endif
                <button type="button" class="btn btn-danger" onclick="confirmReset()">
                    <i class="bi bi-arrow-clockwise"></i> <span class="d-none d-md-inline">Reset</span>
                </button>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Volver</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas Detalladas -->
    <div class="row g-4 mb-4">
        <!-- Lado Izquierdo: VISITAS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-eye-fill"></i> Visitas Totales
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-3 fw-bold text-primary mb-2">
                            {{ number_format($survey->views_count ?? 0) }}
                        </div>
                        <p class="text-muted mb-0">Personas han visto esta encuesta</p>
                    </div>

                    <!-- Desglose de visitas -->
                    <div class="desglose-box p-3 rounded-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-info-circle"></i> Información Detallada
                        </h6>

                        <div class="row g-3 text-center">
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-eye"></i> Total de vistas de página
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($survey->views_count ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            @if(($survey->views_count ?? 0) > 0)
                            <div class="col-6">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="text-success">
                                        <i class="bi bi-check-circle-fill" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-4 mt-2">{{ number_format($uniqueVoters) }}</div>
                                    <small class="text-muted">Votaron</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="text-warning">
                                        <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="fw-bold fs-4 mt-2">{{ number_format(($survey->views_count ?? 0) - $uniqueVoters) }}</div>
                                    <small class="text-muted">No votaron</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Derecho: VOTANTES -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold text-success">
                        <i class="bi bi-people-fill"></i> Votantes
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-3 fw-bold text-success mb-2">
                            {{ number_format($uniqueVoters) }}
                        </div>
                        <p class="text-muted mb-0">Personas han completado la encuesta</p>
                    </div>

                    <!-- Desglose de votos -->
                    <div class="desglose-box p-3 rounded-3" style="background: linear-gradient(135deg, rgba(67, 233, 123, 0.1) 0%, rgba(56, 249, 215, 0.1) 100%);">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-graph-up"></i> Desglose de Datos
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-person-check-fill text-success"></i> Votantes únicos
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($uniqueVoters) }}</span>
                                    </div>
                                    <small class="text-muted">Por IP/Fingerprint único</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-chat-dots-fill text-primary"></i> Total de respuestas
                                        </span>
                                        <span class="fw-bold fs-5">{{ number_format($totalVotes) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $survey->questions->count() }} pregunta(s) × {{ $uniqueVoters }} votantes</small>
                                </div>
                            </div>

                            @if(($survey->views_count ?? 0) > 0)
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-percent text-info"></i> Tasa de conversión
                                        </span>
                                        <span class="fw-bold fs-5 text-success">
                                            {{ number_format(($uniqueVoters / $survey->views_count) * 100, 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ min(($uniqueVoters / $survey->views_count) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Link para compartir -->
    <!-- Link con generación automática de tokens (ÚNICO VÁLIDO) -->
    <div class="alert alert-success d-flex align-items-center flex-wrap gap-2 mb-4" role="alert">
        <i class="bi bi-key-fill"></i>
        <div class="flex-grow-1">
            <strong>Link Público de la Encuesta:</strong>
            <code class="ms-2 d-inline-block text-break">{{ url('/t/' . $survey->public_slug) }}</code>
            <div class="mt-2">
                <small class="text-muted">
                    <i class="bi bi-lightbulb"></i> Este link genera un token único para cada persona automáticamente.
                    Puedes agregar: <code>?source=facebook-ads</code> o <code>?source=whatsapp&campaign_id=verano-2025</code>
                </small>
            </div>
        </div>
        <button class="btn btn-sm btn-success" onclick="copyToClipboard('{{ url('/t/' . $survey->public_slug) }}')">
            <i class="bi bi-clipboard"></i> Copiar
        </button>
    </div>

    <!-- Resultados por pregunta -->
    @foreach($questionStats as $index => $stat)
        <div class="card border-0 shadow-sm mb-4 question-card">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                          style="width: 40px; height: 40px; font-size: 1.1rem;">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-semibold">{{ $stat['question'] }}</h5>
                        <small class="text-muted">
                            <i class="bi bi-graph-up"></i> Total de votos: <strong>{{ number_format($stat['total_votes']) }}</strong>
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 p-lg-4">
                <div class="row">
                    <!-- Gráfico de pastel (solo en desktop) -->
                    <div class="col-lg-5 d-none d-lg-block mb-4 mb-lg-0">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="chart-{{ $index }}"></canvas>
                        </div>
                    </div>

                    <!-- Lista de opciones con barras -->
                    <div class="col-12 col-lg-7">
                        @foreach($stat['options'] as $optIndex => $option)
                            <div class="option-stat mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-medium text-dark">{{ $option['text'] }}</span>
                                    <span class="badge bg-primary rounded-pill">
                                        {{ number_format($option['votes']) }} votos ({{ $option['percentage'] }}%)
                                    </span>
                                </div>
                                <div class="progress" style="height: 28px;">
                                    <div class="progress-bar bg-primary bg-gradient position-relative"
                                         role="progressbar"
                                         style="width: {{ $option['percentage'] }}%"
                                         aria-valuenow="{{ $option['percentage'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        @if($option['percentage'] > 10)
                                            <strong>{{ $option['percentage'] }}%</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if(count($questionStats) === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-graph-down text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Aún no hay votos</h5>
                <p class="text-muted">Comparte el link de la encuesta para comenzar a recibir respuestas.</p>
                <a href="{{ url('/t/' . $survey->public_slug) }}" target="_blank" class="btn btn-primary mt-3">
                    <i class="bi bi-link-45deg"></i> Ver Encuesta Pública
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Formulario oculto para reset -->
<form id="resetForm" method="POST" action="{{ route('admin.surveys.reset', $survey) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
/* Animaciones y efectos */
.card {
    animation: fadeInUp 0.5s ease-out;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
}

.question-card {
    animation: fadeInUp 0.6s ease-out;
    transition: all 0.3s ease;
}

.question-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
}

.option-stat {
    transition: all 0.3s ease;
    padding: 10px;
    border-radius: 8px;
}

.option-stat:hover {
    background: rgba(13, 110, 253, 0.05);
    transform: translateX(5px);
}

.progress-bar {
    transition: width 1.5s ease-out;
}

.desglose-box {
    animation: fadeIn 0.8s ease-out 0.3s both;
}

.desglose-box .bg-white {
    transition: all 0.3s ease;
}

.desglose-box .bg-white:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Números grandes */
.display-3 {
    line-height: 1.2;
}

/* Responsive */
@media (max-width: 992px) {
    .display-3 {
        font-size: 3rem !important;
    }
}

@media (max-width: 768px) {
    .display-3 {
        font-size: 2.5rem !important;
    }

    .card-body {
        padding: 1.5rem !important;
    }

    .desglose-box {
        padding: 1rem !important;
    }

    .desglose-box h6 {
        font-size: 0.9rem !important;
    }
}

@media (max-width: 576px) {
    .btn-group {
        width: 100%;
    }

    .btn-group .btn {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }

    .display-3 {
        font-size: 2rem !important;
    }

    .fs-4 {
        font-size: 1.1rem !important;
    }

    .fs-5 {
        font-size: 1rem !important;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('¡Link copiado al portapapeles!');
    });
}

function confirmFinish() {
    if (confirm(`📊 ¿Terminar esta encuesta?\n\n` +
                `Esta acción hará lo siguiente:\n` +
                `• La encuesta dejará de aceptar votos\n` +
                `• Se mostrará una página de resultados finales\n` +
                `• Los usuarios serán redirigidos a ver los resultados\n\n` +
                `Puedes reactivarla después si lo necesitas.\n\n` +
                `¿Continuar?`)) {

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.surveys.finish", $survey) }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmReset() {
    const totalVotes = {{ $totalVotes }};
    const uniqueVoters = {{ $uniqueVoters }};

    if (confirm(`⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE\n\n` +
                `Se eliminarán:\n` +
                `• ${totalVotes} votos totales\n` +
                `• ${uniqueVoters} votantes únicos\n` +
                `• Todos los resultados de esta encuesta\n\n` +
                `¿Estás SEGURO de que deseas continuar?`)) {

        // Segunda confirmación
        if (confirm(`🔴 ÚLTIMA CONFIRMACIÓN\n\n` +
                    `Escribe "RESET" en la próxima ventana para confirmar`)) {

            const confirmation = prompt('Escribe "RESET" para confirmar (en mayúsculas):');

            if (confirmation === 'RESET') {
                const form = document.getElementById('resetForm');
                form.method = 'POST';
                form.submit();
            } else {
                alert('❌ Operación cancelada. El texto no coincide.');
            }
        }
    }
}

// Crear gráficos de pastel (solo en desktop)
@if(count($questionStats) > 0)
document.addEventListener('DOMContentLoaded', function() {
    const isMobile = window.innerWidth < 992;

    if (!isMobile) {
        const questionStats = @json($questionStats);
        const defaultColors = [
            '#667eea', '#f093fb', '#4facfe', '#43e97b',
            '#fa709a', '#30cfd0', '#a8edea', '#fed6e3'
        ];

        questionStats.forEach((question, index) => {
            const ctx = document.getElementById(`chart-${index}`);
            if (!ctx) return;

            const labels = question.options.map(opt => opt.text);
            const data = question.options.map(opt => opt.percentage);
            const colors = defaultColors.slice(0, question.options.length);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    family: "'Inter', system-ui, sans-serif",
                                    weight: '500'
                                },
                                color: '#374151',
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500
                    }
                }
            });
        });
    }
});
@endif
</script>
@endsection
