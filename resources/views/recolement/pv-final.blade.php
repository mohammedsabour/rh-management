<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>المحضر النهائي الموحد للجرد</title>
    <style>
        body { font-family: xbriyaz, dejavusans, sans-serif; font-size: 11pt; direction: rtl; }
        h1 { text-align: center; font-size: 15pt; margin: 0 0 2mm; }
        h2 { text-align: center; font-size: 12pt; margin: 0 0 5mm; font-weight: normal; }
        h3 { font-size: 12pt; margin: 6mm 0 2mm; }
        p { margin: 0 0 3mm; line-height: 1.8; text-align: justify; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.3mm solid #000; padding: 1.2mm 1.5mm; text-align: center; vertical-align: middle; font-size: 9.5pt; }
        th { background: #e8e8e8; }
        td.r { text-align: right; }
        .sig td { border: none; width: 33%; text-align: right; padding: 2mm 1mm; font-size: 10pt; }
    </style>
</head>
<body>
    @php
        $case = fn (bool $v) => '<span style="font-family: dejavusans; font-size: 15pt;">' . ($v ? '&#9745;' : '&#9744;') . '</span>';
        $res = $inventaire->resultat_rapprochement;
        $lignesBilan = [
            'inspectes' => 'عدد العناصر التي تمت معاينتها',
            'concordants' => 'عناصر متطابقة',
            'ecarts' => 'اختلاف في الكمية أو الترقيم',
            'introuvables' => 'ممتلكات غير موجودة/غير متحقق منها',
            'abimes' => 'ممتلكات متضررة أو غير صالحة',
            'non_enregistres' => 'ممتلكات غير مسجلة أو غير مرقمة',
        ];
    @endphp

    <h1>المحضر النهائي الموحد للجرد ومطابقة نتائج اللجنتين</h1>
    <h2>{{ $chambre }}</h2>

    <p>
        في يوم {{ $dateReunion }}، وعلى الساعة {{ $heure }}، اجتمع أعضاء اللجنتين (أ) و(ب)، المعينتين
        بموجب المقرر رقم {{ $inventaire->decision_numero ?: '……/2026' }} المؤرخ في {{ $dateDecision }}،
        قصد مقارنة نتائج الجرد المادي المستقل الذي أنجزته كل لجنة.
    </p>

    <h3>أولاً: أعضاء اللجنتين</h3>
    <table>
        <thead><tr><th>اللجنة (أ)</th><th>التوقيع</th><th>اللجنة (ب)</th><th>التوقيع</th></tr></thead>
        <tbody>
            @for ($i = 0; $i < 3; $i++)
                <tr><td>{{ $membresA[$i] ?? '' }}</td><td>&nbsp;</td><td>{{ $membresB[$i] ?? '' }}</td><td>&nbsp;</td></tr>
            @endfor
        </tbody>
    </table>

    <h3>ثانيًا: جدول المقارنة والمطابقة</h3>
    <table>
        <thead>
            <tr>
                <th style="width:6%">رقم</th>
                <th style="width:9%">رقم الجرد</th>
                <th>تسمية الممتلك</th>
                <th>نتيجة اللجنة (أ)</th>
                <th>نتيجة اللجنة (ب)</th>
                <th>نتيجة المطابقة/الفارق</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r['n'] }}</td>
                    <td>{{ $r['inventaire'] }}</td>
                    <td class="r">{{ $r['designation'] }}</td>
                    <td class="r">{{ $r['a'] }}</td>
                    <td class="r">{{ $r['b'] }}</td>
                    <td>{{ $r['concordance'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>ثالثًا: الحصيلة الإجمالية</h3>
    <table>
        <thead><tr><th>البيان</th><th>اللجنة (أ)</th><th>اللجنة (ب)</th><th>بعد المطابقة</th></tr></thead>
        <tbody>
            @foreach ($lignesBilan as $cle => $libelle)
                <tr>
                    <td class="r">{{ $libelle }}</td>
                    <td>{{ $bilan[$cle]['a'] }}</td>
                    <td>{{ $bilan[$cle]['b'] }}</td>
                    <td>{{ $bilan[$cle]['final'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>رابعًا: نتيجة المطابقة</h3>
    <p>{!! $case($res === 'total') !!} تطابق تام بين نتائج اللجنتين.</p>
    <p>{!! $case($res === 'limite') !!} وجود اختلافات محدودة تمت مراجعتها وتسويتها وتوثيقها.</p>
    <p>{!! $case($res === 'substantiel') !!} وجود فوارق جوهرية تستوجب عرضها على مدير الغرفة للنظر في تشكيل لجنة ثالثة.</p>
    <p>تفصيل الفوارق والتحفظات : {{ $inventaire->details_rapprochement ?: '................................................' }}</p>

    <h3>خامسًا: التوصيات</h3>
    <p>– تحيين سجل الجرد وبطاقات الممتلكات وفق النتائج المثبتة.</p>
    <p>– استكمال ترقيم الممتلكات بعد التحقق من السجلات والوثائق.</p>
    <p>– إحالة حالات الممتلكات غير الموجودة أو مجهولة المكان إلى تحقق إداري مستقل، مع عدم اعتبارها مشطوبة بمجرد إثبات عدم العثور عليها.</p>
    <p>– اقتراح الصيانة أو الإصلاح أو إجراءات الشطب عند الاقتضاء، وفق الإجراءات المعمول بها وبقرار من السلطة المختصة.</p>
    <p>– إرفاق الكشوف التفصيلية للجنتين بهذا المحضر باعتبارها جزءًا لا يتجزأ منه.</p>

    <p>حرر هذا المحضر في {{ $dateRedaction }}، ووقع عليه أعضاء اللجنتين، ورفع إلى مدير الغرفة للاطلاع واتخاذ ما يراه مناسبًا.</p>

    <h3>توقيعات أعضاء اللجنتين</h3>
    @foreach ([['أ', $membresA], ['ب', $membresB]] as [$lettre, $liste])
        <table class="sig">
            <tr>
                @for ($i = 0; $i < 3; $i++)
                    <td>
                        <strong>اللجنة ({{ $lettre }}) – العضو {{ $i + 1 }}</strong><br>
                        الاسم واللقب : {{ $liste[$i] ?? '....................' }}<br>
                        التوقيع : ....................
                    </td>
                @endfor
            </tr>
        </table>
    @endforeach

    <h3>تأشيرة مدير الغرفة</h3>
    <p>
        {!! $case(false) !!} اعتماد النتائج
        &nbsp; {!! $case(false) !!} طلب استكمال/تحقق
        &nbsp; {!! $case(false) !!} تشكيل لجنة ثالثة
    </p>
    <p>ملاحظات المدير : ................................................</p>
    <table class="sig">
        <tr>
            <td>الاسم واللقب : {{ $directeur }}</td>
            <td>التاريخ : ……/……/……</td>
            <td>الإمضاء والختم</td>
        </tr>
    </table>
</body>
</html>