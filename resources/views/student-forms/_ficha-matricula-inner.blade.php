<style>
  .fm-kv2 { width: 100%; border-collapse: collapse; font-size: 9px; }
  .fm-kv2 th, .fm-kv2 td { border: 1px solid #ccc; }
  .fm-kv2 th { background: #f2f2f2; font-weight: 600; text-align: left; }
  .fm-section { margin-top: 8px; }
  .fm-section > strong { font-size: 10px; }
</style>

<h1>FICHA DE MATRÍCULA</h1>
<p class="muted" style="font-size: 9px; margin: 4px 0 6px;">Conferência de dados cadastrais e da matrícula no i-Educar (campos conforme registros do sistema).</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
  'compactTwoCol' => true,
])

@php
  $ident = [['Nome civil', $matricula->aluno_nome]];
  if (!empty($matricula->nome_social_aluno)) {
    $ident[] = ['Nome social', $matricula->nome_social_aluno];
  }
  $ident = array_merge($ident, [
    ['Data de nascimento', $matricula->data_nascimento_br ?? '—'],
    ['Sexo (cadastro)', $matricula->sexo_desc ?? '—'],
    ['Naturalidade', $matricula->naturalidade_txt ?? '—'],
    ['Nacionalidade', $matricula->nacionalidade_desc ?? '—'],
    ['E-mail (cadastro)', $matricula->aluno_email ?? '—'],
    ['Telefone', $matricula->telefone_principal ?? '—'],
    ['INEP do(a) estudante', $matricula->aluno_inep ?? '—'],
    ['NIS (PIS/PASEP)', $matricula->nis_formatado ?? '—'],
    ['CPF', $matricula->cpf_formatado ?? '—'],
    ['RG', $matricula->rg_completo ?? '—'],
    ['Emancipado(a)', ($matricula->emancipado ?? false) ? 'Sim' : 'Não'],
  ]);

  $filiacao = [
    ['Nome do pai (cadastro)', $matricula->nm_pai ?: '—'],
    ['Nome da mãe (cadastro)', $matricula->nm_mae ?: '—'],
    ['Tipo de responsável (cadastro)', $matricula->tipo_responsavel_desc ?? '—'],
    ['Responsável legal (pessoa vinculada)', $matricula->responsavel_legal_nome ?: '—'],
  ];

  $matTurma = [
    ['Data da matrícula', $matricula->data_matricula_br ?? '—'],
    ['Situação da matrícula', $matricula->situacao_matricula_txt ?? '—'],
    ['Turno (enturmação)', $matricula->turno_enturmacao ?? '—'],
    ['Semestre', $matricula->semestre ?? '—'],
    ['Dependência administrativa', ($matricula->dependencia ?? false) ? 'Sim' : 'Não'],
    ['Última matrícula (flag)', ($matricula->ultima_matricula_flag ?? false) ? 'Sim' : 'Não'],
    ['Formando(a)', ($matricula->formando ?? false) ? 'Sim' : 'Não'],
    ['Observações (matrícula)', $matricula->observacao_matricula ? $matricula->observacao_matricula : '—'],
  ];
@endphp

<div class="box fm-section">
  <strong>Identificação do(a) estudante</strong>
  <table class="fm-kv2" style="margin-top: 4px;">
    @for($i = 0; $i < count($ident); $i += 2)
      <tr>
        <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $ident[$i][0] }}</th>
        <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $ident[$i][1] }}</td>
        @if(isset($ident[$i + 1]))
          <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $ident[$i + 1][0] }}</th>
          <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $ident[$i + 1][1] }}</td>
        @else
          <th style="width:13%; padding:2px 4px;"></th>
          <td style="width:37%; padding:2px 4px;"></td>
        @endif
      </tr>
    @endfor
  </table>
</div>

<div class="box fm-section">
  <strong>Filiação e responsável</strong>
  <table class="fm-kv2" style="margin-top: 4px;">
    @for($i = 0; $i < count($filiacao); $i += 2)
      <tr>
        <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $filiacao[$i][0] }}</th>
        <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $filiacao[$i][1] }}</td>
        @if(isset($filiacao[$i + 1]))
          <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $filiacao[$i + 1][0] }}</th>
          <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $filiacao[$i + 1][1] }}</td>
        @else
          <th style="width:13%; padding:2px 4px;"></th>
          <td style="width:37%; padding:2px 4px;"></td>
        @endif
      </tr>
    @endfor
  </table>
</div>

<div class="box fm-section">
  <strong>Matrícula, turma e situação</strong>
  <table class="fm-kv2" style="margin-top: 4px;">
    @for($i = 0; $i < count($matTurma); $i += 2)
      <tr>
        <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $matTurma[$i][0] }}</th>
        <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $matTurma[$i][1] }}</td>
        @if(isset($matTurma[$i + 1]))
          <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $matTurma[$i + 1][0] }}</th>
          <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $matTurma[$i + 1][1] }}</td>
        @else
          <th style="width:13%; padding:2px 4px;"></th>
          <td style="width:37%; padding:2px 4px;"></td>
        @endif
      </tr>
    @endfor
  </table>
</div>

<p class="muted" style="margin-top: 6px; font-size: 8px; line-height: 1.35;">
  Os dados acima refletem o cadastro na data de emissão. Abaixo: termo resumido de imagem/voz, checklist de documentação e assinaturas.
</p>

@include('advanced-reports::student-forms._ficha-matricula-termo-e-checklist-documentos', ['matricula' => $matricula])

@include('advanced-reports::student-forms._signatures-responsavel-emissor', [
  'issuerName' => $issuerName ?? null,
  'schoolInep' => $schoolInep ?? null,
  'responsavelExibicao' => $matricula->responsavel_exibicao ?? null,
])
