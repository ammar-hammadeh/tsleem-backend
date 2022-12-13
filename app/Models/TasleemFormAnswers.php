<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasleemFormAnswers extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "tasleem_form_answers";

    public function Attachements()
    {
        return $this->hasMany(AnswersAttachement::class,'answer_id','id');
    }
}
