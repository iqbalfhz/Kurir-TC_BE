<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Models\Delivery;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
 use Filament\Forms;
 use Filament\Forms\Form;
 use Filament\Resources\Resource;
 use Filament\Tables;
 use Filament\Tables\Table;

class DeliveryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sender_name')->required()->maxLength(191),
                Forms\Components\TextInput::make('receiver_name')->required()->maxLength(191),
                Forms\Components\Textarea::make('address')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'selesai' => 'Selesai',
                    ])->default('selesai'),
                Forms\Components\FileUpload::make('photo')->directory('deliveries')->disk('public')->image()->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->disk('public')->label('Photo')->square()->toggleable(),
                Tables\Columns\TextColumn::make('sender_name')->searchable(),
                Tables\Columns\TextColumn::make('receiver_name')->searchable(),
                Tables\Columns\TextColumn::make('address')->searchable(),
                // Tables\Columns\TextColumn::make('notes')->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                // add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view', 'view_any', 'create', 'update', 'delete',
        ];
    }
}
