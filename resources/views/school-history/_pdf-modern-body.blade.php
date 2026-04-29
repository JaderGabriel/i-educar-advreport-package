@php($student = $data['student'])

  <style>
    h1 { font-size: 14px; letter-spacing: .08em; text-align: center; margin: 0 0 10px; }
    h2 { font-size: 11px; margin-top: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
    table th { background: #f8fafc; }
    .pill { display:inline-block; padding: 2px 6px; border: 1px solid #e5e7eb; border-radius: 999px; font-size: 9px; color: #374151; }
  </style>

  <h1>HISTÓRICO ESCOLAR</h1>

  <div class="box" style="border-color:#e5e7eb;">
    <table>
      <tr><th>Aluno(a)</th><td>{{ $student->aluno_nome }}</td></tr>
      <tr><th>Aluno (ID)</th><td><span class="pill">{{ $student->aluno_id }}</span></td></tr>
    </table>
  </div>

  @foreach(($data['items'] ?? []) as $item)
    @php($h = $item['history'])
    @php($disciplines = $item['disciplines'])

    <h2>{{ $h->nm_serie }} — {{ $h->ano }}</h2>
    <div class="muted" style="margin-bottom: 6px;">
      Escola: {{ $h->escola }} ({{ $h->escola_cidade }}/{{ $h->escola_uf }})
      @if(!empty($h->nm_curso)) — Curso: {{ $h->nm_curso }} @endif
      @if(!empty($h->frequencia)) — Frequência: {{ $h->frequencia }}% @endif
      @if(!empty($h->carga_horaria)) — Carga horária: {{ $h->carga_horaria }} @endif
    </div>

    @if(!empty($h->observacao))
      <div class="box" style="border-color:#e5e7eb;">
        <strong>Observações</strong>
        <div class="muted" style="margin-top: 4px;">{{ $h->observacao }}</div>
      </div>
    @endif

    <table>
      <thead>
      <tr>
        <th>Disciplina</th>
        <th>Nota</th>
        <th>Faltas</th>
        <th>Carga horária</th>
        <th>Dependência</th>
      </tr>
      </thead>
      <tbody>
      @foreach($disciplines as $d)
        <tr>
          <td>{{ $d->nm_disciplina }}</td>
          <td>{{ $d->nota ?? '-' }}</td>
          <td>{{ $d->faltas ?? '-' }}</td>
          <td>{{ $d->carga_horaria_disciplina ?? '-' }}</td>
          <td>{{ ($d->dependencia ?? 0) ? 'Sim' : 'Não' }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endforeach
