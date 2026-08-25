<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'description', 'ip_address', 'user_agent'];

    public function user() { return $this->belongsTo(User::class); }
}
