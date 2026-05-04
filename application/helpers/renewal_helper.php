<?php
if (!function_exists('calculate_new_expiry')) {
    /**
     * Calculate a new expiry date based on a base date, duration and time unit.
     *
     * @param string $baseDate   Date in Y-m-d format (e.g., expiry or purchase date)
     * @param int    $duration   Number of units
     * @param string $unit       One of Days, Weeks, Months, Years, Decade, Century
     * @return string            New date in Y-m-d format
     */
    function calculate_new_expiry(string $baseDate, int $duration, string $unit): string {
        $date = new DateTime($baseDate);
        switch ($unit) {
            case 'Days':
                $date->modify("+$duration day");
                break;
            case 'Weeks':
                $date->modify("+" . ($duration * 7) . " day");
                break;
            case 'Months':
                $date->modify("+$duration month");
                break;
            case 'Years':
                $date->modify("+$duration year");
                break;
            case 'Decade':
                $date->modify("+" . ($duration * 10) . " year");
                break;
            case 'Century':
                $date->modify("+" . ($duration * 100) . " year");
                break;
            default:
                // fallback to days if unknown unit
                $date->modify("+$duration day");
        }
        return $date->format('Y-m-d');
    }
}
?>