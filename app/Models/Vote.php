<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'survey_id',
        'survey_token_id',
        'question_id',
        'question_option_id',
        'ip_address',
        'fingerprint',
        'user_agent',
        'platform',
        'screen_resolution',
        'hardware_concurrency',
        'is_manual',
    ];

    // Scope para votos válidos (con token o manuales)
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('survey_token_id')
              ->orWhere('is_manual', true);
        });
    }

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function token()
    {
        return $this->belongsTo(SurveyToken::class, 'survey_token_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
