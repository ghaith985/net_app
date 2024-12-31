<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SanctumSettingsController extends Controller
{
    public function setExpiration(Request $request)
    {
        // التحقق من صحة الإدخال
        $request->validate([
            'expiration_minutes' => 'required|integer|min:1',
        ]);

        $filePath = config_path('sanctum.php'); // مسار ملف التهيئة
        $fileContent = file_get_contents($filePath);

        // تعديل القيمة في ملف التهيئة
        $newContent = preg_replace(
            "/('expiration'\s*=>\s*)\d+,/",
            "'expiration' => {$request->expiration_minutes},",
            $fileContent
        );

        if ($newContent) {
            file_put_contents($filePath, $newContent);
        }

        // مسح الكاش لتطبيق التعديلات
        Artisan::call('config:cache');

        return response()->json([
            'message' => 'Token expiration time updated successfully.',
            'expiration_minutes' => Config::get('sanctum.expiration'),
        ]);
    }

}
