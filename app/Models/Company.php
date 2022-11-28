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
    protected $appends = [
        'files_counter'
    ];

    public function getFilesCounterAttribute($val)
    {
        # code...
        $companyattach = CompanyAttachement::where('company_id', $this->id)->count();
        $userattach = UserAttachement::where('user_id', $this->owner_id)->count();
        $type_code = Type::where('id', $this->type_id)->value('code');

        switch ($type_code) {
            case 'service_provider':
                return '7/' . $companyattach + $userattach;
                break;
            case 'contractor':
                return '5/' . $companyattach + $userattach;
            case 'design_office':
                return '4/' . $companyattach + $userattach;

            default:
                # code...
                break;
        }
    }

    public function Type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
    public function Attachement()
    {
        return $this->hasMany(CompanyAttachement::class, 'company_id');
    }
    public function EngineerOffice()
    {
        return $this->belongsTo(EngineerOffceCategories::class, 'engineer_office_id');
    }
}
