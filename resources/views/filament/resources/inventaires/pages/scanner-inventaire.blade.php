<x-filament-panels::page>
    @php
        $stats = $this->record->statistiques();
        $couleurs = [
            'ok' => '#16a34a', 'deplace' => '#ea580c', 'hors_perimetre' => '#ca8a04', 'attention' => '#ca8a04',
            'deja' => '#6b7280', 'inconnu' => '#dc2626', 'ferme' => '#dc2626',
        ];
        $dernier = $this->historique[0] ?? null;
    @endphp

    <style>
        .sc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .75rem; }
        .sc-stat { border: 1px solid rgba(128,128,128,.3); border-radius: .5rem; padding: .6rem .9rem; }
        .sc-stat b { display: block; font-size: 1.5rem; }
        .sc-reglages { display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1rem; }
        .sc-retour { color: #fff; border-radius: .5rem; padding: 1rem 1.25rem; }
        .sc-retour strong { display: block; font-size: 1.25rem; }
        .sc-ligne { display: flex; gap: .75rem; align-items: baseline; padding: .35rem 0; border-bottom: 1px solid rgba(128,128,128,.2); font-size: .9rem; }
        .sc-pastille { width: .7rem; height: .7rem; border-radius: 50%; flex: none; align-self: center; }
    </style>

    <div class="sc-stats">
        <div class="sc-stat"><span>À contrôler</span><b>{{ $stats['attendus'] }}</b></div>
        <div class="sc-stat"><span>Retrouvés</span><b style="color:#16a34a">{{ $stats['retrouves'] }}</b></div>
        <div class="sc-stat"><span>Restant</span><b style="color:#dc2626">{{ $stats['manquants'] }}</b></div>
        <div class="sc-stat"><span>Déplacés</span><b style="color:#ea580c">{{ $stats['deplaces'] }}</b></div>
        <div class="sc-stat"><span>Avancement</span><b>{{ $stats['avancement'] }} %</b></div>
    </div>

    <x-filament::section>
        <div class="sc-reglages">
            <div>
                <label for="code-scan" style="font-weight:600">Code-barres</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        id="code-scan"
                        type="text"
                        wire:model="code"
                        wire:keydown.enter="scanner"
                        autofocus
                        autocomplete="off"
                        placeholder="Scannez un code-barres ou saisissez un n° d'inventaire, puis Entrée"
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label style="font-weight:600">Je suis dans (emplacement constaté)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="emplacementId">
                        <option value="">Emplacement enregistré du bien</option>
                        @foreach ($this->emplacements() as $id => $nom)
                            <option value="{{ $id }}">{{ $nom }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label style="font-weight:600">État constaté (facultatif)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="etat">
                        <option value="">État enregistré du bien</option>
                        @foreach ($this->etats() as $valeur => $libelle)
                            <option value="{{ $valeur }}">{{ $libelle }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        @if ($dernier)
            <div class="sc-retour" style="background: {{ $couleurs[$dernier['type']] ?? '#6b7280' }}; margin-top: 1rem;">
                <strong>{{ $dernier['designation'] ?? $dernier['code'] }}</strong>
                {{ $dernier['message'] }}
            </div>
        @endif
    </x-filament::section>

    @if (count($this->historique) > 1)
        <x-filament::section heading="Derniers pointages">
            @foreach ($this->historique as $ligne)
                <div class="sc-ligne">
                    <span class="sc-pastille" style="background: {{ $couleurs[$ligne['type']] ?? '#6b7280' }}"></span>
                    <span>{{ $ligne['heure'] }}</span>
                    <strong>{{ $ligne['designation'] ?? $ligne['code'] }}</strong>
                    <span>{{ $ligne['message'] }}</span>
                </div>
            @endforeach
        </x-filament::section>
    @endif
</x-filament-panels::page>