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
  @include('advanced-reports::partials.filters', [
      'route' => route('advanced-reports.students-by-situation.index'),
      'cursos' => $cursos,
      'cursoId' => $cursoId ?? null,
      'ano' => $ano ?? null,
      'instituicaoId' => $instituicaoId ?? null,
      'escolaId' => $escolaId ?? null,
      'series' => $series ?? [],
      'turmas' => $turmas ?? [],
      'withGrade' => true,
      'withSchoolClass' => true,
      'extraRowsView' => 'advanced-reports::students-by-situation._extra-filters-rows',
      'actionsView' => 'advanced-reports::students-by-situation._actions',
      'explainTitle' => 'Alunos por situação',
      'explainText' => 'Lista e consolida alunos por situação de matrícula (cursando, transferido, reclassificado, abandono, falecido, etc.). Use os filtros para restringir por escola/curso/série/turma. A emissão é apenas em PDF (final) ou Excel.',
  ])
@endsection

@push('scripts')
  <script>
    (function () {
      const typeSelect = document.querySelector('.js-export-type');
      const runBtn = document.querySelector('.js-export-run');
      if (!typeSelect || !runBtn) return;

      runBtn.addEventListener('click', function () {
        const v = typeSelect.value;
        const url = v === 'excel' ? typeSelect.dataset.excel : typeSelect.dataset.pdf;
        if (!url) return;
        window.open(url, '_blank');
      });
    })();
  </script>
@endpush
