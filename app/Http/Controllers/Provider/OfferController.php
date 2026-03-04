<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\LegalCase;
use App\Models\Offer;
use Illuminate\Http\Request;
use App\Notifications\CaseNotification;

class OfferController extends Controller
{
    // 1. عرض القضايا المتاحة للمحامين (Pending فقط)
    public function index(Request $request)
    {
        // يمكن الفلترة حسب المجال (جنائي، مالي...) إذا أرسلها المحامي في الطلب
        $query = LegalCase::where('status', 'pending');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $cases = $query->latest()->get();
        return response()->json($cases);
    }

    // 2. تقديم عرض سعر على قضية
    public function store(Request $request, $caseId)
    {
        $user = auth()->user();

        // الشرط الجوهري: التأكد من التوثيق
        if (!$user->providerProfile || $user->providerProfile->status !== 'approved') {
            return response()->json([
                'message' => 'عذراً، يجب توثيق حسابك من قبل الإدارة أولاً لتتمكن من تقديم عروض.'
            ], 403);
        }

        $request->validate([
            'offered_price' => 'required|numeric|min:1',
            'proposal_text' => 'required|string|min:20',
        ]);

        $case = LegalCase::findOrFail($caseId);

        // التأكد أن القضية لا تزال تنتظر عروضاً
        if ($case->status !== 'pending') {
            return response()->json(['message' => 'هذه القضية لم تعد تستقبل عروضاً'], 422);
        }

        // منع تقديم أكثر من عرض لنفس المحامي على نفس القضية
        $exists = Offer::where('case_id', $caseId)->where('provider_id', $user->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'لقد قدمت عرضاً بالفعل على هذه القضية'], 422);
        }

        $offer = Offer::create([
            'case_id' => $caseId,
            'provider_id' => $user->id,
            'offered_price' => $request->offered_price,
            'proposal_text' => $request->proposal_text,
        ]);
        // بعد حفظ العرض بنجاح
        $client = $case->client;
        $client->notify(new CaseNotification([
            'title' => 'عرض جديد',
            'message' => 'قام محامي بتقديم عرض سعر جديد على قضيتك: ' . $case->title,
            'case_id' => $case->id,
            'type' => 'offer_received'
        ]));
        return response()->json(['message' => 'تم تقديم عرضك بنجاح', 'offer' => $offer], 201);
    }
}