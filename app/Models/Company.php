<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'companies';
    protected $guarded = [];

    public function Type()
    {
        return $this->belongsTo(Type::class,'type_id');
    }
    public function Attachement()
    {
        return $this->hasMany(CompanyAttachement::class,'company_id');
    }
}
