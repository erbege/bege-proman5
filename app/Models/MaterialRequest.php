<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaterialRequest extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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

    public function approvalLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
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
        return DocumentSequence::next('MR');
    }
}
