<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CaseNotification;

class AuthController extends Controller
{
    // 1. تسجيل مستخدم جديد (عميل أو محامي أو مكتب)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,lawyer,office', // تحديد الدور
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // إذا كان المستخدم محامي أو مكتب، ننشئ له بروفايل "فارغ" في انتظار رفع الأوراق
        if (in_array($user->role, ['lawyer', 'office'])) {
            ProviderProfile::create([
                'user_id' => $user->id,
                'status' => 'pending' // حالته معلقة حتى يرفع الأوراق ويقبلها الأدمن
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم التسجيل بنجاح',
            'access_token' => $token,
            'user' => $user
        ], 201);
    }

 // 2. تسجيل الدخول المعدل
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // إبطال التوكنات القديمة لزيادة الأمان
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // منطق تحديد حالة البروفايل بشكل آمن
        $profileStatus = 'n/a';
        
        // إذا كان المستخدم محامي أو مكتب، نحاول جلب حالته
        if (in_array($user->role, ['lawyer', 'office'])) {
            // نستخدم الـ optional أو نتحقق من الوجود لتجنب خطأ الـ null
            $profileStatus = $user->providerProfile ? $user->providerProfile->status : 'pending';
        } 
        // إذا كان أدمن أو موظف
        elseif (in_array($user->role, ['admin', 'employee'])) {
            $profileStatus = 'verified_admin';
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'profile_status' => $profileStatus
        ]);
    }
    
    // 3. تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }
    public function getNotifications()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications->count(),
            'notifications' => $user->notifications // يعيد آخر الإشعارات
        ]);
    }

    public function markAsRead($id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['message' => 'تم القراءة']);
    }
}