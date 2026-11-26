<x-filament-panels::page>
    @forelse ($this->commissions() as $commission)
        <x-filament::section :heading="$commission->inventaire->nom . ' — ' . $commission->libelle">
            <p style="margin-bottom:.75rem">
                @if ($commission->est_terminee)
                    Comptage terminé le {{ $commission->termine_le?->format('d/m/Y H:i') }}.
                @elseif ($commission->inventaire->statut === 'comptage')
                    Comptage en cours. Rôle :
                    <strong>{{ $commission->estPresident(auth()->id()) ? 'président' : 'membre' }}</strong>.
                @endif
            </p>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                @if ($commission->est_en_cours)
                    <x-filament::button tag="a" icon="heroicon-o-qr-code"
                        :href="\App\Filament\Pages\ComptageCommission::getUrl(['commission' => $commission->id])">
                        Compter (code-barres)
                    </x-filament::button>
                @endif

                @if ($commission->est_terminee)
                    <x-filament::button tag="a" color="gray" icon="heroicon-o-printer" target="_blank"
                        :href="route('recolement.pv-commission', $commission)">
                        Imprimer le PV
                    </x-filament::button>
                @endif
            </div>
        </x-filament::section>
    @empty
        <p>Vous ne faites partie d'aucune commission de récolement.</p>
    @endforelse
</x-filament-panels::page>