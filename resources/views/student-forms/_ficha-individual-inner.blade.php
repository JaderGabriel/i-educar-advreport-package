<h1>FICHA INDIVIDUAL</h1>
<p class="muted">Emitida com base nos registros do i-Educar.</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
])

<div class="box" style="margin-top: 12px;">
  <strong>Desempenho</strong> <span class="muted" style="font-weight: normal;">(histórico escolar oficial, se existir; caso contrário, médias e faltas do diário de classe)</span>
  <table style="margin-top: 8px;">
    <thead>
    <tr>
      <th>Componente</th>
      <th style="width: 90px;">Nota</th>
      <th style="width: 90px;">Faltas</th>
      <th style="width: 120px;">Carga horária</th>
    </tr>
    </thead>
    <tbody>
    @forelse(($extra['disciplinas'] ?? []) as $d)
      <tr>
        <td>{{ $d['nome'] ?? '' }}</td>
        <td><strong>{{ $d['nota'] ?? '—' }}</strong></td>
        <td>{{ $d['faltas'] ?? '—' }}</td>
        <td>{{ $d['carga_horaria'] ?? '—' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="4" class="muted">Sem disciplinas disponíveis para os parâmetros informados.</td>
      </tr>
    @endforelse
    </tbody>
  </table>
</div>

@include('advanced-reports::student-documents._authority-signatures')

