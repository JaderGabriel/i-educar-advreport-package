@php($item = $item ?? [])
@php($F = $fields ?? [])
@php($ctx = $context ?? [])
@php($def = $definition ?? [])

<h1>{{ $def['doc_title'] ?? 'COMUNICADO' }}</h1>
<p class="muted" style="text-align:center;">{{ $def['doc_subtitle'] ?? '' }}</p>

<table style="margin-top: 10px;">
  <tr>
    <th style="width: 22%;">Referência</th>
    <td>{{ $F['ref_documento'] ?: '—' }}</td>
  </tr>
  <tr>
    <th>Data do comunicado</th>
    <td>
      @if(!empty($F['data_documento']))
        {{ \Illuminate\Support\Carbon::parse($F['data_documento'])->format('d/m/Y') }}
      @else
        —
      @endif
    </td>
  </tr>
  <tr>
    <th>Ano letivo / Unidade</th>
    <td>{{ $ctx['ano_letivo'] ?? '—' }} — {{ $ctx['escola_nome'] ?? '—' }}</td>
  </tr>
  @if(!empty($ctx['curso_nome']) || !empty($ctx['serie_nome']) || !empty($ctx['turma_nome']))
    <tr>
      <th>Curso / Série / Turma</th>
      <td>
        {{ $ctx['curso_nome'] ?? '—' }}
        @if(!empty($ctx['serie_nome'])) — {{ $ctx['serie_nome'] }} @endif
        @if(!empty($ctx['turma_nome'])) — Turma: {{ $ctx['turma_nome'] }} @endif
      </td>
    </tr>
  @endif
</table>

<div class="box" style="margin-top: 12px;">
  <p style="margin: 0 0 8px 0;"><strong>Assunto:</strong> {{ $F['assunto'] ?? '—' }}</p>
  @if(!empty($F['local_evento']) || !empty($F['data_evento']) || !empty($F['hora_evento']))
    <p class="muted" style="margin: 0;">
      @if(!empty($F['local_evento']))<strong>Local:</strong> {{ $F['local_evento'] }} &nbsp; @endif
      @if(!empty($F['data_evento']))
        <strong>Data:</strong> {{ \Illuminate\Support\Carbon::parse((string) $F['data_evento'])->format('d/m/Y') }}
      @endif
      @if(!empty($F['hora_evento']))
        &nbsp; <strong>Horário:</strong> {{ substr($F['hora_evento'], 0, 5) }}
      @endif
    </p>
  @endif
  @if(!empty($F['prazo_resposta']))
    <p style="margin: 8px 0 0 0;"><strong>Prazo:</strong> {{ $F['prazo_resposta'] }}</p>
  @endif
</div>

<p style="margin-top: 14px; line-height: 1.45;">
  <strong>{{ $item['destinatario_prefixo'] ?? 'Aos(Às) responsáveis' }}</strong>
  @if(!empty($item['aluno_nome']))
    <strong> {{ $item['aluno_nome'] }}</strong>,
  @endif
</p>
<p class="muted" style="margin-top: 4px;">{{ $ctx['escola_nome'] ?? '' }}</p>

<div class="box" style="margin-top: 12px;">
  {!! nl2br(e($F['corpo'] ?? '')) !!}
</div>

@if(!empty($F['pauta']))
  <div class="box" style="margin-top: 10px;">
    <strong>Pauta / ordem do dia</strong>
    <div style="margin-top: 6px;">{!! nl2br(e($F['pauta'])) !!}</div>
  </div>
@endif

@include('advanced-reports::pdf._issuer-signature', ['issuerName' => $issuerName ?? null, 'schoolInep' => $schoolInep ?? null])
