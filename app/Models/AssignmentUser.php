<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class AssignmentUser extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'assignment_id',
        'user_id',
        'department_id',
        'access_level',
      ];

      public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Define the relationship to the user (if not already defined)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
