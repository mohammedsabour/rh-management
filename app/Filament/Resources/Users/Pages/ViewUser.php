<?php
 
namespace App\Filament\Resources\Users\Pages;
 
use App\Filament\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
 
class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;
 
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('attestation')
                ->label('Attestation de travail')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn ($record) => route('employee.attestation.download', $record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
 