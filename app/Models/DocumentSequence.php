<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSequence extends Model
{
    protected $fillable = ['type', 'prefix', 'next_number'];

    /**
     * Get the next number for a document type in a race-safe way.
     */
    public static function next(string $type, string $prefix = ''): string
    {
        return DB::transaction(function () use ($type, $prefix) {
            $sequence = self::where('type', $type)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = self::create([
                    'type' => $type,
                    'prefix' => $prefix ?: $type,
                    'next_number' => 1
                ]);
            }

            $currentNumber = $sequence->next_number;
            $sequence->increment('next_number');

            return sprintf(
                '%s/%s/%05d',
                $sequence->prefix,
                now()->format('Ymd'),
                $currentNumber
            );
        });
    }
}
