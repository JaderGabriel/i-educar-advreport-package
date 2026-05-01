@php
$m = $matricula;
$showInstituicao = $showInstituicao ?? true;
$compactTwoCol = $compactTwoCol ?? false;
$pairs = [
    ['Aluno(a)', $m->aluno_nome],
    ['Matrícula interna (i-Educar)', (string) $m->matricula_id],
    ['Ano letivo', (string) $m->ano_letivo],
];
if ($showInstituicao) {
    $pairs[] = ['Instituição', $m->instituicao];
}
$pairs[] = ['Escola', $m->escola];
$pairs[] = ['Curso', $m->curso];
$pairs[] = ['Série', $m->serie];
$pairs[] = ['Turma', $m->turma];
if (!empty($m->data_entrada_turma_br)) {
    $pairs[] = [$entradaLabel ?? 'Início na turma', $m->data_entrada_turma_br];
}
if (!empty($m->data_fim_turma_br)) {
    $pairs[] = [$saidaLabel ?? 'Até (registro)', $m->data_fim_turma_br];
}
@endphp
<div class="box">
  @if($compactTwoCol)
    <table class="fm-kv2" style="width:100%; border-collapse:collapse; font-size:9px;">
      @for($i = 0; $i < count($pairs); $i += 2)
        <tr>
          <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $pairs[$i][0] }}</th>
          <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $pairs[$i][1] }}</td>
          @if(isset($pairs[$i + 1]))
            <th style="width:13%; padding:2px 4px; vertical-align:top;">{{ $pairs[$i + 1][0] }}</th>
            <td style="width:37%; padding:2px 4px; vertical-align:top;">{{ $pairs[$i + 1][1] }}</td>
          @else
            <th style="width:13%; padding:2px 4px;"></th>
            <td style="width:37%; padding:2px 4px;"></td>
          @endif
        </tr>
      @endfor
    </table>
  @endif

  @if(! $compactTwoCol)
    <table>
      <tr><th>Aluno(a)</th><td>{{ $m->aluno_nome }}</td></tr>
      <tr><th>Matrícula interna (i-Educar)</th><td>{{ $m->matricula_id }}</td></tr>
      <tr><th>Ano letivo</th><td>{{ $m->ano_letivo }}</td></tr>
      @if($showInstituicao)
        <tr><th>Instituição</th><td>{{ $m->instituicao }}</td></tr>
      @endif
      <tr><th>Escola</th><td>{{ $m->escola }}</td></tr>
      <tr><th>Curso</th><td>{{ $m->curso }}</td></tr>
      <tr><th>Série</th><td>{{ $m->serie }}</td></tr>
      <tr><th>Turma</th><td>{{ $m->turma }}</td></tr>
      @if(!empty($m->data_entrada_turma_br))
        <tr><th>{{ $entradaLabel ?? 'Início na turma' }}</th><td>{{ $m->data_entrada_turma_br }}</td></tr>
      @endif
      @if(!empty($m->data_fim_turma_br))
        <tr><th>{{ $saidaLabel ?? 'Até (registro)' }}</th><td>{{ $m->data_fim_turma_br }}</td></tr>
      @endif
    </table>
  @endif
</div>
