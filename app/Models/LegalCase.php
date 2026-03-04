<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalCase extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'category',
        'initial_budget',
        'status',
        'accepted_provider_id'
    ];

    // العميل صاحب القضية
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // المحامي اللي تم قبوله للشغل
    public function acceptedProvider()
    {
        return $this->belongsTo(User::class, 'accepted_provider_id');
    }

    // العروض المقدمة على هذه القضية
    public function offers()
    {
        return $this->hasMany(Offer::class, 'case_id');
    }

    // تحديثات القضية (السجل اللي شفناه في الصورة)
    public function updates()
    {
        return $this->hasMany(CaseUpdate::class, 'case_id');
    }
}
