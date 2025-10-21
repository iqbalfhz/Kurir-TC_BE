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

        // defensive checks for PHP file upload problems (temp dir permission, disk full, etc.)
        if ($request->hasFile('photo')) {
            // check PHP upload error if present
            $fileInputName = 'photo';
            $phpError = null;
            if (isset($_FILES[$fileInputName]) && isset($_FILES[$fileInputName]['error'])) {
                $phpError = (int) $_FILES[$fileInputName]['error'];
            }

            if ($phpError !== null && $phpError !== UPLOAD_ERR_OK) {
                // log and return clear message
                logger()->error('File upload PHP error', ['error' => $phpError, 'file' => $_FILES[$fileInputName] ?? null]);
                return response()->json(['message' => 'File upload failed (PHP error). Please check server temporary directory and PHP configuration.','errors' => ['photo' => ['validation.uploaded']]], 500);
            }

            // ensure temp dir writable
            $tmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
            if (! is_dir($tmpDir) || ! is_writable($tmpDir)) {
                logger()->error('PHP upload tmp dir not writable', ['upload_tmp_dir' => $tmpDir]);
                return response()->json(['message' => 'Server cannot write temporary files for uploads. Check upload_tmp_dir and permissions.'], 500);
            }

            try {
                // ensure tmp directory exists on local disk
                Storage::disk('local')->makeDirectory('tmp');

                $uploaded = $request->file('photo');
                $filename = now()->format('YmdHis') . '_' . uniqid() . '.' . $uploaded->getClientOriginalExtension();

                // store a temporary copy on local disk (storage/app/tmp)
                // use UploadedFile::storeAs which is reliable in tests and runtime
                $tmpRelative = 'tmp/' . $filename;
                $stored = $uploaded->storeAs('tmp', $filename, 'local');

                if ($stored !== $tmpRelative) {
                    logger()->error('Failed to store temporary uploaded file', ['file' => $uploaded, 'stored' => $stored]);
                    return response()->json(['message' => 'Failed to save temporary uploaded file.'], 500);
                }

                    if (! Storage::disk('local')->exists($tmpRelative)) {
                        logger()->error('Temporary file missing after storeAs (storage disk)', ['expected' => $tmpRelative]);
                        return response()->json(['message' => 'Temporary uploaded file missing on server.'], 500);
                    }

                    $tmpFullPath = Storage::disk('local')->path($tmpRelative);

                // move from local tmp to public disk (deliveries)
                $finalPath = Storage::disk('public')->putFileAs('deliveries', new \Illuminate\Http\File($tmpFullPath), $filename);

                if (! $finalPath) {
                    logger()->error('Failed to move file from tmp to public', ['tmp' => $tmpRelative]);
                    return response()->json(['message' => 'Failed to move uploaded file to public storage.'], 500);
                }

                // delete temporary file
                Storage::disk('local')->delete($tmpRelative);

                $data['photo'] = $finalPath;
            } catch (\Throwable $e) {
                logger()->error('Failed to store uploaded file', ['exception' => $e]);
                return response()->json(['message' => 'Failed to save uploaded file on server.','detail' => $e->getMessage()], 500);
            }
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
            $fileInputName = 'photo';
            $phpError = null;
            if (isset($_FILES[$fileInputName]) && isset($_FILES[$fileInputName]['error'])) {
                $phpError = (int) $_FILES[$fileInputName]['error'];
            }

            if ($phpError !== null && $phpError !== UPLOAD_ERR_OK) {
                logger()->error('File upload PHP error on update', ['error' => $phpError, 'file' => $_FILES[$fileInputName] ?? null]);
                return response()->json(['message' => 'File upload failed (PHP error). Please check server temporary directory and PHP configuration.','errors' => ['photo' => ['validation.uploaded']]], 500);
            }

            $tmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
            if (! is_dir($tmpDir) || ! is_writable($tmpDir)) {
                logger()->error('PHP upload tmp dir not writable on update', ['upload_tmp_dir' => $tmpDir]);
                return response()->json(['message' => 'Server cannot write temporary files for uploads. Check upload_tmp_dir and permissions.'], 500);
            }

            try {
                // ensure tmp directory exists on local disk
                Storage::disk('local')->makeDirectory('tmp');

                $uploaded = $request->file('photo');
                $filename = now()->format('YmdHis') . '_' . uniqid() . '.' . $uploaded->getClientOriginalExtension();

                // store a temporary copy on local disk
                $tmpPath = Storage::disk('local')->putFileAs('tmp', $uploaded, $filename);
                if (! $tmpPath) {
                    logger()->error('Failed to store temporary uploaded file on update', ['file' => $uploaded]);
                    return response()->json(['message' => 'Failed to save temporary uploaded file.'], 500);
                }

                // delete old
                if ($delivery->photo) {
                    Storage::disk('public')->delete($delivery->photo);
                }

                // move to public disk
                    if (! Storage::disk('local')->exists($tmpPath)) {
                        logger()->error('Temporary file missing after storeAs (storage disk) on update', ['expected' => $tmpPath]);
                        return response()->json(['message' => 'Temporary uploaded file missing on server.'], 500);
                    }

                    $tmpFullPath = Storage::disk('local')->path($tmpPath);
                    $finalPath = Storage::disk('public')->putFileAs('deliveries', new \Illuminate\Http\File($tmpFullPath), $filename);
                if (! $finalPath) {
                    logger()->error('Failed to move file from tmp to public on update', ['tmp' => $tmpPath]);
                    return response()->json(['message' => 'Failed to move uploaded file to public storage.'], 500);
                }

                // delete temporary file
                Storage::disk('local')->delete($tmpPath);

                $data['photo'] = $finalPath;
            } catch (\Throwable $e) {
                logger()->error('Failed to store uploaded file on update', ['exception' => $e]);
                return response()->json(['message' => 'Failed to save uploaded file on server.','detail' => $e->getMessage()], 500);
            }
        }

        $delivery->update($data);

        return response()->json($delivery);
    }

    public function destroy(Request $request, Delivery $delivery)
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
