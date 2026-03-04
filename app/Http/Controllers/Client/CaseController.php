<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LegalCase;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\CaseNotification;

class CaseController extends Controller
{
    // 5. جلب كل قضايا العميل الحالي
public function index(Request $request)
{
    $query = LegalCase::where('client_id', auth()->id())
        ->withCount('offers'); // عشان العميل يعرف كل قضية جالها كام عرض

    // فلترة اختيارية حسب الحالة (pending, processing, completed)
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    $cases = $query->latest()->get();

    return response()->json($cases);
}

// 6. عرض تفاصيل قضية واحدة مع بيانات المحامي المقبول
public function show($id)
{
    $case = LegalCase::with(['acceptedProvider.providerProfile'])
        ->where('client_id', auth()->id())
        ->findOrFail($id);

    return response()->json($case);
}

    // 4. العميل يشوف العروض المقدمة على قضية معينة
public function getOffers($caseId)
{
    // التأكد أن القضية تخص العميل الحالي
    $case = LegalCase::where('id', $caseId)
                     ->where('client_id', auth()->id())
                     ->firstOrFail();

    // جلب العروض مع بيانات المحامي (صاحب العرض)
    $offers = Offer::with(['provider:id,name,phone']) // بنجيب بيانات محددة من المحامي للخصوصية
                   ->where('case_id', $caseId)
                   ->latest()
                   ->get();

    return response()->json([
        'case_title' => $case->title,
        'offers_count' => $offers->count(),
        'offers' => $offers
    ]);
}
    // 1. العميل ينشر قضية جديدة
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string', // جنائي، مالي، إلخ
            'initial_budget' => 'required|numeric|min:0',
        ]);

        $case = LegalCase::create([
            'client_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'initial_budget' => $request->initial_budget,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'تم نشر القضية بنجاح', 'case' => $case], 201);
    }

    // 2. العميل يقبل عرض محامي معين
public function acceptOffer($offerId)
{
    // جلب العرض مع بيانات المحامي (Provider)
    $offer = Offer::with(['legalCase', 'provider.providerProfile'])->findOrFail($offerId);
    $case = $offer->legalCase;

    if ($case->client_id !== auth()->id() || $case->status !== 'pending') {
        return response()->json(['message' => 'لا يمكنك قبول هذا العرض'], 403);
    }

    DB::transaction(function () use ($case, $offer) {
        // تحديث حالة القضية وتعيين المحامي المقبول
        $case->update([
            'status' => 'processing',
            'accepted_provider_id' => $offer->provider_id
        ]);

        // إرسال الإشعار للمحامي
        $offer->provider->notify(new CaseNotification([
            'title' => 'تم قبول عرضك',
            'message' => 'مبروك! اختارك العميل للبدء في قضية: ' . $case->title,
            'case_id' => $case->id,
            'type' => 'case_accepted'
        ]));
    });

    return response()->json([
        'message' => 'تم قبول العرض بنجاح',
        'accepted_lawyer' => [
            'name'  => $offer->provider->name,
            'phone' => $offer->provider->phone,
            'email' => $offer->provider->email,
            'bio'   => $offer->provider->providerProfile->bio ?? 'لا يوجد وصف',
        ]
    ]);
}


    // 3. العميل يغير حالة القضية (إغلاق أو إعادة فتح)
    public function updateStatus(Request $request, $caseId)
    {
        $request->validate([
            'status' => 'required|in:completed,unresolved'
        ]);

        $case = LegalCase::findOrFail($caseId);

        if ($case->client_id !== auth()->id()) {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        if ($request->status == 'completed') {
            $case->update(['status' => 'completed']);
            // منطق تحويل الأموال للمحامي يتم هنا
            return response()->json(['message' => 'تم إغلاق القضية بنجاح']);
        }

        if ($request->status == 'unresolved') {
            // إعادة القضية للبحث عن محامي آخر (Pending)
            $case->update([
                'status' => 'pending',
                'accepted_provider_id' => null
            ]);
            return response()->json(['message' => 'تمت إعادة القضية للحالة المعلقة']);
        }
    }
}