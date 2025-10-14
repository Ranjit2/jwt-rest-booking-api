<?php
namespace Src\Controllers;

use Src\Core\Database;
use Src\Services\InvoiceService;
use Src\Helpers\ResponseHelper;
use Src\Middleware\AuthMiddleware;
use Src\Repositories\InvoiceRepository;

/**
 * Handles invoice download requests.
 */
class InvoiceController
{
    private InvoiceService $invoiceService;
    /**
     * InvoiceController constructor.
     *
     * Initializes database connection, booking repository, and service layer.
     * Loads configuration settings from the `config/config.php` file.
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $db = new Database($config['db']);
        $invoiceRepository = new InvoiceRepository($db);
        $this->invoiceService = new InvoiceService($invoiceRepository);
    }

    /**
     * Download a booking invoice for the logged-in user.
     
    */
    public function download()
    {
        $payload = AuthMiddleware::handle();
        try {
            $userId = (int) $payload->sub;

            if (!$userId) {
                throw new \Exception('Unauthorized: missing user ID');
            }

            $bookingId = $_GET['booking_id'] ?? null;
            if (!$bookingId) {
                throw new \Exception('Missing booking_id in request');
            }

            //  Generate PDF
            $pdfPath = $this->invoiceService->generateInvoiceForUser($userId, $bookingId);

            //  Output file
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
            readfile($pdfPath);
            exit;

        } catch (\Exception $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 400);
        }
    }
}
