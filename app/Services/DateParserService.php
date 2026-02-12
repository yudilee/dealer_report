<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DateParserService
{
    /**
     * Parse a value that could be a DD/MM/YYYY string, an Excel serial date number,
     * a DateTime object, or empty.
     */
    public static function parse($value): ?Carbon
    {
        if (empty($value) || $value === '' || $value === '  /  /' || $value === '0') {
            return null;
        }

        // If it's already a DateTime/Carbon
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        // If it's a numeric value (Excel serial date)
        // NOTE: Source XLS files were created with DD/MM locale but Excel stored
        // dates as MM/DD, so month and day are swapped in the serial numbers.
        // We swap them back after conversion to get the correct date.
        if (is_numeric($value) && (float)$value > 25000 && (float)$value < 60000) {
            try {
                $dt = Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value));
                $m = $dt->month;
                $d = $dt->day;
                // Swap month <-> day if the day value can be a valid month (1-12)
                if ($d <= 12) {
                    return Carbon::create($dt->year, $d, $m);
                }
                return $dt;
            } catch (\Exception $e) {
                return null;
            }
        }

        // If it's a string date
        if (is_string($value)) {
            $value = trim($value);
            if (empty($value) || $value === '/  /') {
                return null;
            }

            // Try DD/MM/YY (2-digit year, common in XLS)
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2})$#', $value, $m)) {
                $yr = (int)$m[3];
                $fullYear = $yr < 100 ? 2000 + $yr : $yr;
                try {
                    return Carbon::create($fullYear, (int)$m[2], (int)$m[1]);
                } catch (\Exception $e) {}
            }

            // Try DD/MM/YYYY (4-digit year)
            try {
                return Carbon::createFromFormat('d/m/Y', $value);
            } catch (\Exception $e) {}

            // Try other common formats
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {}
        }

        return null;
    }

    /**
     * Parse a numeric value, handling empty and non-numeric gracefully.
     */
    public static function parseDecimal($value, float $default = 0): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return $default;
    }

    /**
     * Parse an integer value.
     */
    public static function parseInt($value, ?int $default = null): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }
        return $default;
    }
}
