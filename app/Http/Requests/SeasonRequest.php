<?php

namespace App\Http\Requests;

use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-seasons');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'crop_id' => ['required', Rule::exists('crops', 'id')->where('is_active', true)],
            'start_date' => ['required', 'date'],
            'expected_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ];
    }

    /**
     * @return array<int, string> field id => acres, limited to this farm's fields
     */
    public function fieldAreas(): array
    {
        $farmFields = Field::query()->pluck('id')->all();

        return collect($this->validated('fields'))
            ->filter(fn ($area, $id) => is_numeric($area) && $area > 0 && in_array((int) $id, $farmFields, true))
            ->all();
    }

    public function seasonData(): array
    {
        return ['fields' => $this->fieldAreas()] + $this->safe()->except('fields');
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $areas = collect($this->input('fields', []))->filter(fn ($v) => is_numeric($v) && $v > 0);
            if ($areas->isEmpty()) {
                $validator->errors()->add('fields', __('Select at least one field and enter its acres.'));
            }
        });
    }
}
