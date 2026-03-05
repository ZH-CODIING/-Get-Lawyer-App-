<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role', // (admin, client, lawyer, office, employee)
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // --- العلاقات (Relationships) ---

    // علاقة المحامي/المكتب بملف التوثيق
    public function providerProfile()
    {
        return $this->hasOne(ProviderProfile::class);
    }

    // القضايا التي نشرها العميل
    public function cases()
    {
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    // العروض التي قدمها المحامي
    public function offers()
    {
        return $this->hasMany(Offer::class, 'provider_id');
    }
    
        // التقييمات التي حصل عليها المحامي
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'provider_id');
    }
    
    // دالة لحساب متوسط التقييم (اختيارية لكن مفيدة جداً)
    public function getAverageRatingAttribute()
    {
        return $this->reviewsReceived()->avg('rating') ?: 0;
    }
}
