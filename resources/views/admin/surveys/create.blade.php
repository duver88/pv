@extends('layouts.admin')

@section('title', 'Crear Encuesta')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h2 fw-bold">Crear Nueva Encuesta</h1>
        <p class="text-muted">Completa el formulario para crear una encuesta</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.surveys.store') }}" enctype="multipart/form-data" id="surveyForm">
                @csrf

                <!-- Título -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">
                        <i class="bi bi-card-heading"></i> Título de la Encuesta *
                    </label>
                    <input type="text" class="form-control" name="title" id="title" required
                           placeholder="Ej: Encuesta de Satisfacción 2025">
                    @error('title')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">
                        <i class="bi bi-textarea-t"></i> Descripción
                    </label>
                    <textarea class="form-control" name="description" id="description" rows="3"
                              placeholder="Descripción breve de la encuesta (opcional)"></textarea>
                </div>

                <!-- Banner -->
                <div class="mb-4">
                    <label for="banner" class="form-label fw-semibold">
                        <i class="bi bi-image"></i> Banner/Imagen de la Encuesta
                    </label>
                    <input type="file" class="form-control" name="banner" id="banner" accept="image/*">
                    <small class="text-muted">Imagen que se muestra en la página de la encuesta</small>
                </div>

                <!-- Banner para Facebook/Open Graph -->
                <div class="mb-4">
                    <label for="og_image" class="form-label fw-semibold">
                        <i class="bi bi-facebook"></i> Imagen para Facebook (Open Graph)
                    </label>
                    <input type="file" class="form-control" name="og_image" id="og_image" accept="image/*">
                    <div class="alert alert-info mt-2 py-2">
                        <small>
                            <i class="bi bi-info-circle"></i> <strong>Recomendado:</strong> 1200x630 píxeles para Facebook, WhatsApp y redes sociales.
                            <br>Si no subes una imagen, se usará el banner principal.
                        </small>
                    </div>
                </div>

                <!-- Mostrar Resultados -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_results" id="show_results" value="1" checked>
                        <label class="form-check-label fw-semibold" for="show_results">
                            <i class="bi bi-bar-chart"></i> Mostrar resultados después de votar
                        </label>
                    </div>
                    <small class="text-muted ms-4">Si está activado, los usuarios verán los resultados en tiempo real después de votar.</small>
                </div>

                <hr class="my-4">

                <!-- Preguntas -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-question-circle"></i> Preguntas
                        </h5>
                        <button type="button" onclick="addQuestion()" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle"></i> Agregar Pregunta
                        </button>
                    </div>

                    <div id="questionsContainer">
                        <!-- Las preguntas se agregarán dinámicamente aquí -->
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Crear Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionHtml = `
        <div class="card mb-3 question-block" data-question="${questionCount}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Pregunta ${questionCount + 1}</h6>
                <button type="button" onclick="removeQuestion(${questionCount})" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Texto de la Pregunta *</label>
                    <input type="text" name="questions[${questionCount}][question_text]" required
                           class="form-control" placeholder="Escribe tu pregunta aquí">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Pregunta *</label>
                    <select name="questions[${questionCount}][question_type]" required
                            class="form-select">
                        <option value="single_choice">Opción Única (radio)</option>
                        <option value="multiple_choice">Opción Múltiple (checkbox)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">Opciones de Respuesta *</label>
                        <button type="button" onclick="addOption(${questionCount})" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus"></i> Agregar Opción
                        </button>
                    </div>
                    <div id="optionsContainer${questionCount}">
                        <!-- Opciones se agregarán aquí -->
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', questionHtml);

    // Agregar 2 opciones por defecto
    addOption(questionCount);
    addOption(questionCount);

    questionCount++;
}

function removeQuestion(index) {
    const questionBlock = document.querySelector(`[data-question="${index}"]`);
    questionBlock.remove();
}

let optionCounters = {};

function addOption(questionIndex) {
    if (!optionCounters[questionIndex]) {
        optionCounters[questionIndex] = 0;
    }

    const container = document.getElementById(`optionsContainer${questionIndex}`);
    const optionIndex = optionCounters[questionIndex];

    const optionHtml = `
        <div class="mb-2 option-row" data-option="${optionIndex}">
            <div class="input-group">
                <span class="input-group-text bg-light">${optionIndex + 1}</span>
                <input type="text" name="questions[${questionIndex}][options][]" required
                       placeholder="Texto de la opción"
                       class="form-control">
                <input type="color" name="questions[${questionIndex}][colors][]"
                       class="form-control form-control-color"
                       value="#${Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')}"
                       title="Elige un color para esta opción">
                <button type="button" onclick="removeOption(this)" class="btn btn-outline-danger">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', optionHtml);
    optionCounters[questionIndex]++;
}

function removeOption(button) {
    button.closest('.option-row').remove();
}

// Agregar primera pregunta al cargar
document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>
@endsection
