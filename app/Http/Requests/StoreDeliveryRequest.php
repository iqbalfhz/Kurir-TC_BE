<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize()
    {
        // Route is protected by auth:sanctum. Allow request to pass validation here.
        return true;
    }

    public function rules()
    {
        return [
            'sender_name' => 'required|string|max:191',
            'delivered_by_name' => 'nullable|string|max:191',
            'receiver_name' => 'required|string|max:191',
            'address' => 'required|string',
            'notes' => 'nullable|string',
            // status allowed values: only 'selesai' (Selesai)
            'status' => "nullable|string|in:selesai",
            // Photo is required for new deliveries per updated schema
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
