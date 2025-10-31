<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'survey_id',
        'question_id',
        'question_option_id',
        'ip_address',
        'fingerprint',
        'user_agent',
        'platform',
        'screen_resolution',
        'hardware_concurrency',
    ];

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
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
