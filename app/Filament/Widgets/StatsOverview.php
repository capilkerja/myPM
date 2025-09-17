<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if ($isSuperAdmin) {
            return $this->getSuperAdminStats();
        } else {
            return $this->getUserStats();
        }
    }

    protected function getSuperAdminStats(): array
    {
        $totalProjects = Project::count();
        $totalTickets = Ticket::count();
        $usersCount = User::count();
        $myTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->where('ticket_users.user_id', auth()->id())
            ->count();

        return [
            Stat::make('Total Project', $totalProjects)
                ->description('Total Keseluruhan Project di Sistem')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Total Ticket', $totalTickets)
                ->description('Total Keseluruhan Ticket di Sistem')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Penugasan Ticket', $myTickets)
                ->description('Ticket yang ditugaskan ke kamu')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),

            Stat::make('Pengguna', $usersCount)
                ->description('Pengguna Terdaftar di Sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }

    protected function getUserStats(): array
    {
        $user = auth()->user();

        $myProjects = $user->projects()->count();

        $myProjectIds = $user->projects()->pluck('projects.id')->toArray();

        $projectTickets = Ticket::whereIn('project_id', $myProjectIds)->count();

        $myAssignedTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->where('ticket_users.user_id', $user->id)
            ->count();

        $myCreatedTickets = Ticket::where('created_by', $user->id)->count();

        $newTicketsThisWeek = Ticket::whereIn('project_id', $myProjectIds)
            ->where('tickets.created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $myOverdueTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $user->id)
            ->where('tickets.due_date', '<', Carbon::now())
            ->whereNotIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->count();

        $myCompletedThisWeek = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $user->id)
            ->whereIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->where('tickets.updated_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $teamMembers = User::whereHas('projects', function ($query) use ($myProjectIds) {
            $query->whereIn('projects.id', $myProjectIds);
        })->where('id', '!=', $user->id)->count();

        return [
            Stat::make('Project Saya', $myProjects)
                ->description('Project dimana kamu tergabung')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Penugasan Ticket', $myAssignedTickets)
                ->description('Ticket yang ditugaskan ke kamu')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($myAssignedTickets > 10 ? 'danger' : ($myAssignedTickets > 5 ? 'warning' : 'success')),

            Stat::make('Ticket Saya', $myCreatedTickets)
                ->description('Ticket yang kamu buat')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make('Project ', $projectTickets)
                ->description('Total ticket pada project yang kamu tergabung')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Selesai Minggu Ini', $myCompletedThisWeek)
                ->description('Ticket yang kamu selesaikan dalam minggu ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($myCompletedThisWeek > 0 ? 'success' : 'gray'),

            Stat::make('Ticket Baru Minggu Ini', $newTicketsThisWeek)
                ->description('Ticket baru dalam minggu ini')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('Melebihi Deadline', $myOverdueTickets)
                ->description('Ticket Kamu yang melebihi deadline.')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($myOverdueTickets > 0 ? 'danger' : 'success'),

            Stat::make('Anggota Tim', $teamMembers)
                ->description('Anggota Tim yang tergabung dengan project kamu.')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
