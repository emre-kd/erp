<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('name')
            ->rules([
                'required'
            ]),
            TextInput::make('email')
            ->rules([
                'email',
                'required',
             
            ])
            ->unique(ignoreRecord: true),
            TextInput::make('password')
              ->rules([
                'nullable',
                'min:8',
            ])
            ->password()
            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
            ->dehydrated(fn ($state) => filled($state))
            ->label('Şifre')
            ->required(fn (string $context) => $context === 'create'),
            Select::make('roles')  // Rol çoklu olabilir diye roles dedik
            ->rules([
                'required'
            ])
            ->multiple()
            ->relationship('roles', 'name')
            ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles')
                ->label('Rol')
                ->getStateUsing(fn ($record) => $record->getRoleNames()->join(', '))
                ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function afterCreate($record): void
    {
        if (request()->has('role')) {
            $record->assignRole(request('role'));
        }
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }




  
}
