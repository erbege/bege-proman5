<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $fillable = [
        'project_id',
        'requested_by',
        'code',
        'request_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateCode();
            }
            if (empty($model->request_date)) {
                $model->request_date = now();
            }
        });
    }

    public static function generateCode(): string
    {
        $prefix = 'MR-' . date('Ym');
        $lastRequest = static::where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->first();

        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
