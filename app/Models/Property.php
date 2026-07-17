<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'contact_email',
        'contact_phone',
        'timezone',
        'logo',
        'status',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Property $model): void {
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
     * Blocks belonging to this property.
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    /**
     * Units belonging to this property through blocks.
     */
    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(Unit::class, Block::class);
    }

    /**
     * Users assigned to this property through unit assignments.
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            UserUnitAssignment::class,
            'property_id',  // FK on user_unit_assignments
            'id',           // FK on users
            'id',           // LK on properties
            'user_id',      // LK on user_unit_assignments
        );
    }

    /**
     * Facilities belonging to this property.
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    /**
     * Visitor registrations for this property.
     */
    public function visitorRegistrations(): HasMany
    {
        return $this->hasMany(VisitorRegistration::class);
    }
}
