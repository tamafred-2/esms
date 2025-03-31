<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'contact_number',
        'street_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
