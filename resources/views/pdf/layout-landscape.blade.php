<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <title>{{ trim($__env->yieldContent('doc_title')) ?: ($title ?? 'Documento') }}</title>
    <style>
      @page { size: A4 landscape; margin: 110px 36px 36px 36px; }
      body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111; }

      .ar-header { position: fixed; top: -92px; left: 0; right: 0; border: 1px solid #ddd; }
      .ar-footer { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 9px; color: #444; }
      .ar-content { padding-bottom: 96px; }
      .ar-official-footer { position: fixed; left: 36px; right: 36px; bottom: 36px; }
      h1, h2 { margin: 6px 0; }
      h1 { text-align: center; }
    </style>
  </head>
  <body>
    @include('advanced-reports::pdf.header', [
      'title' => trim($__env->yieldContent('doc_title')) ?: ($title ?? null),
      'subtitle' => trim($__env->yieldContent('doc_subtitle')) ?: ($subtitle ?? null),
      'year' => trim($__env->yieldContent('doc_year')) ?: ($year ?? null),
      'formalHeader' => trim($__env->yieldContent('formal_header')) === '1',
      'municipality' => trim($__env->yieldContent('doc_municipality')) ?: ($municipality ?? null),
      'schoolName' => trim($__env->yieldContent('doc_school')) ?: ($schoolName ?? null),
      'contact' => trim($__env->yieldContent('doc_contact')) ?: ($contact ?? null),
    ])

    @php($disableFooter = trim($__env->yieldContent('disable_footer')) === '1')
    @unless($disableFooter)
      <div class="ar-footer"></div>
      @include('advanced-reports::pdf.footer')
    @endunless

    <div class="ar-content">
      @yield('content')
    </div>
  </body>
</html>

