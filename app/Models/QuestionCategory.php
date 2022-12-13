<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class QuestionCategory extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['name'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('QuestionCategory');
    }

    public function getQuestion()
    {
        return $this->belongsToMany(Question::class, 'question_category_relations', 'question_category_id', 'question_id')
            ->orderBy('question_category_relations.id');
    }

    public function getForms()
    {
        return $this->belongsToMany(FormTamplate::class, 'form_categories', 'question_category_id', 'form_id');
    }
}
