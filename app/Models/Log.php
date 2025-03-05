<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    //
    public $table = 'logs';

    // Define the fillable attributes
    protected $fillable = [
        'module', 'client_id', 'action', 'data', 'created_by','updated_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
