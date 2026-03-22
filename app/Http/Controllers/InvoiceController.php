<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class InvoiceController extends Controller
{
public function exportTxt()
{
    // نهاية السطر المطلوبة في أنظمة الويندوز
    $eol = "\r\n";

    // ⚠️ مهم: لا تضع مسافات إضافية
    $content  = "V=2.0".$eol;

    $content .= "C=51daa647-dd5a-4621-9349-83ed44f2759d"
        ."\t$\tدولار\t595.00\tسنت\t0\t01-01-1980\t0\tدولار\t\t0.00"
        .$eol;

    $content .= "I=f0a77d14-571b-4c73-9415-5eba5e0ae225"
        ."\t1.00\t1\t13000.00\t0.00\t0.00\t1\t\t0.00\t0.00\t0.00\t0.00\t0.00\t0.00\t0.00"
        ."\t\t01-01-1980\t01-01-1980\t0.00"
        .$eol;

    // ✅ التحويل الصحيح للعربية
    $content = iconv('UTF-8', 'CP1256//TRANSLIT//IGNORE', $content);

    return response($content, 200, [
        'Content-Type' => 'text/plain; charset=windows-1256',
        'Content-Disposition' => 'attachment; filename="invoice.txt"',
    ]);
}


/**
 * يحاول تحويل UTF-8 إلى Windows-1256 (CP1256) باستخدام mbstring أو iconv مع fallbacks.
 */
private function convertToWindows1256(string $text): string
{
    // أسماء الترميزات المرشحة
    $candidates = ['CP1256', 'WINDOWS-1256', 'ISO-8859-6'];

    // 1) جرب mb_convert_encoding إن كانت متاحة وتدعم الترميز
    if (function_exists('mb_list_encodings') && function_exists('mb_convert_encoding')) {
        $available = array_map('strtoupper', mb_list_encodings());
        foreach ($candidates as $enc) {
            if (in_array(strtoupper($enc), $available)) {
                // استخدم أول ترميز متاح
                try {
                    return mb_convert_encoding($text, $enc, 'UTF-8');
                } catch (\Throwable $e) {
                    // تجاهل وجرّب التالي
                }
            }
        }
    }

    // 2) اذا فشل، جرب iconv إن كانت متاحة
    if (function_exists('iconv')) {
        foreach ($candidates as $enc) {
            $target = $enc . '//TRANSLIT'; // أو //IGNORE حسب ما تفضّل
            $converted = @iconv('UTF-8', $target, $text);
            if ($converted !== false) {
                return $converted;
            }
        }
    }

    // 3) fallback: إرجاع النص كما هو (UTF-8) — ولكن سنبلّغ المستخدم / السجل لاحقاً إن أردت
    return $text;
}


}
