<x-filament-panels::page>
    @php
        $couleurs = ['ok' => '#16a34a', 'deja' => '#6b7280', 'inconnu' => '#dc2626', 'ferme' => '#dc2626', 'incomplet' => '#ea580c'];
        $dernier = $this->historique[0] ?? null;
    @endphp

    <style>
        .sc-reglages { display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1rem; }
        .sc-retour { color: #fff; border-radius: .5rem; padding: 1rem 1.25rem; }
        .sc-retour strong { display: block; font-size: 1.25rem; }
        .sc-ligne { display: flex; gap: .75rem; align-items: baseline; padding: .35rem 0; border-bottom: 1px solid rgba(128,128,128,.2); font-size: .9rem; }
        .sc-pastille { width: .7rem; height: .7rem; border-radius: 50%; flex: none; align-self: center; }
    </style>

    {{-- Statistiques du comptage de CETTE commission (jamais l'emplacement ni l'état enregistrés) --}}
    @php $stats = $this->statistiques(); @endphp
    <x-filament::section>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap:1rem; text-align:center">
            <div><div style="font-size:.8rem; opacity:.7">À contrôler</div><strong style="font-size:1.6rem">{{ $stats['a_controler'] }}</strong></div>
            <div><div style="font-size:.8rem; opacity:.7">Retrouvés</div><strong style="font-size:1.6rem; color:#16a34a">{{ $stats['retrouves'] }}</strong></div>
            <div><div style="font-size:.8rem; opacity:.7">Restant</div><strong style="font-size:1.6rem; color:#ea580c">{{ $stats['restant'] }}</strong></div>
            <div><div style="font-size:.8rem; opacity:.7">Avancement</div><strong style="font-size:1.6rem">{{ $stats['avancement'] }} %</strong></div>
        </div>
        <div style="margin-top:.75rem; height:.5rem; border-radius:999px; background:rgba(128,128,128,.25); overflow:hidden">
            <div style="height:100%; width:{{ $stats['avancement'] }}%; background:#16a34a"></div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="sc-reglages">
            <div>
                <label for="code-scan" style="font-weight:600">Code-barres</label>
                <x-filament::input.wrapper>
                    <x-filament::input id="code-scan" type="text" wire:model="code" wire:keydown.enter="scanner"
                        autofocus autocomplete="off"
                        placeholder="Scannez un code-barres ou saisissez un n° d'inventaire, puis Entrée" />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label style="font-weight:600">Je suis dans (obligatoire)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="emplacementId">
                        <option value="">— Choisir l'emplacement —</option>
                        @foreach ($this->emplacements() as $id => $nom)
                            <option value="{{ $id }}">{{ $nom }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label style="font-weight:600">État constaté (obligatoire)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="etat">
                        <option value="">— Choisir l'état —</option>
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

    @if (count($this->historique) > 0)
        <x-filament::section heading="Derniers enregistrements">
            @foreach ($this->historique as $ligne)
                <div class="sc-ligne">
                    <span class="sc-pastille" style="background: {{ $couleurs[$ligne['type']] ?? '#6b7280' }}"></span>
                    <span>{{ $ligne['heure'] }}</span>
                    <strong>{{ $ligne['designation'] ?? $ligne['code'] }}</strong>
                    <span>{{ $ligne['message'] }}</span>
                    @if ($ligne['type'] === 'ok' && $ligne['ligne_id'])
                        <button type="button" wire:click="annuler({{ $ligne['ligne_id'] }})"
                            wire:confirm="Annuler cet enregistrement ?"
                            style="margin-left:auto; color:#dc2626; font-size:.8rem">Annuler</button>
                    @endif
                </div>
            @endforeach
        </x-filament::section>
    @endif
</x-filament-panels::page>