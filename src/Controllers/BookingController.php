<?php 

namespace Src\Controllers;

use Src\Core\Database;
use Src\Helpers\ResponseHelper;
use Src\Middleware\AuthMiddleware;
use Src\Repositories\BookingRepository;
use Src\Services\BookingService;

/**
 * Class BookingController
 *
 * Handles booking-related API actions such as creating new bookings.
 * Ensures authenticated access, validates input, delegates business logic
 * to the BookingService, and returns consistent JSON responses.
 *
 * @package Src\Controllers
 */
class BookingController {
    private BookingService $bookingService;
    /**
     * BookingController constructor.
     *
     * Initializes database connection, booking repository, and service layer.
     * Loads configuration settings from the `config/config.php` file.
     */
    public function __construct() 
    {
        $config = require __DIR__ . '/../../config/config.php';
        $db = new Database($config['db']);
        $bookingRepo = new BookingRepository($db);
        $this->bookingService = new BookingService($bookingRepo);
    }

    /**
     * Create a new booking.
     *
     * Requires a valid JWT token in the `Authorization` header.
     * Extracts the authenticated user ID from the JWT payload.
     *
     * Expects JSON body with:
     * - `hotel_id` (int) - Hotel being booked
     * - `room_type` (string) - Type of room (e.g., "deluxe", "suite")
     * - `guests` (int) - Number of guests
     * - `check_in` (string, Y-m-d format)
     * - `check_out` (string, Y-m-d format)
     */
    public function create()
    {
        $payload = AuthMiddleware::handle();
        $userId = (int) $payload->sub;
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $data['user_id'] = $userId;

            $bookingData = $this->bookingService->createBooking($data);
            $data['booking_reference'] = $bookingData['booking_ref'];
            ResponseHelper::json([
                'message' => 'Booking created successfully',
                'data' => $data 
            ], 201);
        } catch (\InvalidArgumentException $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            ResponseHelper::json(['error' => $e->getMessage()], 500);
        }
    }
}