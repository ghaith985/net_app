<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\File;
use App\Models\FileEvent;

class FileTracingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('file_id')) {
            return response()->json(['status' => false, 'message' => 'file_id is required'], 400);
        }

        $fileId = $request->file_id;
        $fileBeforeUpdate = File::find($fileId);

        if (!$fileBeforeUpdate) {
            return response()->json(['status' => false, 'message' => 'File not found'], 404);
        }

        $fileBefore = $fileBeforeUpdate->toArray();
        $filePathBefore = public_path($fileBeforeUpdate->path);
        $fileSizeBefore = file_exists($filePathBefore) ? filesize($filePathBefore) : 'File not found';

        $response = $next($request);

        $fileAfterUpdate = File::find($fileId);
        $fileAfter = $fileAfterUpdate->toArray();
        $filePathAfter = public_path($fileAfterUpdate->path);
        $fileSizeAfter = file_exists($filePathAfter) ? filesize($filePathAfter) : 'File not found';

        $user = auth()->user();

        if ($user) {
            FileEvent::create([
                'file_id' => $fileId,
                'user_id' => $user->id,
                'details' => "File updated by {$user->name} via Middleware",
                'date' => now(),
            ]);

            Log::info('File Update Tracing:', [
                'file_id' => $fileId,
                'user_name' => $user->name,
                'user_id' => $user->id,
                'before' => $fileBefore,
                'after' => $fileAfter,

                'date' => now()->toDateTimeString(),
            ]);
        }

        return $response;
    }


}
