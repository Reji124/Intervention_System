<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Support\Facades\Session;

/**
 * AnalyticsSessionService
 * 
 * Manages selected School Year and Semester for analytics dashboard.
 * Stores selection in session to persist across page navigation.
 * Scope: Admin analytics section only.
 */
class AnalyticsSessionService
{
    private const SESSION_KEY_SEMESTER = 'analytics.selected_semester_id';

    /**
     * Get the currently selected semester.
     * Falls back to the active semester if none selected.
     * Returns Semester with SchoolYear eager-loaded.
     */
    public function getSelectedSemester(): ?Semester
    {
        $semesterId = Session::get(self::SESSION_KEY_SEMESTER);

        if ($semesterId) {
            $semester = Semester::with('schoolYear')->find($semesterId);
            if ($semester) {
                return $semester;
            }
        }

        // Fallback: Use active semester
        return Semester::with('schoolYear')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set the selected semester in session.
     */
    public function setSelectedSemester(int $semesterId): bool
    {
        $semester = Semester::find($semesterId);

        if (!$semester) {
            return false;
        }

        Session::put(self::SESSION_KEY_SEMESTER, $semesterId);
        return true;
    }

    /**
     * Clear the selected semester from session.
     */
    public function clearSelectedSemester(): void
    {
        Session::forget(self::SESSION_KEY_SEMESTER);
    }

    /**
     * Get the session key (for testing/debugging).
     */
    public static function getSessionKey(): string
    {
        return self::SESSION_KEY_SEMESTER;
    }
}
