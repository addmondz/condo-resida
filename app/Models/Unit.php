<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Unit extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'block_id',
        'name',
        'floor',
        'status',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Unit $model): void {
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
     * The block this unit belongs to.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * The property this unit belongs to (through block).
     */
    public function property(): HasOneThrough
    {
        return $this->hasOneThrough(
            Property::class,
            Block::class,
            'id',          // FK on blocks
            'id',          // FK on properties
            'block_id',    // LK on units
            'property_id', // LK on blocks
        );
    }

    /**
     * Residents assigned to this unit.
     */
    public function residents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit_assignments')
            ->withPivot('property_id', 'is_primary', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Unit assignment records.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserUnitAssignment::class);
    }
}
