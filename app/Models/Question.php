<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $guarded = [];

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
