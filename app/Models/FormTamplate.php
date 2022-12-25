<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormTamplate extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'form_tamplates';
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'body',
            ])->useLogName('forms');
    }

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
