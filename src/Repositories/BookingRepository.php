<?php

namespace Src\Repositories;

use PDO;
use Src\Core\Database;
use Src\Helpers\ResponseHelper;

/**
 * Class BookingRepository
 *
 * Repository class for managing booking-related database operations.
 * Handles saving, retrieving, and validating bookings for hotels and users.
 */
class BookingRepository
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    /**
     * BookingRepository constructor.
     *
     * @param Database $database Database wrapper that provides a PDO connection
     */
    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Save a new booking record into the database.
     *
     * @param array $data Booking details including:
     *                    - booking_ref (string)
     *                    - user_id (int)
     *                    - hotel_id (int)
     *                    - room_type (string)
     *                    - price_per_night (float)
     *                    - check_in (string, date format)
     *                    - check_out (string, date format)
     *                    - guests (int)
     *                    - total_price (float)
     *                    - payment_status (string)
     *
     * @return int The ID of the newly created booking
     */
    public function saveBooking(array $data): int
    {
        $sql = "INSERT INTO bookings 
                (booking_ref, user_id, hotel_id, room_type, price_per_night, check_in, check_out, guests, total_price, payment_status)
                VALUES (:booking_ref, :user_id, :hotel_id, :room_type, :price_per_night, :check_in, :check_out, :guests, :total_price, :payment_status)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_ref' => $data['booking_ref'],
            ':user_id' => $data['user_id'],
            ':hotel_id' => $data['hotel_id'],
            ':room_type' => $data['room_type'],
            ':price_per_night' => $data['price_per_night'],
            ':check_in' => $data['check_in'],
            ':check_out' => $data['check_out'],
            ':guests' => $data['guests'],
            ':total_price' => $data['total_price'],
            ':payment_status' => $data['payment_status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find a booking by its reference code.
     *
     * @param string $ref Booking reference code
     * @return array|null Booking details if found, otherwise null
     */
    public function findByRef(string $ref): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE booking_ref = :ref LIMIT 1");
        $stmt->execute([':ref' => $ref]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Retrieve all bookings for a specific user.
     *
     * @param int $userId The user ID
     * @return array List of bookings sorted by creation date (newest first)
     */
    public function userBookings(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find hotel details (name and address) by ID.
     *
     * @param int $hotelId The hotel ID
     * @return array{name: string, address: string} Hotel details
     *
     * @throws \Exception If the hotel is not found
     */
    public function findHotelById(int $hotelId): array
    {
        $stmt = $this->db->prepare("SELECT name, address FROM hotels WHERE id = :hotel_id");
        $stmt->execute([':hotel_id' => $hotelId]);

        $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hotel) {
            ResponseHelper::json(['Hotel not found for given id'], 404);
        }

        return [
            'name' => $hotel['name'],
            'address' => $hotel['address'],
        ];
    }

    /**
     * Check whether a booking reference already exists in the database.
     *
     * @param string $ref Booking reference code
     * @return bool True if it exists, false otherwise
     */
    public function existsByRef(string $ref): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE booking_ref = :ref");
        $stmt->execute([':ref' => $ref]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if a booking overlaps with an existing one for the same user and hotel.
     *
     * @param int $userId The user ID
     * @param int $hotelId The hotel ID
     * @param string $checkIn New booking check-in date (YYYY-MM-DD)
     * @param string $checkOut New booking check-out date (YYYY-MM-DD)
     *
     * @return bool True if overlapping booking exists, false otherwise
     */
    public function findOverlapBooking(int $userId, int $hotelId, string $checkIn, string $checkOut): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM bookings 
            WHERE user_id = :user_id
              AND hotel_id = :hotel_id
              AND (
                  (check_in <= :check_out AND check_out >= :check_in)
              )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':hotel_id' => $hotelId,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut,
        ]);

        return $stmt->fetchColumn() > 0;
    }
}
