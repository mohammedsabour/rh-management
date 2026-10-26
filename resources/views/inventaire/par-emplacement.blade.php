<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Équipements par emplacement</title>
    <style>
        @page { margin: 12mm 10mm 16mm 10mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; color: #000; }
        .entite { font-size: 11pt; margin: 0 0 2mm; }

        table { width: 100%; border-collapse: collapse; margin-top: 10mm;}
        th, td { border: 0.3mm solid #000; padding: 1.3mm 1.5mm; vertical-align: middle; }
        thead th { background: #e3e3e3; text-align: center; }
        tr { page-break-inside: avoid; }
        .c { text-align: center; }
        .lieu td { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; background: #f1f1f1; }
        .page-suivante { page-break-before: always; }

        .pied { position: fixed; bottom: -11mm; left: 0; right: 0; border-top: 0.3mm solid #000; padding-top: 1mm; font-size: 7.5pt; }
        .pied .page { float: right; }
        .pied .page:after { content: counter(page) " / " counter(pages); }
    </style>
</head>
<body>
    <div class="pied">
        Équipements / {{ $date }} / {{ $entite }}
        <span class="page"></span>
    </div>

    @forelse ($groupes as $groupe)
        <div class="{{ $loop->first ? '' : 'page-suivante' }}">
            <p class="entite">Entité : {{ $entite }}</p>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">N°</th>
                        <th style="width: 15%">N° d'inventaire</th>
                        <th style="width: 40%">Désignation</th>
                        <th style="width: 5%">Q</th>
                        <th style="width: 15%">État de l'article</th>
                        <th style="width: 20%">Observation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="lieu"><td colspan="6">{{ $groupe['lieu'] }}</td></tr>
                    @foreach ($groupe['lignes'] as $ligne)
                        <tr>
                            <td class="c">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="c"><b>{{ $ligne['code'] }}</b></td>
                            <td>{{ $ligne['designation'] }}</td>
                            <td class="c">1</td>
                            <td class="c">{{ $ligne['etat'] }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="entite">Entité : {{ $entite }}</p>
        <p class="c">Aucun équipement.</p>
    @endforelse
</body>
</html>