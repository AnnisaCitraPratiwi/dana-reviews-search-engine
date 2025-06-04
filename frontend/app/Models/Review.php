<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'review_id',
        'user_name',
        'score',
        'review_date',
        'content',
        'reply_content'
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'score' => 'integer'
    ];

    // Scope untuk filter berdasarkan rating
    public function scopeByRating($query, $rating)
    {
        return $query->where('score', $rating);
    }

    // Scope untuk filter berdasarkan app_id
    public function scopeByApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    // Accessor untuk format tanggal yang lebih readable
    public function getFormattedDateAttribute()
    {
        return $this->review_date ? $this->review_date->format('d M Y, H:i') : '-';
    }
}