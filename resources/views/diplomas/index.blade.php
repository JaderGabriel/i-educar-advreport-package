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

        <div class="advanced-report-card" style="margin-top: 12px;">
            <span class="advanced-report-card-title"><strong>Modelos (Diploma/Certificado/Declaração)</strong></span>
            <p class="advanced-report-card-text">
                Escolha o documento e os parâmetros. Você pode clicar em <strong>Ver prévia</strong> para abrir um modal
                com o PDF em modo inline (sem download).
            </p>

            <form id="advanced-diplomas-form" method="GET" action="{{ route('advanced-reports.diplomas.pdf') }}">
                <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
                    <tbody>
                    <tr>
                        <td class="formmdtd"><span class="form">Documento</span></td>
                        <td class="formmdtd">
                            <select id="document" name="document" class="geral" style="width: 320px;">
                                <option value="diploma" {{ request('document', 'diploma') === 'diploma' ? 'selected' : '' }}>
                                    Diploma (sem rodapé)
                                </option>
                                <option value="certificate" {{ request('document') === 'certificate' ? 'selected' : '' }}>
                                    Certificado (com rodapé + QR)
                                </option>
                                <option value="declaration" {{ request('document') === 'declaration' ? 'selected' : '' }}>
                                    Declaração (com rodapé + QR)
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="formlttd"><span class="form">Tipo</span></td>
                        <td class="formlttd">
                            <select id="template" name="template" class="geral js-diploma-template" style="width: 320px;">
                                <option value="classic" {{ request('template','classic') === 'classic' ? 'selected' : '' }}>Clássico institucional</option>
                                <option value="modern" {{ request('template') === 'modern' ? 'selected' : '' }}>Moderno minimalista</option>
                                <option value="seal" {{ request('template') === 'seal' ? 'selected' : '' }}>Oficial com brasão</option>
                                <option value="bilingual" {{ request('template') === 'bilingual' ? 'selected' : '' }}>Bilíngue</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="formmdtd"><span class="form">Lado</span></td>
                        <td class="formmdtd">
                            <select id="side" name="side" class="geral js-diploma-side" style="width: 320px;">
                                <option value="front" {{ request('side','front') === 'front' ? 'selected' : '' }}>Frente</option>
                                <option value="back" {{ request('side') === 'back' ? 'selected' : '' }}>Verso</option>
                                <option value="both" {{ request('side') === 'both' ? 'selected' : '' }}>Frente e verso</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="formlttd"><span class="form">Município</span></td>
                        <td class="formlttd">
                            <input class="geral" name="municipality" value="{{ request('municipality') }}" style="width: 320px;" placeholder="Ex.: Prefeitura Municipal de ... / Secretaria de Educação">
                        </td>
                    </tr>
                    <tr>
                        <td class="formmdtd"><span class="form">Escola</span></td>
                        <td class="formmdtd">
                            <input class="geral" name="school_name" value="{{ request('school_name') }}" style="width: 320px;" placeholder="Nome da escola/unidade">
                        </td>
                    </tr>
                    <tr>
                        <td class="formlttd"><span class="form">Endereço/Contato</span></td>
                        <td class="formlttd">
                            <input class="geral" name="contact" value="{{ request('contact') }}" style="width: 520px;" placeholder="Endereço • telefone • e-mail">
                        </td>
                    </tr>
                    <tr>
                        <td class="formmdtd"><span class="form">Emissor</span></td>
                        <td class="formmdtd">
                            <input name="issuer_name" class="geral" value="{{ request('issuer_name') }}" style="width: 320px;" placeholder="Nome do responsável pela emissão">
                        </td>
                    </tr>
                    <tr>
                        <td class="formlttd"><span class="form">Cargo</span></td>
                        <td class="formlttd">
                            <input name="issuer_role" class="geral" value="{{ request('issuer_role') }}" style="width: 320px;" placeholder="Ex.: Secretário(a) escolar">
                        </td>
                    </tr>
                    <tr>
                        <td class="formmdtd"><span class="form">Cidade/UF</span></td>
                        <td class="formmdtd">
                            <input name="city_uf" class="geral" value="{{ request('city_uf') }}" style="width: 160px;" placeholder="Ex.: Saubara/BA">
                        </td>
                    </tr>
                    <tr>
                    </tbody>
                </table>

                <div style="text-align: center; margin-top: 14px;">
                    <button type="button" class="btn js-diploma-preview-open">Ver prévia</button>
                    <button type="submit" class="btn-green" formtarget="_blank" style="margin-left: 8px;">Gerar PDF</button>
                </div>
            </form>
        </div>

        <div id="advancedReportsPreviewModal" class="modal" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 9999;">
            <div style="background:#fff; width: min(1100px, 96vw); height: min(85vh, 820px); margin: 6vh auto; border-radius: 8px; overflow: hidden;">
                <div style="display:flex; justify-content: space-between; align-items:center; padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">
                    <strong>Prévia do documento</strong>
                    <button type="button" class="btn js-diploma-preview-close">Fechar</button>
                </div>
                <iframe class="js-diploma-preview-iframe" style="width: 100%; height: calc(100% - 48px); border: 0;"></iframe>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.getElementById('advanced-diplomas-form');
                var modal = document.getElementById('advancedReportsPreviewModal');
                var openBtn = document.querySelector('.js-diploma-preview-open');
                var closeBtn = document.querySelector('.js-diploma-preview-close');
                var iframe = document.querySelector('.js-diploma-preview-iframe');

                function openModalWithPreview() {
                    if (!form || !modal || !iframe) return;
                    var params = new URLSearchParams(new FormData(form));
                    params.set('preview', '1');
                    iframe.src = "{{ route('advanced-reports.diplomas.pdf') }}" + "?" + params.toString();
                    modal.style.display = 'block';
                }

                function closeModal() {
                    if (!modal || !iframe) return;
                    iframe.src = 'about:blank';
                    modal.style.display = 'none';
                }

                if (openBtn) openBtn.addEventListener('click', function (e) { e.preventDefault(); openModalWithPreview(); });
                if (closeBtn) closeBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
                if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
            });
        })();
    </script>
@endpush

