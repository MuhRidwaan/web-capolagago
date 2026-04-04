<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaymentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  object  $booking  Data booking (dari DB query atau Model)
     * @param  object  $payment  Data payment
     */
    public function __construct(
        public readonly object $booking,
        public readonly object $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Berhasil – Booking #' . $this->booking->booking_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking.payment-confirmed',
            with: [
                'booking'     => $this->booking,
                'payment'     => $this->payment,
                'appName'     => config('app.name'),
                'appUrl'      => config('app.url'),
            ],
        );
    }
}
