<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'signer',
        'code'
    ];

    protected $appends = [
        'signer_text',
    ];
    public function getSignerTextAttribute($val)
    {
        return ($this->signer == 1) ? __('general.active') : __('general.disable');
    }

    public function getSignerAttribute($val)
    {
        return ($val == 1) ? true : false;
    }

    public function setSignerAttribute($val)
    {
        $this->attributes['signer'] = ($val == 'true') ?  1 : 0;
    }
}
