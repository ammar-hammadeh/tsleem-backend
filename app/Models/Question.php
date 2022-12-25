<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Question extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'input_id',
                'title',
            ])->useLogName('inquiries');
    }

    public function inputs()
    {
        return $this->hasOne(Input::class, 'id', 'input_id');
    }

    public function Answer()
    {
        return $this->hasOne(TasleemFormAnswers::class, 'question_id');
    }

    public function getCategory()
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_relations', 'question_id', 'question_category_id');
    }
}
