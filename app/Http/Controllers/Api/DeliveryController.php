<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::query()->with('user');

        // optional: filter by user
        if ($request->has('my') && $request->boolean('my') && $request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $perPage = (int) $request->get('per_page', 15);
        $deliveries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($deliveries);
    }

    public function show(Delivery $delivery)
    {
        $delivery->load('user');
        return response()->json($delivery);
    }

    public function store(StoreDeliveryRequest $request)
    {
    $data = $request->validated();
    $data['user_id'] = $request->user() ? $request->user()->id : null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('deliveries', 'public');
            $data['photo'] = $path;
        }

        $delivery = Delivery::create($data);

        return response()->json($delivery, 201);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        // allow update only if owner or admin; simple ownership check here
        if ($request->user() && $delivery->user_id && $request->user()->id !== $delivery->user_id && ! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // delete old
            if ($delivery->photo) {
                Storage::disk('public')->delete($delivery->photo);
            }
            $path = $request->file('photo')->store('deliveries', 'public');
            $data['photo'] = $path;
        }

        $delivery->update($data);

        return response()->json($delivery);
    }

    public function destroy(Delivery $delivery)
    {
        // allow delete only if owner or admin
        if ($request->user() && $delivery->user_id && $request->user()->id !== $delivery->user_id && ! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($delivery->photo) {
            Storage::disk('public')->delete($delivery->photo);
        }

        $delivery->delete();

        return response()->json(null, 204);
    }
}
