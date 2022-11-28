<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSigner extends Model
{
    use HasFactory;
    protected $fillable = ['form_id', 'type_id'];

    public function Types()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

}
