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
  $footerHtml = config('legacy.config.ieducar_internal_footer');
@endphp

<div class="ar-header">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      <td style="width: 70px; text-align: center; border-right: 1px solid #ddd; padding: 6px;">
        <img src="{{ $logoSrc }}" alt="Logo" style="max-width: 60px; max-height: 55px;">
      </td>
      <td style="padding: 6px 10px; font-size: 9px;">
        <div style="font-weight: 700;">{{ $entityName }}</div>
        @if(!empty($footerHtml))
          <div>{!! $footerHtml !!}</div>
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
        <div>Emitido em: {{ date('d/m/Y H:i') }}</div>
      </td>
    </tr>
  </table>
</div>

