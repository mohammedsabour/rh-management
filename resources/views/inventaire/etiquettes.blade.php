<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Étiquettes code-barres</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #eee; }

        /* Dimensions d'une étiquette (modifiables) */
        :root { --largeur: 60mm; --hauteur: 25mm; }

        .etiquette {
            width: var(--largeur);
            height: var(--hauteur);
            background: #fff;
            padding: 1.5mm 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
        }
        .societe { font-size: 9pt; font-weight: bold; line-height: 1; margin: 0; }
        .etiquette img { width: 100%; height: 12mm; image-rendering: crisp-edges; }
        .code { font-size: 8pt; letter-spacing: .5px; line-height: 1; margin: 0; }

        .barre-outils { padding: 10px; background: #fff; text-align: center; }
        .barre-outils button { padding: 6px 16px; font-size: 14px; cursor: pointer; }

        /* Aperçu à l'écran */
        .planche { display: flex; flex-wrap: wrap; gap: 4mm; padding: 10mm; }
        .etiquette { border: 1px solid #ccc; }

        @media print {
            body { background: #fff; }
            .barre-outils { display: none; }
            .etiquette { border: none; }
        }

        @if ($format === 'rouleau')
        /* Imprimante d'étiquettes : une étiquette par page */
        @page { size: var(--largeur) var(--hauteur); margin: 0; }
        @media print {
            .planche { display: block; padding: 0; }
            .etiquette { page-break-after: always; }
        }
        @else
        /* Feuille A4 : planche de plusieurs étiquettes */
        @page { size: A4; margin: 10mm; }
        @media print {
            .planche { padding: 0; gap: 2mm; }
            .etiquette { break-inside: avoid; border: 0.2mm dotted #bbb; }
        }
        @endif
    </style>
</head>
<body>
    <div class="barre-outils">
        <button onclick="window.print()">Imprimer ({{ $etiquettes->count() }} étiquette{{ $etiquettes->count() > 1 ? 's' : '' }})</button>
    </div>

    <div class="planche">
        @foreach ($etiquettes as $etiquette)
            <div class="etiquette">
                <p class="societe">{{ $societe }}</p>
                <img src="{{ $etiquette['image'] }}" alt="{{ $etiquette['code'] }}">
                <p class="code">{{ $etiquette['code'] }}</p>
            </div>
        @endforeach
    </div>
</body>
</html>