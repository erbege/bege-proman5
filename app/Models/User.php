<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, HasProfilePhoto, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_team')
            ->withPivot('role', 'assigned_from', 'assigned_until', 'is_active')
            ->withTimestamps();
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'requested_by');
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class, 'reported_by');
    }

    /**
     * Get FCM tokens for this user (multi-device support).
     */
    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    // Scopes
    public function scopeWithRole($query, string $role)
    {
        return $query->role($role);
    }

    // Helper Methods
    public function isProjectMember(Project $project): bool
    {
        return $this->projects()->where('projects.id', $project->id)->exists();
    }

    public function getProjectRole(Project $project): ?string
    {
        $pivot = $this->projects()
            ->where('projects.id', $project->id)
            ->first()
                ?->pivot;

        return $pivot?->role;
    }
}
