@props(['seasons', 'name' => 'season_stage_id', 'model' => null])

@php
    $options = $seasons->map(fn ($season) => [
        'id' => $season->id,
        'stages' => $season->stages->values()->map(fn ($stage, $i) => ['id' => $stage->id, 'label' => ($i + 1).'. '.$stage->name()]),
    ])->values();
    $initial = old($name);
@endphp

<div class="grid grid-cols-1 gap-4"
    x-data="{
        options: {{ Js::from($options) }},
        season: '',
        stage: '',
        get stages() { return this.options.find((s) => String(s.id) === String(this.season))?.stages ?? []; },
        pick(stageId) {
            this.stage = stageId ? String(stageId) : '';
            this.season = String(this.options.find((s) => s.stages.some((st) => String(st.id) === this.stage))?.id ?? (this.options.length === 1 ? this.options[0].id : ''));
        },
        init() {
            this.pick({{ Js::from($initial) }});
            @if ($model)
                this.$watch(() => {{ $model }}, (value) => { if (String(value ?? '') !== this.stage) this.pick(value); });
                this.pick({{ $model }} || {{ Js::from($initial) }});
            @endif
        },
    }">
    <x-form.select :label="__('Season')" name="_season" required x-model="season" x-on:change="stage = ''; {{ $model ? $model.' = \'\'' : '' }}">
        @foreach ($seasons as $season)
            <option value="{{ $season->id }}">{{ $season->name }} · {{ $season->crop->name }} ({{ $season->code }})</option>
        @endforeach
    </x-form.select>

    <div x-show="season" x-cloak>
        <x-form.select :label="__('Stage')" :name="$name" required x-model="stage" x-bind:disabled="!season" x-on:change="{{ $model ? $model.' = stage' : '' }}">
            <template x-for="option in stages" :key="option.id">
                <option :value="option.id" x-text="option.label" :selected="String(option.id) === stage"></option>
            </template>
        </x-form.select>
    </div>
</div>
