<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\File;
use App\Models\GroupMember;
class CheckMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق إذا كان هناك 'group_id' في الطلب
        $group_id = $request->group_id;

        if ($group_id) {
            // إذا كان يوجد 'group_id' في الطلب
            $user_id = auth()->user()->id;

            // التحقق من أن المستخدم عضو في المجموعة
            $group_member = GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->exists();

            if ($group_member) {
                // إذا كان المستخدم عضوًا في المجموعة، اكمل تنفيذ الطلب
                return $next($request);
            } else {
                // إذا لم يكن المستخدم عضوًا في المجموعة
                return response()->json(['message' => 'You are not a member of this group'], 401);
            }
        }

        // التحقق إذا كان هناك 'file_id' في الطلب
        $hasParameterFileId = $request->has('file_id');
        if ($hasParameterFileId) {
            $file = File::find($request->file_id);
            if (!$file) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $group_id = $file->group_id;
            $user_id = auth()->user()->id;

            // التحقق من العضوية في المجموعة بناءً على 'file_id'
            $group_member = GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->exists();
            if ($group_member) {
                return $next($request);
            } else {
                return response()->json(['message' => 'You are not a member of this group'], 401);
            }
        }

        // التحقق إذا كان هناك 'member_id' في الطلب
        $hasParameterMemberId = $request->has('member_id');
        if ($hasParameterMemberId) {
            $memberId = $request->member_id;
            $userMember = GroupMember::where('user_id', $memberId)->exists();
            if ($userMember) {
                return $next($request);
            } else {
                return response()->json(['message' => 'The user is not a member of this group'], 401);
            }
        }

        // التحقق من المعرفات الأخرى في الطلب
        $hasParametersIds = $request->all();
        if ($hasParametersIds) {
            $isMember = false;

            foreach ($hasParametersIds as $key => $value) {
                if (preg_match('/^id(\d+)$/', $key)) { // تأكد أن المفتاح يبدأ بـ 'id'
                    $file = File::find($value);
                    if ($file) {
                        $group_id = $file->group_id;
                        $user_id = auth()->user()->id;

                        $group_member = GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->exists();
                        if (($group_id == 1) || ($group_member)) {
                            $isMember = true;
                            break; // إذا وجدنا مستخدمًا مناسبًا، يمكنك الخروج من الحلقة
                        }
                    }
                }
            }

            if ($isMember) {
                return $next($request);
            } else {
                return response()->json(['message' => 'You are not a member of this group'], 401);
            }
        }

        // في حال لم يكن هناك أي من المعلمات السابقة، السماح بالمتابعة
        return $next($request);
    }


}
