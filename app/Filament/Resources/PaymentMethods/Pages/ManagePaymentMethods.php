<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePaymentMethods extends ManageRecords
{
    protected static string $resource = PaymentMethodResource::class;
    protected static ?string $title = 'Moyens de paiement';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
