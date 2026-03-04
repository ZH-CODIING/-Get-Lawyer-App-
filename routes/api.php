<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Provider\ProfileController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Client\CaseController;
use App\Http\Controllers\Provider\OfferController;

/*
|--------------------------------------------------------------------------
| Public Routes (المسارات العامة)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Protected Routes (المسارات المحمية - تحتاج توكن)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- ملف المستخدم والإشعارات ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/notifications', [AuthController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [AuthController::class, 'markAsRead']);

    /*
    |-- مسارات الأدمن والموظفين (الإدارة) --
    */
    Route::prefix('admin')->group(function () {
        // لوحة التحكم (إحصائيات)
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // توثيق المحامين والمكاتب
        Route::get('/pending-lawyers', [VerificationController::class, 'getPendingRequests']);
        Route::post('/verify-lawyer/{id}', [VerificationController::class, 'verify']);

        // إدارة موظفي المنصة (للأدمن فقط)
        Route::post('/add-staff', [StaffController::class, 'addStaff']);
    });

    /*
    |-- مسارات العميل (Clients) --
    */
    Route::prefix('client')->group(function () {
        Route::get('/cases', [CaseController::class, 'index']);      // عرض كل القضايا
    Route::get('/cases/{id}', [CaseController::class, 'show']);
        Route::post('/cases', [CaseController::class, 'store']); // نشر قضية
        Route::post('/offers/{id}/accept', [CaseController::class, 'acceptOffer']); // قبول عرض
        Route::patch('/cases/{id}/status', [CaseController::class, 'updateStatus']); // إغلاق أو تعليق
    // جلب عروض قضية معينة
Route::get('/cases/{caseId}/offers', [CaseController::class, 'getOffers']);
        
    });

    /*
    |-- مسارات المحامي والمكتب (Providers) --
    */
    Route::prefix('provider')->group(function () {
        // رفع أوراق التوثيق
        Route::post('/upload-docs', [ProfileController::class, 'uploadDocuments']);

        // تصفح القضايا المتاحة
        Route::get('/cases', [OfferController::class, 'index']);

        // تقديم عرض سعر
        Route::post('/cases/{caseId}/offers', [OfferController::class, 'store']);
    });

});