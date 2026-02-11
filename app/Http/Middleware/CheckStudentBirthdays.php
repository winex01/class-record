<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\BirthdayNotification;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentBirthdays
{
    // Configuration: Number of days to look back for missed birthdays
    protected int $lookbackDays = 30;

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $this->checkAndNotify();
        }

        return $next($request);
    }

    protected function checkAndNotify(): void
    {
        $today = now();
        $userId = auth()->id();
        $lookbackDate = $today->copy()->subDays($this->lookbackDays);

        // Clean up: Delete birthday notifications older than lookback period
        BirthdayNotification::where('notification_date', '<', $lookbackDate->toDateString())
            ->delete();

        // Get already notified student IDs for this user (within lookback period)
        $alreadyNotifiedStudentIds = BirthdayNotification::where('user_id', $userId)
            ->pluck('student_id')
            ->toArray();

        // Get all students with birthdays between lookback date and today
        $allBirthdayStudents = Student::where(function ($query) use ($lookbackDate, $today) {
                // Get month-day range for birthdays
                $query->whereRaw('DATE_FORMAT(birth_date, "%m-%d") BETWEEN ? AND ?', [
                    $lookbackDate->format('m-d'),
                    $today->format('m-d')
                ]);
            })
            ->whereHas('schoolClasses', function ($query) {
                $query->where('active', true);
            })
            ->whereNotIn('id', $alreadyNotifiedStudentIds)
            ->get();

        if ($allBirthdayStudents->isEmpty()) {
            return;
        }

        // Separate students into TODAY and MISSED birthdays
        $todayStudents = collect();
        $missedStudents = collect();

        foreach ($allBirthdayStudents as $student) {
            $birthMonth = $student->birth_date->format('m');
            $birthDay = $student->birth_date->format('d');

            if ($birthMonth == $today->format('m') && $birthDay == $today->format('d')) {
                $todayStudents->push($student);
            } else {
                $missedStudents->push($student);
            }
        }

        // Send notification for TODAY'S birthdays
        if ($todayStudents->isNotEmpty()) {
            $this->sendTodayNotification($todayStudents, $userId, $today);
        }

        // Send notification for MISSED birthdays
        if ($missedStudents->isNotEmpty()) {
            $this->sendMissedNotification($missedStudents, $userId, $today);
        }
    }

    protected function sendTodayNotification($students, $userId, $today): void
    {
        $count = $students->count();
        $names = $students->pluck('full_name')->join(', ', ' and ');

        Notification::make()
            ->title($count === 1 ? 'Student Birthday Today! 🎂' : 'Student Birthdays Today! 🎂')
            ->body("{$names} " . ($count === 1 ? 'has' : 'have') . " a birthday today!")
            ->success()
            ->sendToDatabase(auth()->user());

        // Save to database
        foreach ($students as $student) {
            BirthdayNotification::create([
                'user_id' => $userId,
                'student_id' => $student->id,
                'notification_date' => $today->toDateString(),
            ]);
        }
    }

    protected function sendMissedNotification($students, $userId, $today): void
    {
        $count = $students->count();
        $names = $students->pluck('full_name')->join(', ', ' and ');

        Notification::make()
            ->title($count === 1 ? 'Recent Student Birthday! 🎂' : 'Recent Student Birthdays! 🎂')
            ->body("Friendly reminder: {$names} " . ($count === 1 ? 'had a' : 'had') . " birthday in the past {$this->lookbackDays} days!")
            ->info() // or ->warning() if you want it to stand out
            ->sendToDatabase(auth()->user());

        // Save to database
        foreach ($students as $student) {
            BirthdayNotification::create([
                'user_id' => $userId,
                'student_id' => $student->id,
                'notification_date' => $today->toDateString(),
            ]);
        }
    }
}
