<?php
namespace Src\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Helper to generate PDFs using Dompdf.
 */
class PdfHelper
{
    public static function generateInvoice(array $data, string $outputPath): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = '
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            .header { text-align:center; font-size:22px; margin-bottom:20px; }
            table { width:100%; border-collapse: collapse; }
            td { padding: 6px; vertical-align: top; }
            .label { font-weight:bold; width:30%; }
        </style>
        <div class="header">Hotel Booking Invoice</div>
        <table>
            <tr><td class="label">Invoice No:</td><td>'.$data['invoice_no'].'</td></tr>
            <tr><td class="label">Customer:</td><td>'.$data['customer_name'].'</td></tr>
            <tr><td class="label">Hotel:</td><td>'.$data['hotel_name'].'<br>'.$data['hotel_address'].'</td></tr>
            <tr><td class="label">Check-in:</td><td>'.$data['check_in'].'</td></tr>
            <tr><td class="label">Check-out:</td><td>'.$data['check_out'].'</td></tr>
            <tr><td class="label">Room Type:</td><td>'.$data['room_type'].'</td></tr>
            <tr><td class="label">Guests:</td><td>'.$data['guests'].'</td></tr>
            <tr><td class="label">Total Price:</td><td>$'.$data['total_price'].'</td></tr>
        </table>
        <br><br>
        <div style="text-align:center;">Thank you for your booking!</div>
        ';

        $dompdf->loadHtml($html);
        $dompdf->render();

        file_put_contents($outputPath, $dompdf->stream());
    }
}
