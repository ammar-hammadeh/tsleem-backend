<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    protected $table = 'companies';
    protected $guarded = [];
    protected $appends = [
        'files_counter'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type_id',
                'owner_id',
                'parent_id',
                'name',
                'commercial',
                'license',
                'owner_name',
                'owner_hardcopyid',
                'commercial_expiration',
                'deleted_at',
            ])->useLogName('companies');
    }


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
