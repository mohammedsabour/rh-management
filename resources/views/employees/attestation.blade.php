<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>شهادة عمل</title>
    <style>
        body { font-family: xbriyaz, dejavusans, sans-serif; font-size: 15pt; color: #000; direction: rtl; }
        .entete { width: 100%; }
        table.ref { width: 100%; border-collapse: collapse; margin-top: 4mm; font-size: 13pt; }
        table.ref td { padding: 0; }
        h1 { text-align: center; font-size: 26pt; margin: 22mm 0 16mm; font-weight: bold; }
        p { margin: 0 0 6mm; line-height: 1.9; text-align: justify; }
        .ligne { text-align: right; }
        .gras { font-weight: bold; }
        .cloture { margin-top: 14mm; font-weight: bold; }
        .signature { margin-top: 9mm; font-weight: bold; text-decoration: underline; text-align: left; width: 150mm; }
    </style>
</head>
<body>
    <htmlpagefooter name="pied">
        <img src="{{ $pied }}" style="width: 100%;">
    </htmlpagefooter>
    <sethtmlpagefooter name="pied" value="on" />

    <img class="entete" src="{{ $entete }}">

    <table class="ref">
        <tr>
            <td style="text-align: right;">الـرقـم: ......  /غ.ص.ت.ح/ {{ $annee }}</td>
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
        {{ $feminin ? 'عاملة' : 'عامل' }} بغرفة الصناعة التقليدية والحرف لمستغانم لممارسة مهام:
        <span class="gras">{{ $user->poste ?: '........' }}</span>
        منذ {{ $user->date_embauche?->format('d/m/Y') ?? '........' }} إلى يومنا هذا.
    </p>

    <p class="cloture">
        وقد سلمت هذه الشهادة بناءا على طلب{{ $feminin ? 'ها' : 'ه' }} للإدلاء بها فيما يخوله {{ $feminin ? 'لها' : 'له' }} القانون.
    </p>

    <div class="signature">الــمديــــر</div>
</body>
</html>