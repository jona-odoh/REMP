<?php

namespace App\Http\Requests\Licensing;

use App\Models\Hotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');

        if ($hotel instanceof Hotel) {
            return $this->user()?->can('update', $hotel) ?? false;
        }

        return $this->user()?->can('create', Hotel::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hotel = $this->route('hotel');
        $hotelId = $hotel instanceof Hotel ? $hotel->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:180',
                Rule::unique('hotels', 'name')
                    ->where(fn ($q) => $q->where('lga_id', $this->input('lga_id')))
                    ->ignore($hotelId),
            ],
            'address' => ['required', 'string', 'max:255'],
            'lga_id' => ['required', 'exists:lgas,id'],
            'establishment_type' => ['required', Rule::in(['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'])],
            'capacity_rooms' => ['required', 'integer', 'min:1', 'max:10000'],
            'capacity_beds' => ['required', 'integer', 'min:1', 'max:50000'],
            'owner_name' => ['required', 'string', 'max:150'],
            'owner_contact' => ['required', 'string', 'max:100'],
            'classification_rating' => ['required', 'string', 'max:60'],
            'latitude' => ['nullable', 'numeric', 'between:6.0,9.5', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:4.5,8.5', 'required_with:latitude'],
        ];
    }
}
