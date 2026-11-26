<?php

namespace App\Filament\Resources\Inventaires\Schemas;

use App\Models\CommissionInventaire;
use App\Models\CommissionMembre;
use App\Models\Inventaire;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InventaireInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('statut')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (string $state) => Inventaire::STATUTS[$state] ?? $state)
                ->color(fn (string $state) => match ($state) {
                    'comptage' => 'warning',
                    'rapprochement' => 'info',
                    default => 'success',
                }),

            TextEntry::make('date_debut')->label('Début')->date('d/m/Y'),
            TextEntry::make('date_fin')->label('Fin')->date('d/m/Y')->placeholder('En cours'),

            TextEntry::make('perimetre')
                ->label('Périmètre')
                ->state(fn (Inventaire $r) => $r->emplacement?->chemin_complet ?? 'Tous les emplacements'),

            TextEntry::make('decision_numero')->label('Décision de nomination')
                ->state(fn (Inventaire $r) => $r->decision_numero
                    ? $r->decision_numero . ($r->decision_date ? ' du ' . $r->decision_date->format('d/m/Y') : '')
                    : null)
                ->placeholder('—'),

            TextEntry::make('createur.nom')
                ->label('Créé par')
                ->placeholder('—')
                ->formatStateUsing(fn ($record) => "{$record->createur->nom} {$record->createur->prenom}"),
                
            self::commission('A'),
            self::commission('B'),

            TextEntry::make('ecarts')
                ->label('Écarts entre les deux commissions')
                ->state(fn (Inventaire $r) => $r->en_comptage ? '—' : $r->nombreEcarts())
                ->visible(fn (Inventaire $r) => ! $r->en_comptage),
        ]);
    }

    private static function commission(string $code): TextEntry
    {
        return TextEntry::make("commission_{$code}")
            ->label("Commission {$code}")
            ->listWithLineBreaks()
            ->state(function (Inventaire $r) use ($code) {
                /** @var CommissionInventaire|null $c */
                $c = $r->commissions()->with('membres.user')->where('code', $code)->first();

                if (! $c) {
                    return [];
                }

                $etat = $c->est_terminee ? 'comptage terminé' : 'comptage en cours';

                return array_merge(
                    ["({$etat})"],
                    $c->membres->sortBy(fn (CommissionMembre $m) => $m->role === 'president' ? 0 : 1)
                        ->map(fn (CommissionMembre $m) => trim($m->user->prenom . ' ' . $m->user->nom)
                            . ' — ' . CommissionMembre::ROLES[$m->role])
                        ->all(),
                );
            });
    }
}
