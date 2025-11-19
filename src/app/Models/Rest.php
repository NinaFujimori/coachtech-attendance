<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Attendance; 
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'approval',
        'start',
        'finish',
    ];

    protected $guarded = [
        'id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
