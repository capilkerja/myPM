<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages;
use App\Filament\Resources\MeetingResource\RelationManagers;
use App\Models\Meeting;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Date;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Meeting';

    protected static ?string $pluralLabel = 'Meeting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->placeholder('Masukkan nama rapat')
                    ->columnSpanFull()
                    ->label('Judul'),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->label('Deskripsi'),
                TextInput::make('location')
                    ->required()
                    ->placeholder('Masukkan lokasi rapat')
                    ->label('Lokasi'),
                // INI PERUBAHAN UTAMA DI FORM
                Forms\Components\Select::make('assignees')
                    ->label('Peserta Rapat')
                    ->relationship('assignees', 'name') // 'assignees' adalah nama method relasi, 'name' adalah kolom yang ditampilkan
                    ->multiple() // Mengizinkan pemilihan lebih dari satu user
                    ->preload() // Memuat opsi saat halaman dimuat
                    ->searchable(),
                DateTimePicker::make('starts_at')
                    ->required()
                    ->placeholder('Masukkan waktu mulai rapat')
                    ->label('Waktu Mulai'),
                DateTimePicker::make('ends_at')
                    ->required()
                    ->placeholder('Masukkan waktu selesai rapat')
                    ->label('Waktu Selesai'),
                TextInput::make('link')
                    ->required()
                    ->placeholder('Masukkan link Gdrive data dukung rapat')
                    ->label('Data Dukung'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->label('Judul'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->wrap(true)
                    ->html(true)
                    ->label('Deskripsi'),
                Tables\Columns\TextColumn::make('location')
                    ->wrap()
                    ->searchable()
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('assignees.name')
                    ->searchable()
                    ->wrap()
                    ->badge()
                    ->label('Penanggung Jawab'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->wrap()
                    ->label('Mulai'),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->wrap()
                    ->label('Selesai'),
                Tables\Columns\TextColumn::make('link')
                    ->searchable()
                    ->wrap()
                    ->label('Data Dukung'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    ActivityLogTimelineTableAction::make('Aktivitas')
                        ->timelineIcons([
                            'created' => 'heroicon-m-check-badge',
                            'updated' => 'heroicon-m-pencil-square',
                        ])
                        ->timelineIconColors([
                            'created' => 'info',
                            'updated' => 'warning',
                        ])
                        ->limit(10),
                ])
                    ->tooltip('Aksi'),
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
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
