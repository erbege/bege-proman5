<?php

namespace App\Traits;

trait GeneratesUniqueCode
{
    /**
     * Generate a unique code for a model
     *
     * @param string $model The model class
     * @param string $prefix The prefix for the code (e.g., 'MAT', 'SUP', 'KLN')
     * @param string $column The column name for the code (default: 'code')
     * @return string
     */
    protected function generateUniqueCode(string $model, string $prefix, string $column = 'code'): string
    {
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . '-' . $number;
            $exists = $model::where($column, $code)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            // Fallback with timestamp if random fails
            $code = $prefix . '-' . time();
        }

        return $code;
    }
}
