<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'name',
        'description',
        'rules',
        'capacity',
        'image',
        'opening_time',
        'closing_time',
        'slot_duration',
        'max_bookings_per_resident',
        'advance_booking_days',
        'cancellation_hours',
        'is_active',
        'is_under_maintenance',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_under_maintenance' => 'boolean',
            'slot_duration' => 'integer',
            'capacity' => 'integer',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Facility $model): void {
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
     * The property this facility belongs to.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Bookings for this facility.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class);
    }

    /**
     * Blocked slots for this facility.
     */
    public function blockedSlots(): HasMany
    {
        return $this->hasMany(FacilityBlockedSlot::class);
    }
}
