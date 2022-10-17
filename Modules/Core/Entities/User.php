<?php

namespace Modules\Core\Entities;

use App\Models\Company;
use App\Models\Type;
use App\Models\UserAttachement;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasRoles, HasFactory, Notifiable, SoftDeletes;

    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    //     'avatar',
    //     'status',
    //     'is_customer'
    // ];
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
        'status_text'
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
}
