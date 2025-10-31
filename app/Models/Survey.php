<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'description',
        'banner',
        'og_image',
        'slug',
        'is_active',
        'is_finished',
        'published_at',
        'finished_at',
        'views_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_finished' => 'boolean',
        'published_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Generar slug automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($survey) {
            if (empty($survey->slug)) {
                $survey->slug = Str::slug($survey->title) . '-' . Str::random(6);
            }
        });
    }

    // Relaciones
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // Métodos útiles
    public function getTotalVotesAttribute()
    {
        return $this->votes()->distinct('ip_address')->count();
    }

    // Incrementar contador de visitas
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
