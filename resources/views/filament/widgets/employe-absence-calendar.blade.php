<x-filament-widgets::widget>
    <x-filament::section heading="Calendrier des absences">
        <style>
            .ac-legende { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; font-size: .85rem; }
            .ac-legende span { display: inline-flex; align-items: center; gap: .4rem; }
            .ac-legende i { width: .85rem; height: .85rem; border-radius: 50%; display: inline-block; }
            .ac-barre { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
            .ac-nav, .ac-vues { display: flex; align-items: center; gap: .4rem; }
            .ac-titre { font-size: 1.35rem; font-weight: 400; text-transform: capitalize; }
            .ac-grille { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-top: 1px solid rgba(128,128,128,.3); border-left: 1px solid rgba(128,128,128,.3); }
            .ac-entete, .ac-cellule { border-right: 1px solid rgba(128,128,128,.3); border-bottom: 1px solid rgba(128,128,128,.3); }
            .ac-entete { text-align: center; font-weight: 600; padding: .5rem 0; font-size: .85rem; }
            .ac-cellule { min-height: 5.5rem; padding: .25rem .35rem; font-size: .85rem; }
            .ac-semaine .ac-cellule { min-height: 9rem; }
            .ac-cellule .ac-num { text-align: right; }
            .ac-cellule.ac-hors { opacity: .4; }
            .ac-cellule.ac-weekend { background: rgba(128,128,128,.08); }
            .ac-cellule.ac-aujourdhui { background: rgba(234,179,8,.15); }
            .ac-cellule.ac-aujourdhui .ac-num { font-weight: 700; }
            .ac-evt { color: #fff; border-radius: .25rem; padding: .05rem .35rem; margin-top: .2rem; font-size: .72rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        </style>

        <div class="ac-legende">
            @foreach ($legende as $item)
                <span><i style="background: {{ $item['couleur'] }}"></i>{{ $item['label'] }}</span>
            @endforeach
        </div>

        <div class="ac-barre">
            <div class="ac-nav">
                <x-filament::icon-button icon="heroicon-m-chevron-left" wire:click="precedent" label="Précédent" />
                <x-filament::icon-button icon="heroicon-m-chevron-right" wire:click="suivant" label="Suivant" />
                <x-filament::button size="sm" color="gray" wire:click="aujourdhui">Aujourd'hui</x-filament::button>
            </div>

            <div class="ac-titre">{{ $titre }}</div>

            <div class="ac-vues">
                <x-filament::button size="sm" :color="$vue === 'mois' ? 'primary' : 'gray'" wire:click="choisirVue('mois')">Mois</x-filament::button>
                <x-filament::button size="sm" :color="$vue === 'semaine' ? 'primary' : 'gray'" wire:click="choisirVue('semaine')">Semaine</x-filament::button>
            </div>
        </div>

        <div class="ac-grille {{ $vue === 'semaine' ? 'ac-semaine' : '' }}">
            @foreach ($joursSemaine as $nom)
                <div class="ac-entete">{{ $nom }}</div>
            @endforeach

            @foreach ($jours as $jour)
                <div @class([
                    'ac-cellule',
                    'ac-hors' => ! $jour['dansMois'],
                    'ac-weekend' => $jour['weekend'],
                    'ac-aujourdhui' => $jour['aujourdhui'],
                ])>
                    <div class="ac-num">{{ $jour['date']->day }}</div>
                    @foreach ($jour['evenements'] as $evt)
                        <div class="ac-evt" style="background: {{ $evt['couleur'] }}" title="{{ $evt['label'] }}">{{ $evt['label'] }}</div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>