<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inventaire comptable</title>
    <style>
        @page { margin: 12mm 10mm 18mm 10mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8pt; color: #000; }
        .entite { font-size: 11pt; margin: 0 0 2mm; }
        h1 { text-align: center; font-size: 14pt; margin: 0 0 4mm; }

        table.inventaire { width: 100%; border-collapse: collapse; }
        .inventaire th, .inventaire td { border: 0.3mm solid #000; padding: 1.2mm 1.5mm; vertical-align: middle; }
        .inventaire thead th { background: #e3e3e3; text-align: center; }
        .inventaire tr { page-break-inside: avoid; }
        .c { text-align: center; }
        .r { text-align: right; white-space: nowrap; }
        .exercice td, .total td { font-weight: bold; background: #f1f1f1; }
        .grand-total td { font-weight: bold; background: #e3e3e3; }

        .pied { position: fixed; bottom: -12mm; left: 0; right: 0; padding: 1.5mm 3mm; font-size: 8pt; }
        .pied .page { float: right; }
        .pied .page:after { content: counter(page) " / " counter(pages); }
    </style>
</head>
<body>
    @php
        $nombre = fn ($v) => $v === null ? '—' : str_replace(' ', "\u{00A0}", number_format($v, 2, ',', ' '));
    @endphp

    <div class="pied">
        <span class="page"></span>
    </div>

    <p class="entite">Entité : {{ $entite }}</p>
    <h1>Inventaire comptable</h1>

    <table class="inventaire">
        <thead>
            <tr>
                <th style="width: 14%">N° d'inventaire</th>
                <th style="width: 9%">N° de<br>Compte</th>
                <th style="width: 33%">Désignation de l'article</th>
                <th style="width: 13%">Date<br>comptabilisation</th>
                <th style="width: 16%">Montant</th>
                <th style="width: 15%">État de<br>article</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($exercices as $exercice)
                <tr class="exercice">
                    <td colspan="6">
                        @if ($exercice['annee'])
                            L'exercice {{ $exercice['annee'] }}
                        @else
                            Date d'acquisition non renseignée
                        @endif
                    </td>
                </tr>

                @foreach ($exercice['lignes'] as $ligne)
                    <tr>
                        <td class="c">{{ $ligne['code'] }}</td>
                        <td class="c">{{ $ligne['compte'] }}</td>
                        <td>{{ $ligne['designation'] }}</td>
                        <td class="c">{{ $ligne['date']?->format('d/m/Y') ?? '—' }}</td>
                        <td class="r">{{ $nombre($ligne['valeur']) }}</td>
                        <td class="c">{{ $ligne['etat'] }}</td>
                    </tr>
                @endforeach

                <tr class="total">
                    <td colspan="4" class="r">Total {{ $exercice['annee'] ? 'exercice ' . $exercice['annee'] : 'sans date' }}</td>
                    <td class="r">{{ $nombre($exercice['valeur']) }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="c">Aucun équipement enregistré.</td>
                </tr>
            @endforelse

            @if ($exercices->isNotEmpty())
                <tr class="grand-total">
                    <td colspan="4" class="r">TOTAL GÉNÉRAL</td>
                    <td class="r">{{ $nombre($valeurTotale) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>