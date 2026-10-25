<?php

namespace App\Filament\Resources\DemandeConges\Tables;

use App\Models\DemandeConge;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use RuntimeException;

class DemandeCongesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('employe'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('employe.nom')
                    ->label('Employé')
                    ->state(fn (DemandeConge $r) => trim($r->employe->prenom.' '.$r->employe->nom))
                    ->searchable(['employe.nom', 'employe.prenom']),
 
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => DemandeConge::TYPES[$state] ?? $state),
 
                TextColumn::make('date_debut')->label('Du')->date('d/m/Y')->sortable(),
                TextColumn::make('date_fin')->label('Au')->date('d/m/Y')->sortable(),
                TextColumn::make('nombre_jours')->label('Jours')->sortable(),
 
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => DemandeConge::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'approuve' => 'success',
                        'refuse' => 'danger',
                        'annule' => 'gray',
                        default => 'warning',
                    }),
 
                TextColumn::make('created_at')->label('Envoyée le')->dateTime('d"/m/Y H:i')->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(DemandeConge::STATUTS),
                SelectFilter::make('type')->label('Type')->options(DemandeConge::TYPES),
            ])
            ->recordActions([
                ViewAction::make(),
                self::actionApprouver(),
                self::actionRefuser(),
                self::actionAnnuler(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Annulation par l'employé lui-même, tant que la demande est en attente
    public static function actionAnnuler(): Action
    {
        return Action::make('annuler')
            ->label('Annuler ma demande')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('gray')
            ->authorize('annuler')
            ->requiresConfirmation()
            ->action(function (DemandeConge $record) {
                $record->update(['statut' => 'annule']);
 
                Notification::make()->title('Demande annulée')->success()->send();
            });
    }

    public static function actionApprouver(): Action
    {
        return Action::make('approuver')
            ->label('Approuver')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->authorize('traiter')
            ->requiresConfirmation()
            ->modalHeading('Approuver cette demande ?')
            ->modalDescription(fn (DemandeConge $record) => $record->decompteLeSolde()
                ? "{$record->nombre_jours} jour(s) seront déduits du solde de l'employé."
                : "Ce type de congé ne modifie pas le solde de l'employé.")
            ->action(function (DemandeConge $record) {
                /** @var User $rh */
                $rh = Auth::user();
 
                try {
                    $record->approuver($rh);
                } catch (RuntimeException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
 
                    return;
                }
 
                Notification::make()->title('Demande approuvée')->success()->send();
            });
    }

    public static function actionRefuser(): Action
    {
        return Action::make('refuser')
            ->label('Refuser')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->authorize('traiter')
            ->modalHeading('Refuser cette demande')
            ->schema([
                Textarea::make('motif_refus')
                    ->label('Motif du refus')
                    ->helperText("L'employé verra ce message dans sa demande.")
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (DemandeConge $record, array $data) {
                /** @var User $rh */
                $rh = Auth::user();
 
                try {
                    $record->refuser($rh, $data['motif_refus']);
                } catch (RuntimeException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
 
                    return;
                }
 
                Notification::make()->title('Demande refusée')->success()->send();
            });
    }

    
}
