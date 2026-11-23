<?php

namespace App\Http\Requests\Culture;

use App\Enums\SafeguardingStatus;
use App\Models\IntangibleHeritage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveIntangibleHeritageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $heritage = $this->route('intangible_heritage');

        if ($heritage instanceof IntangibleHeritage) {
            return $this->user()?->can('update', $heritage) ?? false;
        }

        return $this->user()?->can('create', IntangibleHeritage::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $heritage = $this->route('intangible_heritage');
        $heritageId = $heritage instanceof IntangibleHeritage ? $heritage->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('intangible_heritages', 'name')
                    ->where(fn ($query) => $query->where('lga_id', $this->input('lga_id')))
                    ->ignore($heritageId),
            ],
            'lga_id' => ['nullable', 'exists:lgas,id'],
            'festival_season' => ['required', 'string', 'max:120'],
            'participating_communities' => ['required', 'string', 'max:2000'],
            'historical_description' => ['required', 'string', 'max:10000'],
            'safeguarding_status' => ['required', Rule::enum(SafeguardingStatus::class)],
        ];
    }
}
