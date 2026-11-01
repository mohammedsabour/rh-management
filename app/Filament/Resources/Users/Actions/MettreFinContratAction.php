<?php

namespace App\Filament\Resources\Users\Actions;

use App\Models\Document;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Fin de service d'un employé : date de fin + motif + pièce justificative obligatoire,
 * puis le compte passe en inactif (is_active = false).
 */
class MettreFinContratAction
{
    public static function make(): Action
    {
        return Action::make('fin_contrat')
            ->label('Mettre fin au contrat')
            ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
            ->color('danger')
            ->authorize('update')
            ->visible(fn (User $record) => $record->is_active && $record->isNot(Auth::user()))
            ->modalHeading('Mettre fin au contrat')
            ->modalDescription("L'employé sera désactivé et ne pourra plus se connecter. Son dossier est conservé.")
            ->modalSubmitActionLabel('Confirmer la fin de contrat')
            ->schema([
                Select::make('motif_depart')
                    ->label('Motif')
                    ->options(User::MOTIFS_DEPART)
                    ->required()
                    ->native(false)
                    ->live(),

                DatePicker::make('date_fin')
                    ->label('Dernier jour de travail')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(today())
                    ->minDate(fn (User $record) => $record->date_embauche),

                FileUpload::make('justificatif')
                    ->label(fn (Get $get) => $get('motif_depart') === 'retraite'
                        ? 'Document justifiant la retraite'
                        : "Document d'acceptation de la démission")
                    ->required()
                    ->disk(Document::DISK)
                    ->directory('employes/documents')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(5120)
                    ->storeFileNamesIn('justificatif_nom')
                    ->openable()
                    ->hidden(fn (Get $get) => blank($get('motif_depart'))),
            ])
            ->action(function (User $record, array $data) {
                // Tout ou rien : pas de compte désactivé sans sa pièce justificative
                DB::transaction(function () use ($record, $data) {
                    $record->documents()->create([
                        'type' => $data['motif_depart'] === 'retraite' ? 'justificatif_retraite' : 'acceptation_demission',
                        'file_path' => $data['justificatif'],
                        'file_name' => $data['justificatif_nom'] ?? null,
                    ]);

                    $record->forceFill([
                        'is_active' => false,
                        'date_fin' => $data['date_fin'],
                        'motif_depart' => $data['motif_depart'],
                    ])->save();
                });

                Notification::make()->title('Fin de contrat enregistrée')->success()->send();
            });
    }
}