<?php
namespace Src\Repositories;

use PDO;
use Src\Core\Database;

/**
 * Fetches booking data for invoice generation.
 */
class InvoiceRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Get booking belonging to a specific user.
     *
     * @param int $userId
     * @param int $bookingId
     * @return array|null
     */
    public function findBookingForUser(int $userId, int $bookingId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                b.booking_ref, b.check_in, b.check_out, b.room_type, b.guests, b.total_price,
                u.name AS user_name, u.email AS user_email,
                h.name AS hotel_name, h.address AS hotel_address
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN hotels h ON h.id = b.hotel_id
            WHERE b.id = :booking_id AND b.user_id = :user_id
        ");

        $stmt->execute([':booking_id' => $bookingId, ':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
