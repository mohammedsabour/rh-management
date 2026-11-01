<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>شهادة عمل</title>
    <style>
        body { font-family: xbriyaz, dejavusans, sans-serif; font-size: 15pt; color: #000; direction: rtl; }
        .entete { width: 170mm; }
        .corps { padding: 0 5mm; }   /* le texte garde ses marges de 2 cm ; seuls les traits vont à 1,5 cm */
        table.ref { width: 100%; border-collapse: collapse; margin-top: 4mm; font-size: 13pt; }
        table.ref td { padding: 0; }
        h1 { text-align: center; font-size: 26pt; margin: 22mm 0 16mm; font-weight: bold; }
        p { margin: 0 0 6mm; line-height: 1.9; text-align: justify; }
        .ligne { text-align: right; }
        .gras { font-weight: bold; }
        .cloture { margin-top: 10mm; font-weight: bold; }
        .signature { margin-top: 10mm; font-weight: bold; text-decoration: underline; text-align: left; width: 150mm; }
    </style>
</head>
<body>
    <htmlpagefooter name="pied">
        {{-- Trait au-dessus du pied de page (dessiné dans le modèle Word, absent de l'image) --}}
        <div style="border-top: 0.35mm solid #000; padding-top: 1mm; text-align: center;">
            <img src="{{ $pied }}" style="width: 170mm;">
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="pied" value="on" />

    {{-- Trait sous l'en-tête (dessiné dans le modèle Word, absent de l'image) --}}
    <div style="border-bottom: 0.35mm solid #000; padding-bottom: 1mm; text-align: center;">
        <img class="entete" src="{{ $entete }}">
    </div>

    <div class="corps">
    <table class="ref">
        <tr>
            <td style="text-align: right;">الـرقـم: {{ $numero ?: '......' }} /غ.ص.ت.ح/ {{ $annee }}</td>
            <td style="text-align: left;">حرر بمستغانم في : {{ $date }}</td>
        </tr>
    </table>

    <h1>شـهــــــادة عــمـــــل</h1>

    <p class="ligne">إن مدير غرفة الصناعة التقليدية والحرف لمستغانم يشهد بأن :</p>

    <p class="ligne">
        {{ $feminin ? 'السيدة' : 'السيد' }}: <span class="gras">{{ $nomComplet }}</span> .
    </p>

    <p class="ligne">
        تاريخ و مكان الازدياد:
        <span class="gras">
            {{ $user->date_naissance?->format('d/m/Y') ?? '........' }}
            ب{{ $commune ?: '........' }}
            @if ($wilaya) – ولاية {{ $wilaya }} @endif
        </span> .
    </p>

    <p class="ligne">
        @if ($user->date_fin)
            {{ $feminin ? 'كانت عاملة' : 'كان عاملا' }} بغرفة الصناعة التقليدية والحرف لمستغانم لممارسة مهام:
            <span class="gras">{{ $user->poste ?: '........' }}</span>
            منذ {{ $user->date_embauche?->format('d/m/Y') ?? '........' }} إلى غاية {{ $user->date_fin->format('d/m/Y') }}.
        @else
            {{ $feminin ? 'عاملة' : 'عامل' }} بغرفة الصناعة التقليدية والحرف لمستغانم لممارسة مهام:
            <span class="gras">{{ $user->poste ?: '........' }}</span>
            منذ {{ $user->date_embauche?->format('d/m/Y') ?? '........' }} إلى يومنا هذا.
        @endif
    </p>

    <p class="cloture">
        وقد سلمت هذه الشهادة بناءا على طلب{{ $feminin ? 'ها' : 'ه' }} للإدلاء بها فيما يخوله {{ $feminin ? 'لها' : 'له' }} القانون.
    </p>

    <div class="signature">الــمديــــر</div>
    </div>
</body>
</html>