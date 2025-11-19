<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'approval',
        'start',
        'finish',
        'full',
        'date',
        'description'
    ];

    protected $guarded = [
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(\App\Models\Rest::class);
    }

    public function approval()
    {
        return $this->hasOne(Approval::class);
    }

    public function calculateFullTime(): string
    {
        if (!$this->start || !$this->finish) {
            return '00:00:00';
        }

        $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->start);
        $finish = \Carbon\Carbon::createFromFormat('H:i:s', $this->finish);
        $workSeconds = $finish->diffInSeconds($start);

        $restSeconds = $this->rests->sum(function ($rest) {
            if (!$rest->finish) return 0;
            $restStart = \Carbon\Carbon::createFromFormat('H:i:s', $rest->start);
            $restFinish = \Carbon\Carbon::createFromFormat('H:i:s', $rest->finish);
            return $restFinish->diffInSeconds($restStart);
        });

        $actualSeconds = max($workSeconds - $restSeconds, 0);
        return gmdate('H:i:s', $actualSeconds);
    }
    
}
