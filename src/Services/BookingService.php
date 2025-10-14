<?php

namespace Src\Services;

use DateTime;
use Src\Helpers\ResponseHelper;
use Src\Repositories\BookingRepository;

/**
 * Class BookingService
 *
 * Handles business logic for creating and managing bookings.
 */
class BookingService {
    const MAX_GUESTS_PER_ROOM = 4;
    /**
     * @var BookingRepository Repository for booking database operations
     */
    private BookingRepository $bookingRepo;

    /**
     * BookingService constructor.
     *
     * @param BookingRepository $bookingRepository
     */

    public function __construct(BookingRepository $bookingRepository) {
        $this->bookingRepo = $bookingRepository;
    }

    public function createBooking( array $data ) {

        $this->validateData($data);
        do {
            $ref = $this->generateBookingRef();
        }
        while ( $this->bookingRepo->existsByRef( $ref ) );
        $data[ 'booking_ref' ] = $ref;

        $this->bookingRepo->saveBooking( $data );

        return $data;
    }

    private function generateBookingRef( $length = 6 ): string {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ref = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $ref .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
        }

        return $ref;
    }

    private function validateData(array $data)
    {
        if (!isset($data['guests'], $data['room_type'], $data['check_in'], $data['check_out'])) {
            throw new \Exception('Missing required booking fields');
        }

        $checkIn = new DateTime($data['check_in']);
        $checkOut = new DateTime(($data['check_out']));
        $guests = (int) $data['guests'];
        if ($checkOut <= $checkIn) {
            throw new \Exception('Check-out date must be after check-in date');
        }

        if ($guests < 1 || $guests > self::MAX_GUESTS_PER_ROOM) {
            throw new \Exception('Invalid number of guests for this room type');
        }
        //Duplicate booking check
        $existing = $this->bookingRepo->findOverlapBooking(
            $data['user_id'],
            $data['hotel_id'],
            $data['check_in'],
            $data['check_out']
        );
        
        if ($existing) {
            throw new \Exception('Duplicate booking detected: You already have a booking for this hotel during the selected dates');
        }
    }
}
