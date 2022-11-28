<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class formsQuestions extends Model
{
    use HasFactory;
    protected $table = 'form_questions';
    protected $guarded = [];


    public function Questions()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
