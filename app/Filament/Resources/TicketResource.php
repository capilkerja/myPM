<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketPriority;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Epic;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Ticket';

    protected static ?string $pluralLabel = 'Ticket';

    protected static ?string $navigationGroup = 'Project Management';

    protected static ?int $navigationSort = 5;


    //yang sebelumnya
    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();

    //     if (! auth()->user()->hasRole(['super_admin'])) {
    //         $query->where(function ($query) {
    //             $query->whereHas('assignees', function ($query) {
    //                 $query->where('users.id', auth()->id());
    //             })
    //                 ->orWhere('created_by', auth()->id())
    //                 ->orWhereHas('project.members', function ($query) {
    //                     $query->where('users.id', auth()->id());
    //                 });
    //         });
    //     }

    //     return $query;
    // }

    //yang baru
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // 1. Super Admin dapat melihat semua ticket tanpa batasan.
        if ($user->hasRole('admin')) {
            return $query;
        }

        // 2. Kabid dapat melihat semua ticket dalam project di mana ia menjadi anggota,
        //    dan juga ticket yang ia buat sendiri.
        if ($user->hasRole('kabid')) {
            return $query->where(function (Builder $builder) use ($user) {
                $builder->whereHas('project.members', function (Builder $subQuery) use ($user) {
                    $subQuery->where('users.id', $user->id);
                })
                    ->orWhere('created_by', $user->id);
            });
        }

        // 3. Staff (dan role lainnya sebagai default) hanya dapat melihat ticket
        //    yang secara spesifik ditugaskan (assigned) kepadanya.
        return $query->whereHas('assignees', function (Builder $subQuery) use ($user) {
            $subQuery->where('users.id', $user->id);
        });
    }

    public static function form(Form $form): Form
    {
        $projectId = request()->query('project_id') ?? request()->input('project_id');
        $statusId = request()->query('ticket_status_id') ?? request()->input('ticket_status_id');

        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->placeholder('Pilih Project/Goal yang ingin dicapai')
                    ->options(function () {
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return auth()->user()->projects()->pluck('name', 'projects.id')->toArray();
                    })
                    ->default($projectId)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set) {
                        $set('ticket_status_id', null);
                        $set('assignees', []);
                        $set('epic_id', null);
                    }),

                Forms\Components\Select::make('ticket_status_id')
                    ->label('Status')
                    ->placeholder('Pilih Status')
                    ->options(function ($get) {
                        $projectId = $get('project_id');
                        if (! $projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($statusId)
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('priority_id')
                    ->label('Prioritas')
                    ->placeholder('Pilih Prioritas')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('epic_id')
                    ->label('Epic')
                    ->placeholder('Pilih Epic')
                    ->helperText('Harap diperhartikan dalam pemilihan Epic/Rencana Aksi agar ticket lebih terstruktur dan sesuai dengan project/goal yang ingin dicapai')
                    ->options(function (callable $get) {
                        $projectId = $get('project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hidden(fn(callable $get): bool => !$get('project_id')),

                Forms\Components\TextInput::make('name')
                    ->label('Nama Ticket')
                    ->placeholder('Masukkan nama ticket')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),

                Forms\Components\RichEditor::make('description')
                    ->placeholder('Deskripsi Rincian Ticket/Tugas yang akan diberikan kepada petugas')
                    ->label('Deskripsi Ticket')
                    ->fileAttachmentsDirectory('attachments')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('output')
                    ->label('Target')
                    ->placeholder('Masukkan target yang ingin dicapai')
                    ->numeric()
                    ->rules(['integer', 'min:0'])
                    // ->disabled(fn() => ! Auth::user()->hasAnyRole(['super_admin', 'kabid']))
                    ->required(),

                Forms\Components\TextInput::make('input')
                    ->label('Realisasi')
                    ->placeholder('Masukkan realisasi dari target yang telah dicapai')
                    ->numeric()
                    ->rules(['integer', 'min:0'])
                    ->required(),

                Forms\Components\TextInput::make('category')
                    ->label('Satuan')
                    ->helperText('Satuan dari target (Contoh: Data, Dokumen, Orang, Laporan, dll)')
                    ->required()
                    // ->disabled(fn() => ! Auth::user()->hasAnyRole(['super_admin', 'kabid']))
                    ->maxLength(255),

                Forms\Components\TextInput::make('document')
                    ->label('Data Dukung')
                    ->helperText('Link File Dukung (Contoh: Google Drive, Dropbox, Github, dll)')
                    ->required()
                    ->maxLength(255),

                // Multi-user assignment
                Forms\Components\Select::make('assignees')
                    ->label('Petugas')
                    ->placeholder('Pilih Petugas')
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, callable $get) {
                            $projectId = $get('project_id');
                            if (! $projectId) {
                                return $query->whereRaw('1 = 0');
                            }

                            $project = Project::find($projectId);
                            if (! $project) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query->whereHas('projects', function ($query) use ($projectId) {
                                $query->where('projects.id', $projectId);
                            });
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih satu atau lebih user untuk memberi tugas kepada mereka. Hanya member tim  yang dapat ditunjuk sebagai petugas pada sebuah ticket')
                    ->hidden(fn(callable $get): bool => !$get('project_id'))
                    ->live(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->helperText('Tanggal mulai untuk ticket ini')
                    ->required()
                    ->native(false)
                    ->locale('id')
                    ->displayFormat('d/m/Y'),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Tanggal Selesai')
                    ->helperText('Deadline untuk ticket ini, set tanggal +7 hari dari tanggal mulai')
                    ->required()
                    ->native(false)
                    ->locale('id')
                    ->displayFormat('d/m/Y'),
                Forms\Components\Select::make('created_by')
                    ->label('Dibuat oleh')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Kode Ticket')
                    ->wrap()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Nama Project')
                    ->sortable()
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Ticket')
                    ->wrap()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority.name')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        'Low' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->default('—')
                    ->placeholder('No Priority'),

                // Display multiple assignees
                Tables\Columns\TextColumn::make('assignees.name')
                    ->label('Petugas')
                    ->wrap()
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('epic.name')
                    ->label('Epic')
                    ->sortable()
                    ->searchable()
                    ->default('—')
                    ->placeholder('No Epic'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(function () {
                        if (auth()->user()->hasRole(['super_admin'])) {
                            return Project::pluck('name', 'id')->toArray();
                        }

                        return auth()->user()->projects()->pluck('name', 'projects.id')->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return TicketStatus::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('epic_id')
                    ->label('Epic')
                    ->options(function () {
                        $projectId = request()->input('tableFilters.project_id');

                        if (!$projectId) {
                            return [];
                        }

                        return Epic::where('project_id', $projectId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('priority_id')
                    ->label('Priority')
                    ->options(TicketPriority::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),

                // Filter by assignees
                Tables\Filters\SelectFilter::make('assignees')
                    ->label('Assignee')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                // Filter by creator
                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from'),
                        Forms\Components\DatePicker::make('due_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                    Action::make('copy')
                        ->label('Copy')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function ($record, $livewire) {

                            // Redirect ke halaman create, dengan parameter copy_from
                            return $livewire->redirect(
                                static::getUrl('create', [
                                    'copy_from' => $record->id,
                                ])
                            );
                        }),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->hasRole(['super_admin'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getEloquentQuery();

        return $query->count();
    }
}
