<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\HarvestRecord;
use App\Models\SilageBatch;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function __construct(private SeasonService $seasons) {}

    public function saveHarvest(CropSeason $season, array $data, ?HarvestRecord $harvest = null): HarvestRecord
    {
        $this->seasons->ensureOpen($season);

        if ($data['method'] === 'trolley_count') {
            if (empty($data['trolley_count']) || empty($data['avg_trolley_kg'])) {
                throw ValidationException::withMessages(['trolley_count' => __('Enter the number of trolleys and their average weight.')]);
            }
            $data['quantity_kg'] = round((int) $data['trolley_count'] * (float) $data['avg_trolley_kg'], 3);
        } else {
            $data['trolley_count'] = null;
            $data['avg_trolley_kg'] = null;
        }

        $harvest ??= new HarvestRecord(['season_id' => $season->id]);
        $harvest->fill(collect($data)->only(['field_id', 'harvest_date', 'quantity_kg', 'method', 'trolley_count', 'avg_trolley_kg', 'moisture_pct'])->all());
        $harvest->save();

        if (in_array($season->status, ['planned', 'active'], true)) {
            $season->update(['status' => 'harvested']);
        }

        return $harvest;
    }

    public function deleteHarvest(HarvestRecord $harvest): void
    {
        $this->seasons->ensureOpen($harvest->season);
        $harvest->delete();
    }

    public function saveBatch(CropSeason $season, array $data, ?SilageBatch $batch = null): SilageBatch
    {
        $this->seasons->ensureOpen($season);

        $batch ??= new SilageBatch(['season_id' => $season->id, 'status' => 'filling']);
        $batch->fill(collect($data)->only(['storage_unit_id', 'fill_start', 'sealed_date', 'fresh_weight_kg', 'method', 'expected_loss_pct'])->all());
        if ($batch->sealed_date && $batch->status === 'filling') {
            $batch->status = 'sealed';
        }
        $batch->save();

        if (in_array($season->status, ['planned', 'active', 'harvested'], true)) {
            $season->update(['status' => 'ensiled']);
        }

        return $batch;
    }

    public function deleteBatch(SilageBatch $batch): void
    {
        $this->seasons->ensureOpen($batch->season);
        $batch->delete();
    }

    public function advanceBatch(SilageBatch $batch, string $to, string $date): void
    {
        $allowed = ['sealed' => 'filling', 'feeding' => 'sealed', 'finished' => 'feeding'];

        if (($allowed[$to] ?? null) !== $batch->status) {
            throw ValidationException::withMessages(['status' => __('This batch cannot move from :from to :to.', ['from' => __(ucfirst($batch->status)), 'to' => __(ucfirst($to))])]);
        }

        $batch->status = $to;
        match ($to) {
            'sealed' => $batch->sealed_date ??= $date,
            'feeding' => $batch->opened_date ??= $date,
            default => null,
        };
        $batch->save();
    }
}
