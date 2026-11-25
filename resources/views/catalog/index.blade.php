@extends('layout.default')

@push('styles')
  @if (class_exists('Asset'))
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/advanced-reports.css') }}"/>
  @else
    <link rel="stylesheet" type="text/css" href="{{ asset('css/advanced-reports.css') }}"/>
  @endif
  <style>
    .ar-catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; margin: 12px 0 24px; }
    .ar-catalog-card { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; padding: 14px; border-top: 3px solid #0e7f6c; }
    .ar-catalog-card h4 { margin: 0 0 6px; font-size: 15px; }
    .ar-catalog-card p { margin: 0 0 10px; font-size: 12px; color: #4b5563; line-height: 1.4; }
    .ar-catalog-card a.ar-open { display: inline-flex; align-items: center; gap: 6px; background: #0e7f6c; color: #fff; text-decoration: none; border-radius: 4px; padding: 6px 10px; font-size: 12px; font-weight: 600; }
    .ar-formats { display: flex; flex-wrap: wrap; gap: 4px; margin: 0 0 10px; }
    .ar-formats span { font-size: 10px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 999px; padding: 2px 8px; }
    .ar-formats span.qr { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .ar-edital-bar { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0 0; }
    .ar-edital-bar span { font-size: 11px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 3px 8px; color: #334155; }
  </style>
@endpush

@section('content')
  <div class="advanced-report-card">
    <strong class="advanced-report-card-title">Relatórios avançados · catálogo para editais</strong>
    <p class="advanced-report-card-text">
      Visão dos documentos e indicadores disponíveis no pacote (v{{ $versao }}), com formatos de saída e validação por QR
      quando aplicável. Use este painel para demonstrar cobertura típica de termo de referência sem Jasper/Java.
    </p>
    <p class="advanced-report-card-text"><strong>Formatos frequentes em editais:</strong></p>
    <div class="ar-edital-bar">
      @foreach($formatosEdital as $fmt)
        <span>{{ $fmt }}</span>
      @endforeach
    </div>
    <p class="advanced-report-card-text" style="margin-top:10px">
      Neste pacote: <strong>PDF</strong> (Dompdf + QR) e <strong>XLSX</strong> nos indicadores/movimentações.
      Demais formatos de lista (CSV, DOCX, ODT…) podem ser cobertos em conjunto com o módulo de exportação unificada.
    </p>
  </div>

  @foreach($grupos as $grupo => $itens)
    <h3 style="margin:18px 0 8px;font-size:16px;color:#0f172a">{{ $grupo }}</h3>
    <div class="ar-catalog-grid">
      @foreach($itens as $item)
        <article class="ar-catalog-card">
          <h4>{{ $item['titulo'] }}</h4>
          <p>{{ $item['descricao'] }}</p>
          <div class="ar-formats">
            @foreach($item['formatos'] as $fmt)
              <span>{{ $fmt }}</span>
            @endforeach
            @if($item['qr'])
              <span class="qr">QR</span>
            @endif
          </div>
          @if(!empty($item['rota']) && \Illuminate\Support\Facades\Route::has($item['rota']))
            <a class="ar-open" href="{{ route($item['rota']) }}">Abrir</a>
          @elseif(empty($item['rota']))
            <span style="font-size:12px;color:#64748b">Rota pública: /documentos/validar/{código}</span>
          @else
            <span style="font-size:12px;color:#b45309">Rota ainda não publicada neste ambiente</span>
          @endif
        </article>
      @endforeach
    </div>
  @endforeach
@endsection
