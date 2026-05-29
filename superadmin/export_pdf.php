use Dompdf\Dompdf;

$dompdf = new Dompdf();

$html = "<h1>Laporan CityCare</h1>";

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream("laporan.pdf");