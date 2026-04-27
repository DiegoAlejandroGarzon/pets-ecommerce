<?php

namespace App\Filament\Resources\Pets\Schemas;

use Filament\Schemas\Schema;

class PetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required(),
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                \Filament\Forms\Components\TextInput::make('species')
                    ->required()
                    ->placeholder('Dog, Cat, etc.'),
                \Filament\Forms\Components\TextInput::make('breed'),
                \Filament\Forms\Components\DatePicker::make('birth_date'),
            ]);
    }
}
