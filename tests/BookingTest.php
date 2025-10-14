<?php

use PHPUnit\Framework\TestCase;
use Src\Services\BookingService;
use Src\Repositories\BookingRepository;

/**
 * Class BookingTest
 */
class BookingTest extends TestCase
{
    private BookingService $bookingService;

    protected function setUp(): void
    {
        /** @var \Src\Repositories\BookingRepository&\PHPUnit\Framework\MockObject\MockObject $mockRepo */
        $mockRepo = $this->createMock(BookingRepository::class);

        $mockRepo->method('existsByRef')->willReturn(false);
        $mockRepo->method('saveBooking')->willReturn(true);

        $this->bookingService = new BookingService($mockRepo);
    }


    /**
     * Test that a booking can be created and generates a booking reference.
     */
    public function testCreateBookingGeneratesReference(): void
    {
        $data = [
            'user_id' => 1,
            'hotel_id' => 2,
            'room_type' => 'Deluxe',
            'guests' => 2,
            'check_in' => '2025-10-15',
            'check_out' => '2025-10-17',
        ];

        $result = $this->bookingService->createBooking($data);

        // Assert that booking_ref key exists
        $this->assertArrayHasKey('booking_ref', $result);

        // Assert booking_ref matches expected format (6 uppercase letters/numbers)
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $result['booking_ref']);
    }
}
