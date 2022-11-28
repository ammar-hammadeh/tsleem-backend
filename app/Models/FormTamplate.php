<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTamplate extends Model
{
    use HasFactory;
    protected $table = 'form_tamplates';
    protected $guarded = [];

    public function Questions()
    {
        return $this->belongsToMany(Question::class, 'form_questions', 'form_id', 'question_id');
    }

    public function Signers()
    {
        return $this->hasMany(FormSigner::class, 'form_id');
    }

    public function Categories()
    {
        return $this->belongsToMany(QuestionCategory::class, 'form_categories', 'form_id', 'question_category_id');
    }
}
