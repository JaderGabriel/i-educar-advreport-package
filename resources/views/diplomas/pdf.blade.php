@extends('advanced-reports::pdf.layout-landscape')

@section('doc_title', 'Diploma/Certificado (modelo)')
@section('doc_subtitle', 'Documento modelo para impressão')
@section('doc_year', (string) ($year ?: date('Y')))
@section('disable_footer', '1')

@section('content')
    <style>
        body {
            font-family: "Times New Roman", serif;
            color: #111827;
        }

        .diploma-page {
            width: 100%;
            min-height: 100%;
            border: 1px solid #e5e7eb;
            padding: 24px 36px;
            box-sizing: border-box;
            position: relative;
        }

        .diploma-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .diploma-entity {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .diploma-title {
            font-size: 32px;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .diploma-subtitle {
            font-size: 16px;
            color: #4b5563;
        }

        .diploma-body {
            font-size: 14px;
            line-height: 1.6;
            margin-top: 24px;
            text-align: justify;
        }

        .diploma-footer {
            position: absolute;
            left: 36px;
            right: 36px;
            bottom: 32px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .diploma-signature {
            width: 40%;
            text-align: center;
        }

        .diploma-signature-line {
            border-top: 1px solid #111827;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 12px;
        }

        .badge-side {
            position: absolute;
            top: 24px;
            right: 36px;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 9999px;
            border: 1px solid #9ca3af;
            text-transform: uppercase;
        }
    </style>

    <div class="diploma-page">
    <div class="badge-side">
        @if($side === 'back')
            Verso
        @elseif($side === 'both')
            Frente e verso
        @else
            Frente
        @endif
    </div>

    <div class="diploma-header">
        <div class="diploma-entity">
            {{ config('legacy.app.entity.name', 'Instituição de Ensino') }}
        </div>
        <div class="diploma-title">
            Diploma de Conclusão
        </div>
        <div class="diploma-subtitle">
            Modelo:
            @switch($template)
                @case('modern') Moderno minimalista @break
                @case('seal') Oficial com brasão @break
                @case('bilingual') Bilíngue @break
                @default Clássico institucional
            @endswitch
        </div>
    </div>

    <div class="diploma-body">
        <p>
            Certificamos que <strong>ALUNO EXEMPLO</strong>, regularmente matriculado no curso
            <strong>{{ $course ?: '__________' }}</strong>, turma <strong>{{ $class ?: '__________' }}</strong>,
            concluiu, no ano letivo de <strong>{{ $year ?: '__________' }}</strong>, todas as exigências legais e
            regimentais para a conclusão dos estudos, fazendo jus ao presente diploma.
        </p>

        <p>
            Este diploma é emitido para fins de comprovação de escolaridade, em conformidade com a legislação
            educacional vigente.
        </p>

        @if($enrollment)
            <p>
                Matrícula selecionada: <strong>{{ $enrollment }}</strong>.
            </p>
        @endif
    </div>

    <div class="diploma-footer">
        <div class="diploma-signature">
            <div class="diploma-signature-line">
                Diretor(a)
            </div>
        </div>

        <div class="diploma-signature">
            <div class="diploma-signature-line">
                Secretário(a) Escolar
            </div>
        </div>
    </div>
</div>
@endsection

