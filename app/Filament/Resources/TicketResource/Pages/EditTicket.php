<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Notifications\TicketCompletedNotification;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle assignees validation before saving
        if (!empty($data['assignees']) && !empty($data['project_id'])) {
            $project = Project::find($data['project_id']);

            if ($project) {
                $validAssignees = [];
                $invalidAssignees = [];

                foreach ($data['assignees'] as $userId) {
                    $isMember = $project->members()->where('users.id', $userId)->exists();

                    if ($isMember) {
                        $validAssignees[] = $userId;
                    } else {
                        $invalidAssignees[] = $userId;
                    }
                }

                // Update data with only valid assignees
                $data['assignees'] = $validAssignees;

                // Show warning if some users were invalid
                if (!empty($invalidAssignees)) {
                    Notification::make()
                        ->warning()
                        ->title('Some assignees removed')
                        ->body('Some selected users are not members of this project and have been removed from assignees.')
                        ->send();
                }
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\Ticket $ticket */
        $ticket = $this->record;

        // Cek apakah status berubah
        if ($ticket->wasChanged('ticket_status_id')) {
            // Ambil nama status sekarang
            $statusName = $ticket->status?->name;

            if ($statusName === 'Done') {
                if ($ticket->creator) {
                    $ticket->creator->notify(new \App\Notifications\TicketCompletedNotification($ticket));
                }
            }
        }
    }


    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Ticket updated')
            ->body('The ticket has been updated successfully.');
    }

    protected function getRedirectUrl(): string
    {
        $referer = request()->header('referer');

        if ($referer && str_contains($referer, 'project-board-page')) {
            return '/admin/project-board-page';
        }

        return $this->getResource()::getUrl('index');
    }
}
