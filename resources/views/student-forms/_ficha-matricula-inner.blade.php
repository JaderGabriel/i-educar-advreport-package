<h1>FICHA DE MATRÍCULA</h1>
<p class="muted">Conferência de dados cadastrais e da matrícula no i-Educar (campos preenchidos conforme registros do sistema).</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
])

<div class="box" style="margin-top: 10px;">
  <strong>Identificação do(a) estudante</strong>
  <table style="margin-top: 8px;">
    <tr><th>Nome civil</th><td>{{ $matricula->aluno_nome }}</td></tr>
    @if(!empty($matricula->nome_social_aluno))
      <tr><th>Nome social</th><td>{{ $matricula->nome_social_aluno }}</td></tr>
    @endif
    <tr><th>Data de nascimento</th><td>{{ $matricula->data_nascimento_br ?? '—' }}</td></tr>
    <tr><th>Sexo (cadastro)</th><td>{{ $matricula->sexo_desc ?? '—' }}</td></tr>
    <tr><th>Naturalidade</th><td>{{ $matricula->naturalidade_txt ?? '—' }}</td></tr>
    <tr><th>Nacionalidade</th><td>{{ $matricula->nacionalidade_desc ?? '—' }}</td></tr>
    <tr><th>E-mail (cadastro)</th><td>{{ $matricula->aluno_email ?? '—' }}</td></tr>
    <tr><th>Telefone</th><td>{{ $matricula->telefone_principal ?? '—' }}</td></tr>
    <tr><th>INEP do(a) estudante</th><td>{{ $matricula->aluno_inep ?? '—' }}</td></tr>
    <tr><th>NIS (PIS/PASEP)</th><td>{{ $matricula->nis_formatado ?? '—' }}</td></tr>
    <tr><th>CPF</th><td>{{ $matricula->cpf_formatado ?? '—' }}</td></tr>
    <tr><th>RG</th><td>{{ $matricula->rg_completo ?? '—' }}</td></tr>
    <tr><th>Emancipado(a)</th><td>{{ ($matricula->emancipado ?? false) ? 'Sim' : 'Não' }}</td></tr>
  </table>
</div>

<div class="box" style="margin-top: 10px;">
  <strong>Filiação e responsável</strong>
  <table style="margin-top: 8px;">
    <tr><th>Nome do pai (cadastro)</th><td>{{ $matricula->nm_pai ?: '—' }}</td></tr>
    <tr><th>Nome da mãe (cadastro)</th><td>{{ $matricula->nm_mae ?: '—' }}</td></tr>
    <tr><th>Tipo de responsável (cadastro)</th><td>{{ $matricula->tipo_responsavel_desc ?? '—' }}</td></tr>
    <tr><th>Responsável legal (pessoa vinculada)</th><td>{{ $matricula->responsavel_legal_nome ?: '—' }}</td></tr>
  </table>
</div>

<div class="box" style="margin-top: 10px;">
  <strong>Matrícula, turma e situação</strong>
  <table style="margin-top: 8px;">
    <tr><th>Data da matrícula</th><td>{{ $matricula->data_matricula_br ?? '—' }}</td></tr>
    <tr><th>Situação da matrícula</th><td>{{ $matricula->situacao_matricula_txt ?? '—' }}</td></tr>
    <tr><th>Turno (enturmação)</th><td>{{ $matricula->turno_enturmacao ?? '—' }}</td></tr>
    <tr><th>Semestre</th><td>{{ $matricula->semestre ?? '—' }}</td></tr>
    <tr><th>Dependência administrativa</th><td>{{ ($matricula->dependencia ?? false) ? 'Sim' : 'Não' }}</td></tr>
    <tr><th>Última matrícula (flag)</th><td>{{ ($matricula->ultima_matricula_flag ?? false) ? 'Sim' : 'Não' }}</td></tr>
    <tr><th>Formando(a)</th><td>{{ ($matricula->formando ?? false) ? 'Sim' : 'Não' }}</td></tr>
    <tr><th>Observações (matrícula)</th><td>{{ $matricula->observacao_matricula ? $matricula->observacao_matricula : '—' }}</td></tr>
  </table>
</div>

<p class="muted" style="margin-top: 10px; font-size: 10px;">
  Os dados acima refletem o cadastro no i-Educar na data de emissão. Abaixo: termo resumido de imagem/voz com assinatura do responsável nesta folha
  e conferência de documentação; assinaturas finais do responsável e do emissor.
</p>

@include('advanced-reports::student-forms._ficha-matricula-termo-e-checklist-documentos', ['matricula' => $matricula])

@include('advanced-reports::student-forms._signatures-responsavel-emissor', [
  'issuerName' => $issuerName ?? null,
  'schoolInep' => $schoolInep ?? null,
  'responsavelExibicao' => $matricula->responsavel_exibicao ?? null,
])
