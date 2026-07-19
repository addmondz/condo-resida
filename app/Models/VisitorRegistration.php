<?php

namespace App\Models;

use App\Enums\EntryType;
use App\Enums\VisitorPurpose;
use App\Enums\VisitorStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class VisitorRegistration extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference_number',
        'resident_id',
        'property_id',
        'block_id',
        'unit_id',
        'purpose',
        'visitor_name',
        'contact_number',
        'vehicle_number',
        'visit_date',
        'notes',
        'entry_type',
        'qr_token_hash',
        'encrypted_qr_token',
        'status',
        'checked_in_at',
        'checked_in_by',
        'checked_out_at',
        'checked_out_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'expired_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'status' => VisitorStatus::class,
            'purpose' => VisitorPurpose::class,
            'entry_type' => EntryType::class,
            'encrypted_qr_token' => 'encrypted',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (VisitorRegistration $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->reference_number)) {
                $model->reference_number = 'VR-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
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
     * The resident who registered this visitor.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * The property this visitor registration belongs to.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The block this visitor registration belongs to.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * The unit this visitor registration belongs to.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * The guard who checked the visitor in.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * The guard who checked the visitor out.
     */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * The user who cancelled this visitor registration.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Activity logs for this visitor registration.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(VisitorActivityLog::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to active visitor registrations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', VisitorStatus::Active);
    }

    /**
     * Scope to checked-in visitor registrations.
     */
    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->where('status', VisitorStatus::CheckedIn);
    }

    /**
     * Scope to today's visitor registrations.
     */
    public function scopeForToday(Builder $query): Builder
    {
        return $query->whereDate('visit_date', Carbon::today());
    }

    /**
     * Scope to a specific resident's visitor registrations.
     */
    public function scopeForResident(Builder $query, int $residentId): Builder
    {
        return $query->where('resident_id', $residentId);
    }

    // ---------------------------------------------------------------
    // Helper Methods
    // ---------------------------------------------------------------

    /**
     * Determine if the visitor registration is active.
     */
    public function isActive(): bool
    {
        return $this->status === VisitorStatus::Active;
    }

    /**
     * Determine if the visitor is currently checked in.
     */
    public function isCheckedIn(): bool
    {
        return $this->status === VisitorStatus::CheckedIn;
    }

    /**
     * Determine if the visitor can be checked in.
     */
    public function canCheckIn(): bool
    {
        return $this->status === VisitorStatus::Active;
    }

    /**
     * Determine if the visitor can be checked out.
     */
    public function canCheckOut(): bool
    {
        return $this->status === VisitorStatus::CheckedIn;
    }

    /**
     * Determine if the visitor registration can be cancelled.
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [VisitorStatus::Active, VisitorStatus::CheckedIn]);
    }
}
