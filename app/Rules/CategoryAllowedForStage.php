<?php

namespace App\Rules;

use App\Models\ExpenseCategory;
use App\Models\SeasonStage;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects a category that is not linked to the stage the expense is booked
 * against. The category an expense already has is always accepted, so old
 * expenses can still be edited after a category's stages change.
 */
class CategoryAllowedForStage implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function __construct(private ?int $keepCategoryId = null) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->keepCategoryId !== null && (int) $value === $this->keepCategoryId) {
            return;
        }

        $templateId = SeasonStage::query()->whereKey($this->data['season_stage_id'] ?? null)->value('stage_template_id');
        $category = ExpenseCategory::query()->with(['stages:id', 'parent.stages:id'])->find($value);

        if ($templateId && $category && ! $category->availableFor($templateId)) {
            $fail(__('This category is not used in the selected stage.'));
        }
    }
}
