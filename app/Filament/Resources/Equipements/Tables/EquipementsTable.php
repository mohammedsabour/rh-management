<?php

namespace App\Filament\Resources\Equipements\Tables;

use App\Models\Affectation;
use App\Models\Departement;
use App\Models\Emplacement;
use App\Models\Equipement;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class EquipementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'categorie',
                'emplacement',
                'affectationEnCours.employe',
                'affectationEnCours.departement',
            ]))
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(36),
                
                TextColumn::make('code_barre')
                    ->label('Code-barres')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->description(fn (Equipement $record) => collect([$record->marque, $record->modele])->filter()->implode(' '))
                    ->searchable(['designation', 'marque', 'modele', 'numero_serie', 'numero_inventaire'])
                    ->sortable()
                    ->wrap(),

                TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->sortable(),

                TextColumn::make('etat')
                    ->label('État')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Equipement::ETATS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'neuf' => 'success',
                        'bon' => 'info',
                        'use' => 'warning',
                        'degrade' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Equipement::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'en_stock' => 'info',
                        'affecte' => 'success',
                        'en_maintenance' => 'warning',
                        'perdu' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('detenteur')
                    ->label('Détenteur')
                    ->state(function (Equipement $record) {
                        $affectation = $record->affectationEnCours;
 
                        if (! $affectation) {
                            return null;
                        }
 
                        return $affectation->employe
                            ? trim($affectation->employe->prenom . ' ' . $affectation->employe->nom)
                            : $affectation->departement?->nom;
                    })
                    ->placeholder('—'),
 
                TextColumn::make('emplacement.nom')
                    ->label('Emplacement')
                    ->placeholder('—')
                    ->toggleable(),
 
                TextColumn::make('numero_serie')
                    ->label('N° de série')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom'),
 
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(Equipement::STATUTS),
 
                SelectFilter::make('etat')
                    ->label('État')
                    ->options(Equipement::ETATS),
 
                SelectFilter::make('emplacement_id')
                    ->label('Emplacement')
                    ->relationship('emplacement', 'nom'),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::actionAffecter(),
                    self::actionRestituer(),

                    Action::make('etiquette')
                        ->label('Étiquette')
                        ->icon(Heroicon::OutlinedQrCode)
                        ->url(fn ($record) => route('inventaire.etiquettes', ['ids' => $record->id]))
                        ->openUrlInNewTab(),
                    ViewAction::make(),
                    EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('etiquettes')
                        ->label('Imprimer les étiquettes')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->action(fn (Collection $records) => redirect()->route('inventaire.etiquettes', [
                            'ids' => $records->pluck('id')->implode(','),
                        ]))
                        ->openUrlInNewTab()
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function actionAffecter(): Action
    {
        return Action::make('affecter')
            ->label('Affecter')
            ->icon(Heroicon::OutlinedUserPlus)
            ->color('success')
            ->visible(fn (Equipement $record) => in_array($record->statut, ['en_stock', 'affecte'], true))
            ->modalHeading(fn (Equipement $record) => 'Affecter « ' . $record->designation . ' »')
            ->modalSubmitActionLabel('Affecter')
            ->schema([
                Select::make('employe_id')
                    ->label('Employé')
                    ->options(fn () => User::orderBy('nom')->get()
                        ->mapWithKeys(fn (User $u) => [$u->id => trim($u->prenom . ' ' . $u->nom)]))
                    ->searchable()
                    ->requiredWithout('departement_id'),
 
                Select::make('departement_id')
                    ->label('Ou département (bien partagé)')
                    ->options(fn () => Departement::orderBy('nom')->pluck('nom', 'id'))
                    ->searchable()
                    ->requiredWithout('employe_id'),
 
                DatePicker::make('date_debut')
                    ->label("Date d'affectation")
                    ->default(now())
                    ->required(),
 
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2),
            ])
            ->action(function (Equipement $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    // On clôture l'affectation en cours (réaffectation directe), puis on en ouvre une nouvelle.
                    // Requête explicite : évite un UPDATE ... ORDER BY invalide sous PostgreSQL.
                    Affectation::where('equipement_id', $record->id)
                        ->whereNull('date_fin')
                        ->update(['date_fin' => $data['date_debut']]);
 
                    Affectation::create([
                        'equipement_id' => $record->id,
                        'employe_id' => $data['employe_id'] ?? null,
                        'departement_id' => $data['departement_id'] ?? null,
                        'date_debut' => $data['date_debut'],
                        'notes' => $data['notes'] ?? null,
                        'cree_par' => Auth::id(),
                    ]);
 
                    $record->update(['statut' => 'affecte']);
                });
 
                Notification::make()->success()->title('Équipement affecté')->send();
            });
    }
 
    private static function actionRestituer(): Action
    {
        return Action::make('restituer')
            ->label('Restituer')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('warning')
            ->visible(fn (Equipement $record) => $record->statut === 'affecte')
            ->modalHeading(fn (Equipement $record) => 'Restituer « ' . $record->designation . ' »')
            ->modalSubmitActionLabel('Restituer')
            ->schema([
                DatePicker::make('date_fin')
                    ->label('Date de restitution')
                    ->default(now())
                    ->required(),
 
                Select::make('emplacement_id')
                    ->label('Rangé dans (emplacement)')
                    ->options(fn () => Emplacement::with('parent.parent.parent')->get()
                        ->mapWithKeys(fn (Emplacement $e) => [$e->id => $e->chemin_complet]))
                    ->searchable(),
            ])
            ->action(function (Equipement $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    Affectation::where('equipement_id', $record->id)
                        ->whereNull('date_fin')
                        ->update(['date_fin' => $data['date_fin']]);
 
                    $record->update([
                        'statut' => 'en_stock',
                        'emplacement_id' => $data['emplacement_id'] ?? $record->emplacement_id,
                    ]);
                });
 
                Notification::make()->success()->title('Équipement restitué')->send();
            });
    }
}
