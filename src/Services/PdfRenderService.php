<?php

namespace iEducar\Packages\AdvancedReports\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Symfony\Component\HttpFoundation\Response;

class PdfRenderService
{
    public function __construct(private readonly ViewFactory $views)
    {
    }

    /**
     * Renderiza uma view Blade em PDF e devolve resposta "download".
     *
     * @param array<string,mixed> $data
     */
    public function download(
        string $view,
        array $data,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait',
        string $disposition = 'inline'
    ): Response
    {
        $html = $this->views->make($view, $data)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }
}

