@extends('layout.default')

@push('styles')
    @if (class_exists('Asset'))
        <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
    @else
        <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
    @endif
    <style>
        .diploma-preview {
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            padding: 16px;
            background: #f9fafb;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .diploma-preview-canvas {
            width: 100%;
            max-width: 520px;
            min-height: 180px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 16px 24px;
            background: linear-gradient(135deg, #ffffff, #f3f4ff);
            box-shadow: 0 8px 20px rgba(148, 163, 184, 0.35);
        }

        .diploma-preview-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 12px;
        }

        .diploma-preview-entity {
            font-size: 11px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .diploma-preview-title {
            font-size: 20px;
            font-weight: 600;
            margin-top: 4px;
            color: #111827;
        }

        .diploma-preview-body {
            font-size: 13px;
            color: #4b5563;
        }
    </style>
@endpush

@section('content')
    <div class="advanced-report">
        <div class="advanced-report-header">
            <h1 class="advanced-report-title">Relatórios Avançados - Diplomas</h1>
            <p class="advanced-report-subtitle">
                Gere diplomas em diferentes modelos de frente e verso, com pré-visualização do layout selecionado.
            </p>
        </div>

        @include('advanced-reports::partials.filters', [
            'route' => route('advanced-reports.diplomas.index'),
            'cursos' => $cursos,
            'cursoId' => $cursoId ?? null,
            'withCharts' => false,
            'explainTitle' => 'Filtros para emissão de diplomas',
            'explainText' => 'Escolha ano letivo, instituição, escola e curso para localizar as matrículas elegíveis à emissão de diplomas.',
            'explainDictionary' => 'Ano = ano letivo da matrícula; Escola = unidade em que o aluno está matriculado; Curso = etapa/modalidade concluída.'
        ])

        <div class="row">
            <div class="col-md-4">
                <form id="advanced-diplomas-form"
                      method="GET"
                      action="{{ route('advanced-reports.diplomas.pdf') }}"
                      class="form-horizontal">
                    <div class="advanced-report-card">
                        <span class="advanced-report-card-title"><strong>Modelo de diploma</strong></span>
                        <p class="advanced-report-card-text">
                            Selecione o modelo visual e o lado a ser impresso. A pré-visualização ao lado é atualizada
                            conforme a escolha.
                        </p>

                        <div class="form-group">
                            <label for="document" class="col-md-4 control-label">Documento</label>
                            <div class="col-md-8">
                                <select id="document" name="document" class="form-control">
                                    <option value="diploma" {{ request('document', 'diploma') === 'diploma' ? 'selected' : '' }}>
                                        Diploma (sem rodapé)
                                    </option>
                                    <option value="certificate" {{ request('document') === 'certificate' ? 'selected' : '' }}>
                                        Certificado (com rodapé)
                                    </option>
                                    <option value="declaration" {{ request('document') === 'declaration' ? 'selected' : '' }}>
                                        Declaração (com rodapé)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="template" class="col-md-4 control-label">Tipo</label>
                            <div class="col-md-8">
                                <select id="template" name="template" class="form-control js-diploma-template">
                                    <option value="classic" {{ request('template') === 'classic' ? 'selected' : '' }}>
                                        Clássico institucional
                                    </option>
                                    <option value="modern" {{ request('template') === 'modern' ? 'selected' : '' }}>
                                        Moderno minimalista
                                    </option>
                                    <option value="seal" {{ request('template') === 'seal' ? 'selected' : '' }}>
                                        Oficial com brasão
                                    </option>
                                    <option value="bilingual" {{ request('template') === 'bilingual' ? 'selected' : '' }}>
                                        Bilíngue
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="side" class="col-md-4 control-label">Lado</label>
                            <div class="col-md-8">
                                <select id="side" name="side" class="form-control js-diploma-side">
                                    <option value="front" {{ request('side', 'front') === 'front' ? 'selected' : '' }}>
                                        Frente
                                    </option>
                                    <option value="back" {{ request('side') === 'back' ? 'selected' : '' }}>
                                        Verso
                                    </option>
                                    <option value="both" {{ request('side') === 'both' ? 'selected' : '' }}>
                                        Frente e verso
                                    </option>
                                </select>
                            </div>
                        </div>

                        <hr style="margin: 12px 0;">

                        <div class="form-group">
                            <label for="issuer_name" class="col-md-4 control-label">Emissor</label>
                            <div class="col-md-8">
                                <input id="issuer_name" name="issuer_name" class="form-control" value="{{ request('issuer_name') }}" placeholder="Nome do responsável pela emissão">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="issuer_role" class="col-md-4 control-label">Cargo</label>
                            <div class="col-md-8">
                                <input id="issuer_role" name="issuer_role" class="form-control" value="{{ request('issuer_role') }}" placeholder="Ex.: Secretário(a) escolar">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="city_uf" class="col-md-4 control-label">Cidade/UF</label>
                            <div class="col-md-8">
                                <input id="city_uf" name="city_uf" class="form-control" value="{{ request('city_uf') }}" placeholder="Ex.: Saubara/BA">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Livro/Folha/Registro</label>
                            <div class="col-md-8" style="display:flex;gap:8px;">
                                <input name="book" class="form-control" value="{{ request('book') }}" placeholder="Livro" style="width: 33%;">
                                <input name="page" class="form-control" value="{{ request('page') }}" placeholder="Folha" style="width: 33%;">
                                <input name="record" class="form-control" value="{{ request('record') }}" placeholder="Registro" style="width: 34%;">
                            </div>
                        </div>

                        <div class="text-right" style="margin-top: 12px;">
                            <button type="button" class="btn btn-default js-diploma-refresh">
                                Atualizar pré-visualização
                            </button>

                            <button type="submit" class="btn btn-primary" formtarget="_blank" style="margin-left: 8px;">
                                Gerar PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-8">
                <div class="advanced-report-card">
                    <span class="advanced-report-card-title"><strong>Pré-visualização do modelo selecionado</strong></span>
                    <p class="advanced-report-card-text">
                        Esta prévia é ilustrativa e usa dados fictícios apenas para demonstrar o layout. Na geração do
                        relatório, serão utilizados os dados reais de aluno, escola, curso e legislação.
                    </p>

                    <div class="diploma-preview js-diploma-preview" data-template="{{ request('template', 'classic') }}">
                        <div class="diploma-preview-canvas diploma-preview-canvas--classic">
                            <div class="diploma-preview-header">
                                <span class="diploma-preview-entity js-diploma-entity">Instituição de Ensino</span>
                                <span class="diploma-preview-title js-diploma-title">Diploma de Conclusão</span>
                            </div>
                            <div class="diploma-preview-body js-diploma-body">
                                <p>
                                    Modelo <strong>Clássico institucional</strong>, com brasão centralizado no topo,
                                    tipografia tradicional e ênfase na legislação vigente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="text-center advanced-report-card-text" style="margin-top: 15px;">
                        Outros modelos (moderno, oficial com brasão, bilíngue) serão aplicados conforme a seleção na
                        próxima etapa de implementação.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function updateDiplomaPreview() {
                var templateSelect = document.getElementById('template');
                var sideSelect = document.getElementById('side');
                var canvas = document.querySelector('.js-diploma-preview .diploma-preview-canvas');
                var body = document.querySelector('.js-diploma-preview .js-diploma-body');

                if (!templateSelect || !sideSelect || !canvas || !body) {
                    return;
                }

                var template = templateSelect.value || 'classic';
                var side = sideSelect.value || 'front';

                canvas.className = 'diploma-preview-canvas diploma-preview-canvas--' + template + ' diploma-preview-canvas--' + side;

                var descriptions = {
                    'classic': 'Modelo Clássico institucional, com brasão centralizado e tipografia tradicional.',
                    'modern': 'Modelo Moderno minimalista, com foco em tipografia limpa e áreas em branco.',
                    'seal': 'Modelo Oficial com brasão em destaque, ideal para diplomas formais.',
                    'bilingual': 'Modelo Bilíngue, exibindo os textos em português e em um segundo idioma.'
                };

                var sideSuffix = {
                    'front': 'Prévia do lado frontal do diploma.',
                    'back': 'Prévia do verso, com espaço para histórico e registros adicionais.',
                    'both': 'Representação resumida de frente e verso em um único layout.'
                };

                var html = ''
                    + '<p>'
                    + descriptions[template]
                    + '</p>'
                    + '<p style="margin-top: 8px; font-size: 11px; color: #6b7280;">'
                    + sideSuffix[side]
                    + '</p>';

                body.innerHTML = html;
            }

            document.addEventListener('DOMContentLoaded', function () {
                var templateSelect = document.getElementById('template');
                var sideSelect = document.getElementById('side');
                var refreshButton = document.querySelector('.js-diploma-refresh');

                if (templateSelect) {
                    templateSelect.addEventListener('change', updateDiplomaPreview);
                }

                if (sideSelect) {
                    sideSelect.addEventListener('change', updateDiplomaPreview);
                }

                if (refreshButton) {
                    refreshButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        updateDiplomaPreview();
                    });
                }

                updateDiplomaPreview();
            });
        })();
    </script>
@endpush

