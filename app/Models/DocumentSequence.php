<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentSequence extends Model
{
    protected $fillable = ['type', 'prefix', 'next_number', 'project_id'];

    public static function getNext(string $type, string $prefix = '', ?int $projectId = null): int
    {
        $hasProjectId = Schema::hasColumn('document_sequences', 'project_id');

        return DB::transaction(function () use ($type, $prefix, $projectId, $hasProjectId) {
            $query = self::where('type', $type);
            if ($hasProjectId && $projectId) {
                $query->where('project_id', $projectId);
            }
            $sequence = $query->lockForUpdate()->first();

            if (! $sequence) {
                $data = [
                    'type' => $type,
                    'prefix' => $prefix ?: $type,
                    'next_number' => 1,
                ];
                if ($hasProjectId) {
                    $data['project_id'] = $projectId;
                }
                $sequence = self::create($data);
            }

            $current = $sequence->next_number;
            $sequence->increment('next_number');

            return $current;
        });
    }

    public static function next(string $type, string $prefix = '', ?int $projectId = null): string
    {
        $num = self::getNext($type, $prefix, $projectId);

        return sprintf('%s/%s/%05d', $prefix ?: $type, now()->format('Ymd'), $num);
    }
}