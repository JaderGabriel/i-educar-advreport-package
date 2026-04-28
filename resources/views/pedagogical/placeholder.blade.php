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
    <p class="advanced-report-card-text">
      Esta tela existe para orientar o usuário leigo e manter a árvore de menus consistente com o roadmap do pacote.
    </p>
  </div>
@endsection

