<?php

namespace App\Http\Requests;

use App\Models\CropSeason;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('expense')
            ? $this->user()->can('edit-expenses')
            : $this->user()->can('enter-expenses');
    }

    public function paymentRequested(): bool
    {
        return in_array($this->input('payment_option'), ['full', 'partial'], true) && $this->user()->can('record-payments');
    }

    public function rules(): array
    {
        return self::rulesFor($this->user());
    }

    public static function rulesFor(User $user): array
    {
        $farm = $user->farm_id;

        return [
            'season_stage_id' => ['required', 'integer', Rule::exists('season_stages', 'id')->whereIn('season_id', CropSeason::query()->pluck('id'))],
            'category_id' => ['required', Rule::exists('expense_categories', 'id')->where(fn ($q) => $q->whereNull('farm_id')->orWhere('farm_id', $farm))->where('is_active', true)],
            'expense_date' => ['required', 'date', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'description' => ['required', 'string', 'max:200'],
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'unit_code' => ['nullable', Rule::exists('units', 'code')],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999999999'],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('farm_id', $farm)],
            'resource_id' => ['nullable', Rule::exists('resources', 'id')->where('farm_id', $farm)],
            'field_id' => ['nullable', Rule::exists('fields', 'id')->where('farm_id', $farm)],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,pdf', 'max:8192'],
            'payment_option' => ['nullable', Rule::in(['unpaid', 'full', 'partial'])],
            'paid_amount' => ['nullable', 'required_if:payment_option,partial', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'required_if:payment_option,full,partial', Rule::in(Payment::METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function attributes(): array
    {
        return [
            'season_stage_id' => __('stage'),
            'category_id' => __('category'),
            'expense_date' => __('date'),
            'unit_code' => __('unit'),
            'unit_price' => __('unit price'),
            'vendor_id' => __('vendor'),
            'resource_id' => __('machine'),
            'field_id' => __('field'),
            'paid_amount' => __('amount paid'),
            'payment_method' => __('payment method'),
        ];
    }
}
