<?php

namespace App\Models;

use App\Enums\ResidentType;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'resident_type',
        'avatar',
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
            'status' => UserStatus::class,
            'resident_type' => ResidentType::class,
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * Unit assignments for this user.
     */
    public function unitAssignments(): HasMany
    {
        return $this->hasMany(UserUnitAssignment::class);
    }

    /**
     * Units this user is assigned to.
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'user_unit_assignments')
            ->withPivot('property_id', 'is_primary', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Properties accessible through the user's unit assignments.
     */
    public function properties(): HasManyThrough
    {
        return $this->hasManyThrough(
            Property::class,
            UserUnitAssignment::class,
            'user_id',      // FK on user_unit_assignments
            'id',           // FK on properties
            'id',           // LK on users
            'property_id',  // LK on user_unit_assignments
        );
    }

    /**
     * The user's primary unit assignment.
     */
    public function primaryUnit(): HasOne
    {
        return $this->hasOne(UserUnitAssignment::class)->where('is_primary', true);
    }

    /**
     * Visitor registrations created by this resident.
     */
    public function visitorRegistrations(): HasMany
    {
        return $this->hasMany(VisitorRegistration::class, 'resident_id');
    }

    /**
     * Approval records for this user.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(UserApproval::class);
    }

    /**
     * Facility bookings made by this resident.
     */
    public function facilityBookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class, 'resident_id');
    }

    /**
     * Notification recipients for this user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to only approved users.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Approved);
    }

    /**
     * Scope to only pending users.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Pending);
    }

    /**
     * Scope to users with a specific role.
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->role($role);
    }

    /**
     * Scope to users belonging to a specific property.
     */
    public function scopeByProperty(Builder $query, int $propertyId): Builder
    {
        return $query->whereHas('unitAssignments', function (Builder $q) use ($propertyId): void {
            $q->where('property_id', $propertyId);
        });
    }

    // ---------------------------------------------------------------
    // Helper Methods
    // ---------------------------------------------------------------

    /**
     * Determine if the user is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === UserStatus::Approved;
    }

    /**
     * Determine if the user is pending.
     */
    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    /**
     * Determine if the user is an admin (super_admin or property_admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'property_admin']);
    }

    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Determine if the user is a guard.
     */
    public function isGuard(): bool
    {
        return $this->hasRole('guard');
    }

    /**
     * Determine if the user is a resident.
     */
    public function isResident(): bool
    {
        return $this->hasRole('resident');
    }
}
