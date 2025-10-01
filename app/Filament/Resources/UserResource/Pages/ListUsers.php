<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New User')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users')
                ->icon('heroicon-o-users')
                ->badge(User::count()),
            
            'admins' => Tab::make('System Admins')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('has_admin_role', true))
                ->badge(User::where('has_admin_role', true)->count())
                ->badgeColor('danger'),
            
            'team' => Tab::make('Team Members')
                ->icon('heroicon-o-user-group')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', User::ROLE_TEAM))
                ->badge(User::where('role', User::ROLE_TEAM)->count())
                ->badgeColor('success'),
            
            'consultants' => Tab::make('Consultants')
                ->icon('heroicon-o-briefcase')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', User::ROLE_CONSULTANT))
                ->badge(User::where('role', User::ROLE_CONSULTANT)->count())
                ->badgeColor('warning'),
            
            'clients' => Tab::make('Clients')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', User::ROLE_CLIENT))
                ->badge(User::where('role', User::ROLE_CLIENT)->count())
                ->badgeColor('info'),
        ];
    }
}
