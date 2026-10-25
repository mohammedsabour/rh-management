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
        p { text-align: center; font-size: 11pt;}
    </style>
</head>
<body>
    <h1>Attestation de travail</h1>
    <p>Je soussigné, certifie que M./Mme <strong>{{ $user->nom }} {{ $user->prenom }}</strong>...</p>
</body>
</html>