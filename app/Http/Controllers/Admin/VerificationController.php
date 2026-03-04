<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // عرض كل الطلبات المعلقة للأدمن
    public function getPendingRequests()
    {
        $requests = ProviderProfile::with('user')->where('status', 'pending')->get();
        return response()->json($requests);
    }

    // اتخاذ قرار (قبول أو رفض)
    public function verify(Request $request, $id)
    {
        // السماح للأدمن والموظف فقط بالدخول
        if (!in_array(auth()->user()->role, ['admin', 'employee'])) {
            return response()->json(['message' => 'ليس لديك صلاحية مراجعة الأوراق'], 403);
        }
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'required_if:status,rejected' // السبب إجباري في حالة الرفض
        ]);

        $profile = ProviderProfile::findOrFail($id);
        $profile->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes
        ]);

        // هنا يمكنك إرسال إشعار (Notification) للمحامي بالنتيجة

        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح']);
    }
}