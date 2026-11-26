<x-filament-panels::page>
    @php $bilan = $this->record->bilan(); @endphp

    <x-filament::section heading="Bilan">
        <div style="overflow-x:auto">
            <table style="width:100%; border-collapse:collapse; font-size:.9rem">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid rgba(128,128,128,.3)">
                        <th style="padding:.4rem">Libellé</th>
                        <th style="padding:.4rem">Commission A</th>
                        <th style="padding:.4rem">Commission B</th>
                        <th style="padding:.4rem">Après rapprochement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ([
                        'inspectes' => 'Biens retrouvés',
                        'concordants' => 'Biens concordants',
                        'ecarts' => 'Écarts entre les deux commissions (emplacement, état, trouvé par une seule)',
                        'introuvables' => 'Biens attendus non retrouvés',
                        'abimes' => 'Biens endommagés ou hors service',
                        'non_enregistres' => 'Biens retrouvés mais non attendus',
                    ] as $cle => $libelle)
                        <tr style="border-bottom:1px solid rgba(128,128,128,.15)">
                            <td style="padding:.4rem">{{ $libelle }}</td>
                            <td style="padding:.4rem">{{ $bilan[$cle]['a'] }}</td>
                            <td style="padding:.4rem">{{ $bilan[$cle]['b'] }}</td>
                            <td style="padding:.4rem"><strong>{{ $bilan[$cle]['final'] }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>