<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LegalCase;
use App\Models\ProviderProfile;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // التأكد من الصلاحية (أدمن أو موظف فقط)
        if (!in_array(auth()->user()->role, ['admin', 'employee'])) {
            return response()->json(['message' => 'غير مصرح لك بالدخول'], 403);
        }

        // إحصائيات سريعة (Stats Widgets)
        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'total_lawyers' => User::where('role', 'lawyer')->count(),
            'pending_verifications' => ProviderProfile::where('status', 'pending')->count(),
            'active_cases' => LegalCase::where('status', 'processing')->count(),
            'completed_cases' => LegalCase::where('status', 'completed')->count(),
            'total_revenue' => Transaction::sum('amount'), // إجمالي الأموال المتداولة
        ];

        // آخر القضايا التي تم نشرها
        $recent_cases = LegalCase::with('client')
            ->latest()
            ->take(5)
            ->get();

        // آخر طلبات التوثيق للمحامين والمكاتب
        $latest_verification_requests = ProviderProfile::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_cases' => $recent_cases,
            'latest_verification_requests' => $latest_verification_requests,
        ]);
    }
}