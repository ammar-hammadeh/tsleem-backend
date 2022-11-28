<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormCategory extends Model
{
    use HasFactory;

    public function getCategory()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id', 'id');
    }
}
