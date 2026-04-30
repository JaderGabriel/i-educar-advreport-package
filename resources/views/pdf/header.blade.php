@php
  $logoPath = config('legacy.config.ieducar_image') ?? config('legacy.app.template.pdf.logo');
  $logoSrc = $logoPath
    ? (str_starts_with($logoPath, 'http')
      ? $logoPath
      : (class_exists('Asset')
        ? (Asset::get($logoPath) ?? url($logoPath))
        : url($logoPath)))
    : url('intranet/imagens/brasao-republica.png');

  $entityName = mb_strtoupper(
    config('legacy.config.ieducar_entity_name') ?? config('legacy.app.entity.name') ?? 'i-Educar',
    'UTF-8'
  );
@endphp

<div class="ar-header">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      <td style="width: 70px; text-align: center; border-right: 1px solid #ddd; padding: 6px;">
        <img src="{{ $logoSrc }}" alt="Logo" style="max-width: 60px; max-height: 55px;">
      </td>
      <td style="padding: 6px 10px; font-size: 9px;">
        <div style="font-weight: 700;">{{ $entityName }}</div>
        @if(!empty($formalHeader))
          @if(!empty($municipality))
            <div>{{ $municipality }}</div>
          @endif
          @if(!empty($schoolName))
            <div><strong>{{ $schoolName }}</strong></div>
          @endif
          @if(!empty($contact))
            <div>{{ $contact }}</div>
          @endif
        @else
          @php($footerHtml = config('legacy.config.ieducar_internal_footer'))
          @if(!empty($footerHtml))
            <div>{!! $footerHtml !!}</div>
          @endif
        @endif
        @if(!empty($subtitle))
          <div style="margin-top: 2px; color: #444;">{{ $subtitle }}</div>
        @endif
      </td>
      <td style="width: 180px; padding: 6px 10px; font-size: 9px; text-align: right; color: #444;">
        <div><strong>{{ $title ?? '' }}</strong></div>
        @if(!empty($year))
          <div>Ano: {{ $year }}</div>
        @endif
        <div>Emitido em: {{ !empty($issuedAt) ? $issuedAt : date('d/m/Y H:i') }}</div>
        @if(!empty($validationCode))
          <div style="margin-top: 2px; font-size: 8px;">Código: <strong>{{ $validationCode }}</strong></div>
        @endif
      </td>
    </tr>
  </table>
</div>

<script type="text/php">
  if (isset($pdf)) {
    $text = "Pág. {PAGE_NUM}/{PAGE_COUNT}";
    $size = 7;
    $font = $fontMetrics->get_font("DejaVu Sans", "normal");
    $width = $fontMetrics->get_text_width($text, $font, $size);
    $x = $pdf->get_width() - $width - 36;
    $y = $pdf->get_height() - 52;
    $pdf->page_text($x, $y, $text, $font, $size, [0.25, 0.25, 0.25]);
  }
</script>

