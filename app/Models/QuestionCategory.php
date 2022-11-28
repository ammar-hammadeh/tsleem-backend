<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function getQuestion()
    {
        return $this->belongsToMany(Question::class, 'question_category_relations', 'question_category_id', 'question_id');
    }

    public function getForms()
    {
        return $this->belongsToMany(FormTamplate::class, 'form_categories', 'question_category_id', 'form_id');
    }
}
