<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AhspCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'sort_order',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AhspCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AhspCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function workTypes(): HasMany
    {
        return $this->hasMany(AhspWorkType::class);
    }

    // Recursive children for tree building
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    // Get full code path (e.g., 1.1.1)
    public function getFullCodeAttribute(): string
    {
        $code = trim($this->code);

        if ($this->parent) {
            $parentFullCode = trim($this->parent->full_code);

            // If current code already starts with parent's full code + dot (e.g. parent=1.1, code=1.1.1),
            // then just return the code as is to avoid 1.1.1.1.1
            if (str_starts_with($code, $parentFullCode . '.')) {
                return $code;
            }

            return $parentFullCode . '.' . $code;
        }
        return $code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Get flat list of categories with indentation for dropdown
     * Returns collection with 'id', 'display_name' (indented), 'full_code', 'level'
     */
    public static function getFlatListWithIndent(bool $includeInactive = false): \Illuminate\Support\Collection
    {
        // Use withCount to get work type counts in a single query instead of N+1
        $query = self::ordered();

        if (!$includeInactive) {
            $query->active();
        }

        $allCategories = $query->withCount([
            'workTypes as active_work_types_count' => function ($query) {
                $query->where('is_active', true);
            }
        ])
            ->get();
        $result = collect();

        $buildTree = function ($parentId = null, $level = 0) use (&$buildTree, &$result, $allCategories) {
            // Filter children
            $categories = $allCategories->where('parent_id', $parentId);

            // Apply Natural Sort (1, 2, 10 instead of 1, 10, 2)
            // Still respect explicit sort_order first if it exists (not 0)
            $categories = $categories->sort(function ($a, $b) {
                if ($a->sort_order != $b->sort_order) {
                    return $a->sort_order <=> $b->sort_order;
                }
                return strnatcmp($a->code, $b->code);
            });

            foreach ($categories as $category) {
                $indent = str_repeat('— ', $level);
                $result->push([
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'full_code' => $category->full_code,
                    'level' => $level,
                    'display_name' => $indent . $category->code . ' - ' . $category->name,
                    'has_work_types' => $category->active_work_types_count > 0,
                ]);

                // Recursively add children
                $buildTree($category->id, $level + 1);
            }
        };

        $buildTree();

        return $result;
    }

    /**
     * Get all nested children IDs (for getting all work types in a category tree)
     */
    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }
}
