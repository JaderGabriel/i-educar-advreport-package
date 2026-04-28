@extends('layout.default')

@push('styles')
  @if (class_exists('Asset'))
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
  @else
    <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
  @endif
@endpush

@section('content')
  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">{{ $title }}</strong>
    <p class="advanced-report-card-text">{{ $text }}</p>
    @if(!empty($status))
      <div style="margin-top: 10px; display:flex; align-items:center; gap: 10px; flex-wrap: wrap;">
        <span style="color:#b91c1c; font-weight: 700;">{{ $status }}</span>
        @if(!empty($issuesUrl))
          <a class="btn" href="{{ $issuesUrl }}" target="_blank" rel="noopener noreferrer">Enviar sugestão (issues)</a>
        @endif
      </div>
    @endif
  </div>

  @include('advanced-reports::partials.filters', [
      'route' => request()->url(),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'withGrade' => true,
      'withSchoolClass' => true,
      'withCharts' => false,
      'explainTitle' => 'Filtros',
      'explainText' => 'Use os filtros para restringir por ano/instituição/escola/curso/série/turma. A emissão será implementada por indicador.',
      'actionsView' => 'advanced-reports::indicators._actions',
  ])
@endsection

