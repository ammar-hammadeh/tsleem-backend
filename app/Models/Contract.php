<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function AssignCamps()
    {
        return $this->belongsTo(AssignCamp::class,'assign_camps_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function Ministry()
    {
        return $this->belongsTo(User::class,'ministry');
    }
    public function Kidana()
    {
        return $this->belongsTo(User::class,'kidana');
    }
    public function Company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
    public function CompanyLicense()
    {
        return $this->belongsTo(Company::class,'company_license','license');
    }
    

}
