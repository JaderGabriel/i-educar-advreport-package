<h1>FICHA INDIVIDUAL</h1>
<p class="muted">Emitida com base nos registros do i-Educar.</p>

@include('advanced-reports::student-documents._matricula-data-box', [
  'matricula' => $matricula,
  'showInstituicao' => false,
])

@php($db = $extra['desempenho_boletim'] ?? null)
@php($etapasCount = (int) ($db['etapas_count'] ?? 0))
@php($boletimRows = $db['rows'] ?? [])

<div class="box" style="margin-top: 12px;">
  <strong>Desempenho e frequência</strong>
  <span class="muted" style="font-weight: normal;"> — Notas e faltas por período avaliativo, recuperação (Rc) e resultado consolidado (mesma base do boletim).</span>

  @if(!empty($boletimRows))
    @if(($db['frequencia'] ?? null) !== null && $db['frequencia'] !== '')
      <p style="margin: 8px 0 4px; font-size: 10px;"><strong>Frequência no ano</strong>: {{ $db['frequencia'] }}%</p>
    @endif

    <table style="margin-top: 6px; width: 100%; border-collapse: collapse; font-size: 8px; table-layout: fixed;">
      <thead>
      <tr>
        <th style="border: 1px solid #ccc; padding: 4px 3px; text-align: left; width: 18%;">Componente</th>
        @for($i = 1; $i <= $etapasCount; $i++)
          <th style="border: 1px solid #ccc; padding: 4px 2px; text-align: center;">Etapa {{ $i }}</th>
        @endfor
        <th style="border: 1px solid #ccc; padding: 4px 2px; text-align: center;">Rc</th>
        <th style="border: 1px solid #ccc; padding: 4px 2px; text-align: center;">Média / resultado final</th>
      </tr>
      </thead>
      <tbody>
      @foreach($boletimRows as $r)
        <tr>
          <td style="border: 1px solid #ccc; padding: 3px; vertical-align: top; font-weight: 600;">{{ $r['nome'] ?? '' }}</td>
          @for($i = 1; $i <= $etapasCount; $i++)
            @php($cell = $r['etapas'][(string) $i] ?? null)
            <td style="border: 1px solid #ccc; padding: 3px 2px; vertical-align: top; text-align: center;">
              <div><strong>{{ is_array($cell) ? ($cell['nota'] ?? '—') : ($cell ?? '—') }}</strong></div>
              @if(is_array($cell) && array_key_exists('faltas', $cell) && $cell['faltas'] !== null)
                <div style="font-size: 7px; color: #555; margin-top: 2px;">
                  F: {{ $cell['faltas'] }}@if(!empty($cell['faltas_pct'])) ({{ $cell['faltas_pct'] }}%)@endif
                </div>
              @endif
            </td>
          @endfor
          @php($rc = $r['etapas']['Rc'] ?? null)
          <td style="border: 1px solid #ccc; padding: 3px 2px; vertical-align: top; text-align: center;">
            <div><strong>{{ is_array($rc) ? ($rc['nota'] ?? '—') : ($rc ?? '—') }}</strong></div>
            @if(is_array($rc) && array_key_exists('faltas', $rc) && $rc['faltas'] !== null)
              <div style="font-size: 7px; color: #555; margin-top: 2px;">
                F: {{ $rc['faltas'] }}@if(!empty($rc['faltas_pct'])) ({{ $rc['faltas_pct'] }}%)@endif
              </div>
            @endif
          </td>
          <td style="border: 1px solid #ccc; padding: 3px 2px; vertical-align: top; text-align: center;">
            <div><strong>{{ $r['media_final'] ?? '—' }}</strong></div>
            @if(array_key_exists('faltas_total_anual', $r) && $r['faltas_total_anual'] !== null)
              <div style="font-size: 7px; color: #555; margin-top: 2px;">Faltas no ano: {{ $r['faltas_total_anual'] }}</div>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <p class="muted" style="margin-top: 6px; font-size: 7px; line-height: 1.35;">
      <strong>Rc</strong>: recuperação quando houver no diário. <strong>Média / resultado final</strong>: consolidado do diário (quando existir).
      “F” = faltas no período; percentual quando a regra de carga horária permitir calcular.
    </p>
  @else
    <p class="muted" style="margin-top: 6px; font-size: 9px;">Boletim indisponível para esta matrícula (ex.: sem turma ativa ou regra de avaliação). Exibindo, se houver, apenas o histórico escolar simplificado:</p>
    <table style="margin-top: 8px; width: 100%; border-collapse: collapse; font-size: 9px;">
      <thead>
      <tr>
        <th style="border: 1px solid #ccc; padding: 4px;">Componente</th>
        <th style="border: 1px solid #ccc; padding: 4px; width: 90px;">Nota</th>
        <th style="border: 1px solid #ccc; padding: 4px; width: 90px;">Faltas</th>
        <th style="border: 1px solid #ccc; padding: 4px; width: 120px;">Carga horária</th>
      </tr>
      </thead>
      <tbody>
      @forelse(($extra['disciplinas'] ?? []) as $d)
        <tr>
          <td style="border: 1px solid #ccc; padding: 3px;">{{ $d['nome'] ?? '' }}</td>
          <td style="border: 1px solid #ccc; padding: 3px;"><strong>{{ $d['nota'] ?? '—' }}</strong></td>
          <td style="border: 1px solid #ccc; padding: 3px;">{{ $d['faltas'] ?? '—' }}</td>
          <td style="border: 1px solid #ccc; padding: 3px;">{{ $d['carga_horaria'] ?? '—' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="muted" style="border: 1px solid #ccc; padding: 8px; text-align: center;">Sem disciplinas disponíveis para os parâmetros informados.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  @endif
</div>

@include('advanced-reports::student-documents._authority-signatures')
