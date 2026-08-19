<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReportCardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'name', 'category', 'description',
        'canvas_width', 'canvas_height', 'background',
        'elements', 'published_elements',
        'is_default', 'is_active', 'created_by', 'cloned_from',
    ];

    protected $casts = [
        'background'         => 'array',
        'elements'           => 'array',
        'published_elements' => 'array',
        'is_default'         => 'boolean',
        'is_active'          => 'boolean',
    ];

    public const CATEGORIES = ['nursery', 'primary', 'secondary', 'custom'];

    // ---- Scopes -----------------------------------------------------

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Starter templates shipped by the seeder (school_id is null). */
    public function scopeStarter(Builder $query): Builder
    {
        return $query->whereNull('school_id');
    }

    /** A specific school's own (cloned/custom) templates. */
    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        return $schoolId ? $query->where('school_id', $schoolId) : $query;
    }

    // ---- Helpers ------------------------------------------------------

    /**
     * The layout actually used to render live report cards.
     * Falls back to the draft if nothing has been published yet.
     */
    public function liveElements(): array
    {
        return $this->published_elements ?? $this->elements ?? [];
    }

    public function publish(): self
    {
        $this->update(['published_elements' => $this->elements]);

        return $this;
    }

    public function hasUnpublishedChanges(): bool
    {
        return $this->published_elements !== $this->elements;
    }

    /**
     * Duplicate this template as a new editable copy — this is how a school
     * takes one of the 3 starter templates (Nursery/Primary/Secondary) and
     * makes it their own, or clones an existing custom design.
     */
    public function duplicate(string $newName, ?int $schoolId = null, ?int $userId = null): self
    {
        $copy = $this->replicate(['is_default']);
        $copy->name = $newName;
        $copy->school_id = $schoolId ?? $this->school_id;
        $copy->is_default = false;
        $copy->created_by = $userId;
        $copy->cloned_from = $this->id;
        $copy->published_elements = $this->elements; // publish immediately so it works out of the box
        $copy->save();

        return $copy;
    }

    /**
     * Make this the template a school prints with for its category.
     * Unsets is_default on every other of that school's templates in the
     * same category first, so there's always exactly one "chosen" design
     * per school per category — this is what the renderer resolves against.
     */
    public function makeDefaultForSchool(): self
    {
        if ($this->school_id) {
            static::forSchool($this->school_id)
                ->category($this->category)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);
        }

        $this->update(['is_default' => true]);

        return $this;
    }

    /**
     * Resolve the template a given school should print with for a category:
     * 1. That school's own template marked is_default for the category.
     * 2. Any other active template that school owns in the category.
     * 3. The global starter default for the category.
     * 4. Any active starter template for the category (last resort).
     */
    public static function resolveForSchool(?int $schoolId, string $category): ?self
    {
        return static::forSchool($schoolId)->category($category)->active()->where('is_default', true)->first()
            ?? static::forSchool($schoolId)->category($category)->active()->latest()->first()
            ?? static::starter()->category($category)->active()->where('is_default', true)->first()
            ?? static::starter()->category($category)->active()->first();
    }
}
