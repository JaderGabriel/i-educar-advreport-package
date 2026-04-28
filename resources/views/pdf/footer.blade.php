<script type="text/php">
if (isset($pdf)) {
    $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
    $size = 8;
    $font = $fontMetrics->get_font("DejaVu Sans", "normal");
    $width = $fontMetrics->get_text_width($text, $font, $size);
    $x = $pdf->get_width() - $width - 36;
    $y = $pdf->get_height() - 24;
    $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
}
</script>

