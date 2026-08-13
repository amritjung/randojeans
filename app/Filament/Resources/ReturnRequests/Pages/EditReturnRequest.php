<?php

namespace App\Filament\Resources\ReturnRequests\Pages;

use App\Filament\Resources\ReturnRequests\ReturnRequestResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // beforeSave() runs right before Filament actually writes the form
    // to the database — throwing Halt() here stops the save entirely
    // and keeps the form open with the error shown, rather than letting
    // an outside-window approval slip through silently.
    protected function beforeSave(): void
    {
        $isBeingApproved = ($this->data['status'] ?? null) === 'approved'
            && $this->record->status !== 'approved';

        if (! $isBeingApproved) {
            return;
        }

        $order = $this->record->order;

        if (! $order->delivered_at || $order->delivered_at->diffInDays(now()) > 30) {
            Notification::make()
                ->title('Cannot approve')
                ->body('This order is now outside the 30-day return window (delivered ' . $order->delivered_at?->format('d M Y') . ').')
                ->danger()
                ->send();

            throw new Halt();
        }
    }
}