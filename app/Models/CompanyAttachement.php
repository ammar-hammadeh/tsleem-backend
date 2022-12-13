<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAttachement extends Model
{
    use HasFactory;
    protected $table = 'company_attachments';
    protected $guarded = [];
    protected $appends = [
        'full_path'
    ];

    public function getFullPathAttribute($val)
    {
        if ($this->path != null) {
            if (env('DISK') == 's3')
                return url(env('AWS_URL')  . $this->path);
            else
                return url('storage/' . $this->path);
        } else {
            return $this->path;
        }
    }
}
