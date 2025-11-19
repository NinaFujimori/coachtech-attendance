<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('layouts.app', function ($view) {
            $attendance = null;

            if (Auth::check()) {
                $userId = Auth::id();
                $today = Carbon::today();

                $attendance = Attendance::where('user_id', $userId)
                    ->whereDate('date', $today)
                    ->first();
            }

            $view->with('todayAttendance', $attendance);
        });
    }
}
