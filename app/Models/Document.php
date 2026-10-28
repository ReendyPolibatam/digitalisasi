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
        'ocr_result',
        'ocr_text_edited',
        'status',
        'user_id',
        'vessel_name',
        'category',
        'confidence_score',
        'confidence',
        'voyage',
        'loading_date',
        'discharge_date',
        'bl_liters_obs',
        'liters_15c',
        'submitted_at',
        'approved_at',
        'approved_by',
        'admin_note',
    ];

    protected $casts = [
        'loading_date'   => 'date',
        'discharge_date' => 'date',
        'submitted_at'   => 'datetime',
        'approved_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function normalizeVesselName(?string $name): string
    {
        if (!$name) {
            return '';
        }

        $name = mb_strtoupper($name);
        $name = preg_replace('/[^A-Z0-9]/', '', $name);

        return $name;
    }
}