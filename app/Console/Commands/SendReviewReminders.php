<?php

namespace App\Console\Commands;

use App\Mail\ReviewReminder;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReviewReminders extends Command
{
    protected $signature = 'reviews:send-reminders';
    protected $description = 'Email customers a review reminder 7 days after their order was delivered';

    public function handle(): void
    {
        // whereDate() compares just the date part, ignoring time-of-day —
        // so this correctly catches every order delivered "7 days ago"
        // regardless of what time it was actually delivered, and won't
        // accidentally skip or double-catch orders near midnight.
        $orders = Order::where('status', 'delivered')
            ->whereDate('delivered_at', now()->subDays(7)->toDateString())
            ->whereNull('review_reminder_sent_at')
            ->get();

        foreach ($orders as $order) {
            Mail::to($order->email)->send(new ReviewReminder($order));
            $order->update(['review_reminder_sent_at' => now()]);
            $this->info("Sent review reminder for order {$order->order_number}");
        }

        $this->info("Done — {$orders->count()} reminder(s) sent.");
    }
}