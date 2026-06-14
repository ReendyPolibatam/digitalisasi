<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'ocr_text',
        'status',
        'user_id',
        'ship_name',
        'category',
        'confidence_score',
        'ocr_result',
        'confidence',
        'vessel_name',
        'loading_date',
        'discharge_date',
        'bl_liters_obs',
        'liters_15c',
    ];

    protected $casts = [
    'loading_date' => 'date',
    'discharge_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}