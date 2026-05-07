<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Basic Information')
                    ->schema([
                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1000')
                            ->imageResizeTargetHeight('1000')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        \Filament\Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(\App\Models\Product::class, 'slug', ignoreRecord: true),
                        \Filament\Forms\Components\TextInput::make('brand'),
                        \Filament\Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
