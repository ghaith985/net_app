<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFileOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تحقق من وجود file_id في الطلب
        $fileId = $request->input('file_id'); // استخدم input للحصول على القيمة من الطلب
        if (!$fileId) {
            return response()->json(['status' => false, 'message' => 'File ID is required.'], 400);
        }

        // ابحث عن الملف باستخدام file_id
        $file = File::find($fileId);
        if (!$file) {
            return response()->json(['status' => false, 'message' => 'File not found.'], 404);
        }

        // تحقق من أن المستخدم هو مالك الملف
        if ($file->user_id !== auth()->user()->id) {
            return response()->json(['status' => false, 'message' => 'You are not the owner of this file.'], 403);
        }

        return $next($request);
    }
}
