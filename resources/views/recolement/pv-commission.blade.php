<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>محضر الجرد المادي الأولي</title>
    <style>
        body { font-family: xbriyaz, dejavusans, sans-serif; font-size: 11pt; direction: rtl; }
        h1 { text-align: center; font-size: 16pt; margin: 0 0 2mm; }
        h2 { text-align: center; font-size: 12pt; margin: 0 0 5mm; font-weight: normal; }
        h3 { font-size: 12pt; margin: 6mm 0 2mm; }
        p { margin: 0 0 3mm; line-height: 1.8; text-align: justify; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.3mm solid #000; padding: 1.2mm 1.5mm; text-align: center; vertical-align: middle; font-size: 10pt; }
        th { background: #e8e8e8; }
        td.r { text-align: right; }
        .sans-bord td { border: none; text-align: right; padding: 1mm 0; }
        .sig td { border: none; width: 33%; text-align: right; padding: 2mm 1mm; font-size: 10pt; }
    </style>
</head>
<body>
    <h1>محضر الجرد المادي الأولي – اللجنة ({{ $commission->lettre_ar }})</h1>
    <h2>{{ $chambre }}</h2>

    <p>
        عمًلا بالمقرر رقم {{ $inventaire->decision_numero ?: '……/2026' }} المؤرخ في {{ $dateDecision }}
        المتضمن تعيين لجنتي الجرد، اجتمعت اللجنة ({{ $commission->lettre_ar }}) بتاريخ {{ $dateReunion }}
        على الساعة {{ $heure }}، بمقر/مرافق الغرفة، للشروع في الجرد المادي المستقل للممتلكات والتجهيزات.
    </p>

    <h3>أعضاء اللجنة</h3>
    <table>
        <thead><tr><th>الاسم واللقب</th><th>التوقيع</th></tr></thead>
        <tbody>
            @foreach ($membres as $nom)
                <tr><td>{{ $nom }}</td><td>&nbsp;</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>كشف الجرد</h3>
    <table>
        <thead>
            <tr>
                <th style="width:8%">رقم</th>
                <th>تسمية الممتلك</th>
                <th>رقم الجرد</th>
                <th style="width:8%">الكمية</th>
                <th>الحالة المادية</th>
                <th>مكان التواجد</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r['n'] }}</td>
                    <td class="r">{{ $r['designation'] }}</td>
                    <td>{{ $r['inventaire'] }}</td>
                    <td>{{ $r['quantite'] }}</td>
                    <td>{{ $r['etat'] }}</td>
                    <td class="r">{{ $r['lieu'] }}</td>
                    <td>{{ $r['remarque'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>خلاصة اللجنة</h3>
    <p>– عدد الممتلكات/العناصر التي تمت معاينتها : {{ $resume['inspectes'] }}</p>
    <p>– الممتلكات غير الموجودة أو التي تعذر التحقق منها : {{ $resume['introuvables'] }}</p>
    <p>– الممتلكات غير المرقمة أو التي تحمل ترقيمًا غير واضح : {{ $resume['non_numerotes'] ?? '........' }}</p>
    <p>– الممتلكات المتضررة أو غير الصالحة للاستعمال : {{ $resume['abimes'] }}</p>
    <p>– ملاحظات أخرى : {{ $commission->observations ?: '................................................' }}</p>

    <p>تؤكد اللجنة أن هذا المحضر يعكس نتائج المعاينة المادية التي قامت بها بصورة مستقلة، دون مطابقة مسبقة مع نتائج اللجنة الأخرى.</p>
    <p>حرر بمستغانم في : {{ $dateRedaction }}</p>

    <h3>إمضاءات أعضاء اللجنة</h3>
    <table class="sig">
        <tr>
            @foreach (['العضو الأول', 'العضو الثاني', 'العضو الثالث'] as $i => $titre)
                <td>
                    <strong>{{ $titre }}</strong><br>
                    الاسم واللقب : {{ $membres[$i] ?? '....................' }}<br><br>
                    التوقيع : ....................
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>
