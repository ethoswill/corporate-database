<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static bool $shouldRegisterNavigation = false; // Hide from navigation since it's accessed via FranchiseeList

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Location Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Location Name'),
                        Forms\Components\TextInput::make('logo_file_url')
                            ->url()
                            ->maxLength(255)
                            ->label('Logo File URL')
                            ->helperText('Enter a URL to the logo file (alternative to uploading)'),
                        Forms\Components\TextInput::make('owner_name')
                            ->maxLength(255)
                            ->label('Owner Name(s)')
                            ->helperText('Enter the name(s) of the location owner(s)'),
                        Forms\Components\DatePicker::make('studio_anniversary')
                            ->label('Studio Anniversary')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('address_line_1')
                            ->required()
                            ->maxLength(255)
                            ->label('Address Line 1'),
                        Forms\Components\TextInput::make('address_line_2')
                            ->maxLength(255)
                            ->label('Address Line 2'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('state')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('zip_code')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Zip Code'),
                                Forms\Components\TextInput::make('country')
                                    ->default('US')
                                    ->maxLength(255),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255)
                                    ->label('Phone Number'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255)
                                    ->label('Email Address'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Artwork Files')
                    ->description('Enter URLs to up to 5 artwork files that will be used for production')
                    ->schema([
                        Forms\Components\TextInput::make('lockup_file_1')
                            ->label('Artwork File 1 URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Enter the full URL to the artwork file'),
                        Forms\Components\TextInput::make('lockup_file_2')
                            ->label('Artwork File 2 URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Enter the full URL to the artwork file'),
                        Forms\Components\TextInput::make('lockup_file_3')
                            ->label('Artwork File 3 URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Enter the full URL to the artwork file'),
                        Forms\Components\TextInput::make('lockup_file_4')
                            ->label('Artwork File 4 URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Enter the full URL to the artwork file'),
                        Forms\Components\TextInput::make('lockup_file_5')
                            ->label('Artwork File 5 URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Enter the full URL to the artwork file'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Location Name'),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->label('Owner Name(s)')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('studio_anniversary')
                    ->date()
                    ->sortable()
                    ->label('Studio Anniversary')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address_line_1')
                    ->label('Address')
                    ->formatStateUsing(fn ($record) => $record->address_line_1 . ', ' . $record->city . ', ' . $record->state . ' ' . $record->zip_code)
                    ->searchable(['address_line_1', 'city', 'state', 'zip_code'])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_lockups')
                    ->label('Artwork Files')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->lockup_file_1) || !empty($record->lockup_file_2) || !empty($record->lockup_file_3) || !empty($record->lockup_file_4) || !empty($record->lockup_file_5))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
