<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'usage_number',
        'project_id',
        'rab_item_id',
        'usage_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'project_id' => 'integer',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rabItem(): BelongsTo
    {
        return $this->belongsTo(RabItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialUsageItem::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->usage_number)) {
                $model->usage_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'MU-' . date('Ym');
        $last = static::where('usage_number', 'like', $prefix . '%')
            ->orderByDesc('usage_number')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->usage_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
