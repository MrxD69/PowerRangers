<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generatePdf(string $html): string
    {
        // Ensure the HTML content is encoded in UTF-8
        $html = mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}