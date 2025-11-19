<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start',
        'finish',
        'rest_start',
        'rest_finish',
        'description',
    ];

    protected $guarded = [
        'id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
