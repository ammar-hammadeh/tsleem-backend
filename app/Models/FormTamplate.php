<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTamplate extends Model
{
    use HasFactory;
    protected $table = 'form_tamplates';

    public function Questions()
    {
        return $this->belongsToMany(Question::class,'form_questions','question_id','form_id');
    }

}
