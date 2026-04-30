<h1 style="text-align:center;">DECLARAÇÃO DE CONCLUSÃO</h1>

@if(!empty($extra['ficha_individual']))
  <p style="text-align:center; font-weight:700; font-size:11px; margin: 4px 0 10px;">
    FICHA INDIVIDUAL — {{ $matricula->ano_letivo }}
  </p>
@endif

<p class="muted">
  Emitida com base nos registros do i-Educar para fins de comprovação.
</p>

<div class="box">
  <table>
    <tr><th>Aluno(a)</th><td>{{ $matricula->aluno_nome }}</td></tr>
    <tr><th>Matrícula interna (i-Educar)</th><td>{{ $matricula->matricula_id }}</td></tr>
    <tr><th>Ano letivo</th><td>{{ $matricula->ano_letivo }}</td></tr>
    <tr><th>Instituição</th><td>{{ $matricula->instituicao }}</td></tr>
    <tr><th>Escola</th><td>{{ $matricula->escola }}</td></tr>
    <tr><th>Curso</th><td>{{ $matricula->curso }}</td></tr>
    <tr><th>Série</th><td>{{ $matricula->serie }}</td></tr>
    <tr><th>Turma</th><td>{{ $matricula->turma }}</td></tr>
    @if(!empty($extra['historico_emissao_dias']))
      <tr><th>Prazo informado (histórico)</th><td><strong>{{ (int) $extra['historico_emissao_dias'] }} dia(s)</strong></td></tr>
    @endif
  </table>
</div>

@if(!empty($extra['disciplinas']) && is_array($extra['disciplinas']) && count($extra['disciplinas']) > 0)
  <div class="box" style="margin-top: 10px;">
    <strong>Componentes curriculares / aproveitamento</strong>
    <table style="margin-top: 8px;">
      <thead>
        <tr>
          <th>Componente</th>
          <th style="width: 90px;">Nota / conceito</th>
          <th style="width: 50px;">Faltas</th>
          <th style="width: 70px;">C. horária</th>
        </tr>
      </thead>
      <tbody>
        @foreach($extra['disciplinas'] as $d)
          <tr>
            <td>{{ $d['nome'] ?? '' }}</td>
            <td>{{ $d['nota'] ?? '—' }}</td>
            <td>{{ $d['faltas'] ?? '—' }}</td>
            <td>{{ $d['carga_horaria'] ?? '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

<p>
  Declaramos, para os devidos fins, que o(a) aluno(a) acima identificado(a) concluiu a etapa conforme registros desta unidade escolar no ano letivo informado, podendo fazer jus à emissão do histórico escolar e demais documentos legais.
</p>

@if(!empty($extra['historico_emissao_dias']))
  <p>
    <strong>Observação:</strong> foi informado prazo de <strong>{{ (int) $extra['historico_emissao_dias'] }} dia(s)</strong>
    para emissão/entrega do histórico escolar, conforme solicitado no momento da emissão desta declaração.
  </p>
@endif

@if(!empty($extra['ficha_individual']))
  @include('advanced-reports::student-documents._authority-signatures')
@else
  @include('advanced-reports::pdf._issuer-signature', [
    'issuerName' => $issuerName ?? null,
    'schoolInep' => $schoolInep ?? null,
  ])
@endif
