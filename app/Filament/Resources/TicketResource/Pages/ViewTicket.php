<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Pages\ProjectBoard;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public ?int $editingCommentId = null;

    protected function getHeaderActions(): array
    {
        $ticket = $this->getRecord();
        $project = $ticket->project;
        $canComment = auth()->user()->can('createForTicket', [TicketComment::class, $ticket]);

        return [
            Actions\EditAction::make()
                ->visible(function () {
                    $ticket = $this->getRecord();

                    return auth()->user()->hasRole(['super_admin'])
                        || $ticket->created_by === auth()->id()
                        || $ticket->assignees()->where('users.id', auth()->id())->exists();
                }),

            Actions\Action::make('addComment')
                ->label('Tambahkan Komentar')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->form([
                    RichEditor::make('comment')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $ticket = $this->getRecord();

                    $comment = $ticket->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => $data['comment'],
                    ]);

                    // Mark related notifications as read for current user
                    auth()->user()->notifications()
                        ->where('data->ticket_id', $ticket->id)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);

                    Notification::make()
                        ->title('Komentar berhasil ditambahkan')
                        ->success()
                        ->send();
                })
                ->visible($canComment),

            Action::make('back')
                ->label('Kembali ke Board')
                ->color('gray')
                ->url(fn() => ProjectBoard::getUrl(['project_id' => $this->record->project_id])),
        ];
    }

    public function handleEditComment($id)
    {
        $comment = TicketComment::find($id);

        if (! $comment) {
            Notification::make()
                ->title('Komentar tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        // Check permissions
        if (! auth()->user()->can('update', $comment)) {
            Notification::make()
                ->title('Anda tidak memiliki izin untuk mengedit komentar ini.')
                ->danger()
                ->send();

            return;
        }

        $this->editingCommentId = $id; // Set ID komentar yang sedang diedit
        $this->mountAction('editComment', ['commentId' => $id]);
    }

    public function handleDeleteComment($id)
    {
        $comment = TicketComment::find($id);

        if (! $comment) {
            Notification::make()
                ->title('Komentar tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        // Check permissions
        if (! auth()->user()->can('delete', $comment)) {
            Notification::make()
                ->title('Anda tidak memiliki izin untuk menghapus komentar ini.')
                ->danger()
                ->send();

            return;
        }

        $comment->delete();

        Notification::make()
            ->title('Komentar berhasil dihapus')
            ->success()
            ->send();

        // Refresh the page
        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('uuid')
                                        ->label('Kode Ticket')
                                        ->copyable(),

                                    TextEntry::make('name')
                                        ->label('Nama Ticket'),

                                    TextEntry::make('project.name')
                                        ->label('Project'),
                                ]),
                        ])->columnSpan(1),

                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('status.name')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'To Do' => 'warning',
                                            'In Progress' => 'info',
                                            'Review' => 'primary',
                                            'Done' => 'success',
                                            default => 'gray',
                                        }),

                                    // FIXED: Multi-user assignees
                                    TextEntry::make('assignees.name')
                                        ->label('Petugas')
                                        ->badge()
                                        ->separator(',')
                                        ->default('Unassigned'),

                                    TextEntry::make('creator.name')
                                        ->label('Dibuat oleh')
                                        ->default('Unknown'),

                                    TextEntry::make('due_date')
                                        ->label('Deadline')
                                        ->date(),
                                ]),
                        ])->columnSpan(1),

                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created At')
                                        ->dateTime(),

                                    TextEntry::make('updated_at')
                                        ->label('Updated At')
                                        ->dateTime(),

                                    TextEntry::make('epic.name')
                                        ->label('Epic')
                                        ->default('No Epic'),
                                ]),
                        ])->columnSpan(1),
                    ]),

                Section::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Komentar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->description('Diskusikan ticket ini')
                    ->schema([
                        TextEntry::make('comments_list')
                            ->label('Komentar Terbaru')
                            ->state(function (Ticket $record) {
                                if (method_exists($record, 'comments')) {
                                    return $record->comments()->with('user')->latest()->get();
                                }

                                return collect();
                            })
                            ->view('filament.resources.ticket-resource.latest-comments'),
                    ])
                    ->collapsible(),

                Section::make('History Status')
                    ->icon('heroicon-o-clock')
                    // ->label('History Status')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('histories')
                            ->hiddenLabel()
                            ->view('filament.resources.ticket-resource.timeline-history'),
                    ]),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('editComment')
                ->label('Ubah Komentar')
                ->mountUsing(function (Forms\Form $form, array $arguments) {
                    $commentId = $arguments['commentId'] ?? null;

                    if (! $commentId) {
                        return;
                    }

                    $comment = TicketComment::find($commentId);

                    if (! $comment) {
                        return;
                    }

                    $form->fill([
                        'commentId' => $comment->id,
                        'comment' => $comment->comment,
                    ]);
                })
                ->form([
                    Hidden::make('commentId')
                        ->required(),
                    RichEditor::make('comment')
                        ->label('Comment')
                        ->toolbarButtons([
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'h2',
                            'h3',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $comment = TicketComment::find($data['commentId']);

                    if (! $comment) {
                        Notification::make()
                            ->title('Komentar tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Check permissions
                    if (! auth()->user()->can('update', $comment)) {
                        Notification::make()
                            ->title('anda tidak memiliki izin untuk mengedit komentar ini.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $comment->update([
                        'comment' => $data['comment'],
                    ]);

                    Notification::make()
                        ->title('Komentar berhasil diperbarui')
                        ->success()
                        ->send();

                    // Reset editingCommentId
                    $this->editingCommentId = null;

                    // Refresh the page
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
                })
                ->modalWidth('lg')
                ->modalHeading('Ubah Komentar')
                ->modalSubmitActionLabel('Update')
                ->color('success')
                ->icon('heroicon-o-pencil'),
        ];
    }
}
