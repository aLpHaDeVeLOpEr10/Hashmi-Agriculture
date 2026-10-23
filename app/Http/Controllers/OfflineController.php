<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OfflineController extends Controller
{
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function sync(Request $request, ExpenseService $expenses): JsonResponse
    {
        $this->authorize('enter-expenses');
        $entries = $request->validate([
            'entries' => ['required', 'array', 'max:100'],
            'entries.*.uuid' => ['required', 'uuid'],
            'entries.*.data' => ['required', 'array'],
        ])['entries'];

        $results = [];
        foreach ($entries as $entry) {
            $key = "offline-expense:{$request->user()->id}:{$entry['uuid']}";

            if ($existing = Cache::get($key)) {
                $results[] = ['uuid' => $entry['uuid'], 'ok' => true, 'expense_id' => $existing, 'duplicate' => true];

                continue;
            }

            try {
                $data = Validator::make($entry['data'], collect(ExpenseRequest::rulesFor($request->user()))->except(['receipt', 'payment_option', 'paid_amount', 'payment_method', 'payment_reference'])->all())->validate();
                $expense = $expenses->save($data);
                Cache::put($key, $expense->id, now()->addDays(30));
                $results[] = ['uuid' => $entry['uuid'], 'ok' => true, 'expense_id' => $expense->id];
            } catch (ValidationException $e) {
                $results[] = ['uuid' => $entry['uuid'], 'ok' => false, 'errors' => collect($e->errors())->flatten()->values()->all()];
            }
        }

        return response()->json(['results' => $results]);
    }
}
