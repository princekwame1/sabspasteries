<?php

namespace App\Support;

use App\Models\Message;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Emails the owner about new orders and messages (Settings → "Notify email"). Failures are logged, never shown to the customer. */
class LeadNotifier
{
    public static function order(Order $order): void
    {
        $lines = $order->summaryLines();
        $lines[] = 'Phone: '.$order->phone;
        $lines[] = 'Email: '.($order->email ?: '-');
        if ($order->design_notes) {
            $lines[] = '';
            $lines[] = $order->design_notes;
        }
        if ($order->allergies) {
            $lines[] = 'Allergies: '.$order->allergies;
        }
        $lines[] = '';
        $lines[] = 'Open in dashboard: '.route('admin.orders.show', $order);

        self::send('New order '.$order->reference().': '.$order->itemLine().' for '.$order->needed_on->format('j M'), implode("\n", $lines));
    }

    public static function message(Message $message): void
    {
        self::send('New message from '.$message->name, implode("\n", [
            'From: '.$message->name,
            'Phone: '.($message->phone ?: '-'),
            'Email: '.($message->email ?: '-'),
            '',
            $message->message,
            '',
            'Open in dashboard: '.route('admin.messages.show', $message),
        ]));
    }

    private static function send(string $subject, string $body): void
    {
        $to = trim(Setting::get('notify_email'));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
        } catch (Throwable $e) {
            Log::warning('Notification email failed: '.$e->getMessage());
        }
    }
}
