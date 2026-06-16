<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_MarketingBrandMention extends Model
{
    use HasFactory;

    protected $table = 'tbl_marketing_brand_mentions';

    protected $fillable = [
        'source',
        'username',
        'review_text',
        'sentiment',
        'category',
        'ai_root_cause',
        'ai_marketing_step',
        'ai_business_solution',
        'status',
    ];
}
