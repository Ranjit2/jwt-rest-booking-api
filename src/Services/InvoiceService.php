<?php
namespace Src\Services;

use Src\Repositories\InvoiceRepository;
use Src\Helpers\PdfHelper;
use Exception;

/**
 * Handles business logic for generating invoices.
 */
class InvoiceService
{
    private InvoiceRepository $invoiceRepo;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
    }

    /**
     * Generates invoice PDF for the given user and booking.
     *
     * @param int $userId
     * @param int $bookingId
     * @return string Path to generated PDF.
     * @throws Exception
     */
    public function generateInvoiceForUser(int $userId, int $bookingId): string
    {
        $booking = $this->invoiceRepo->findBookingForUser($userId, $bookingId);

        if (!$booking) {
            throw new Exception('Booking not found or does not belong to user');
        }

        $data = [
            'invoice_no' => $booking['booking_ref'],
            'customer_name' => $booking['user_name'],
            'hotel_name' => $booking['hotel_name'],
            'hotel_address' => $booking['hotel_address'],
            'check_in' => $booking['check_in'],
            'check_out' => $booking['check_out'],
            'room_type' => $booking['room_type'],
            'guests' => $booking['guests'],
            'total_price' => $booking['total_price'],
            'date' => date('Y-m-d'),
        ];

        $pdfPath = __DIR__ . '/../../public/invoices/invoice_' . $booking['booking_ref'] . '.pdf';
        PdfHelper::generateInvoice($data, $pdfPath);

        return $pdfPath;
    }
}
