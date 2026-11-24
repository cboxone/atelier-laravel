<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // formulaire public
    }

    public function rules(): array
    {
        return [
            'service_id'     => ['nullable', 'integer', 'exists:services,id'],
            'equipment'      => ['required', 'string', 'max:255'],
            'problem'        => ['required', 'string', 'max:2000'],
            'district'       => ['required', 'string', 'max:120'],
            // Format indicatif : à ajuster selon la numérotation que vous acceptez.
            'phone'          => ['required', 'string', new PhoneNumber()],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'is_urgent'      => ['boolean'],
            'attachments'    => ['nullable', 'array', 'max:3'],
            'attachments.*'  => ['file', 'mimes:jpg,jpeg,png,webp,mp4', 'max:10240'], // 10 Mo
            'consent'        => ['accepted'],
            'website'        => ['prohibited'], // champ piège anti-spam : doit rester vide
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted'   => 'Vous devez accepter l’utilisation de vos informations pour traiter la demande.',
            'equipment.required' => 'Indiquez l’équipement concerné.',
            'problem.required'   => 'Décrivez brièvement la panne.',
            'district.required'  => 'Indiquez votre quartier.',
            'phone.required'     => 'Indiquez un numéro pour vous rappeler.',
        ];
    }
}
