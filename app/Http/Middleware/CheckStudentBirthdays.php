<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Student;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentBirthdays
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $cacheKey = 'birthday_checked_' . now()->format('Y-m-d');

            if (!Cache::has($cacheKey)) {
                $this->checkAndNotify();
                Cache::put($cacheKey, true, now()->endOfDay());
            }
        }

        return $next($request);
    }

    protected function checkAndNotify(): void
    {
        $today = now();

        // Get students from active classes only (deduplicated automatically)
        $students = Student::whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->whereHas('schoolClasses', function ($query) {
                $query->where('active', true);
            })
            ->get();

        if ($students->isNotEmpty()) {
            $count = $students->count();
            $names = $students->pluck('full_name')->join(', ', ' and ');

            Notification::make()
                ->title($count === 1 ? 'Student Birthday Today! 🎂' : 'Student Birthdays Today! 🎂')
                ->body("{$names} " . ($count === 1 ? 'has' : 'have') . " a birthday today!")
                ->success()
                ->sendToDatabase(auth()->user());
        }
    }
}
