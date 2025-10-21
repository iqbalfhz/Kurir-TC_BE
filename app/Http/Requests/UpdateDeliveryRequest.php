<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    public function authorize()
    {
        // Route is protected by auth:sanctum; allow validation pass here.
        return true;
    }

    public function rules()
    {
        return [
            'sender_name' => 'sometimes|required|string|max:191',
            'receiver_name' => 'sometimes|required|string|max:191',
            'address' => 'sometimes|required|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
