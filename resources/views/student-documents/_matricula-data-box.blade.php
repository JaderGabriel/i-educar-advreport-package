@php($m = $matricula)
@php($showInstituicao = $showInstituicao ?? true)
<div class="box">
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
</div>
