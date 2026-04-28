<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                \Filament\Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
                \Filament\Forms\Components\TextInput::make('tracking_code'),
                \Filament\Schemas\Components\Section::make('Shipping Data')
                    ->schema([
                        \Filament\Forms\Components\KeyValue::make('shipping_data'),
                    ])->collapsible(),
            ]);
    }
}
