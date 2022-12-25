<?php

namespace Modules\Core\Entities;

use App\Models\City;
use App\Models\Type;
use App\Models\Company;
use App\Models\Category;
use App\Models\UserAttachement;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasRoles, HasFactory, Notifiable, LogsActivity;

    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    //     'avatar',
    //     'status',
    //     'is_customer'
    // ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type_id',
                'city_id',
                'parent_id',
                'company_id',
                'name',
                'phone',
                'signature',
                'email',
                'status',
                'deleted_at',
            ])->useLogName('users');
    }

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];



    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatarAttribute($val)
    {
        if ($val != null) {
            if (env('DISK') == 's3')
                return env('AWS_URL') . $val;
            else
                return url('storage/' . $val);
        } else {
            return $val;
        }
    }

    public function getFullSignatureAttribute($val)
    {
        if ($this->signature != null) {
            if (env('DISK') == 's3')
                return url(env('AWS_URL') . $this->signature);
            else
                return url('storage/' . $this->signature);
        } else {
            return $this->signature;
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function Customers()
    {
        return $this->hasOne(Customer::class, 'id');
    }

    protected $appends = [
        'status_text',
        'full_signature'
    ];

    public function getStatusTextAttribute($val)
    {
        # code...
        return __('general.' . $this->status);
    }
    public function Attachement()
    {
        return $this->hasMany(UserAttachement::class, 'user_id');
    }
    public function Type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
    public function Company()
    {
        return $this->hasOne(Company::class, 'owner_id');
    }
    public function Companies()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function City()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    public function Category()
    {
        return $this->belongsToMany(Category::class, 'users_categories', 'category_id', 'user_id');
    }
}
