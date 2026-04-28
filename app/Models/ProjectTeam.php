<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTeam extends Model
{
    use HasFactory;

    protected $table = 'project_team';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'assigned_from',
        'assigned_until',
        'is_active',
    ];

    protected $casts = [
        'assigned_from' => 'date',
        'assigned_until' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? $this->role;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public static function getRoles(): array
    {
        return [
            'project-manager' => 'Project Manager',
            'site-manager' => 'Site Manager',
            'engineer' => 'Engineer',
            'supervisor' => 'Supervisor',
            'logistics' => 'Logistik',
            'purchasing' => 'Purchasing',
            'admin' => 'Admin',
            'administrator' => 'Administrator',
            'architect' => 'Arsitek',
            'designer' => 'Designer',
            'project-admin' => 'Project Admin',
            'quantity-surveyor' => 'Quantity Surveyor',
            'drafter' => 'Drafter',
            'superintendent' => 'Superintendent',
            'tukang' => 'Tukang',
            'operator' => 'Operator',
            'hse' => 'HSE',
            'surveyor' => 'Surveyor',
        ];
    }
}
