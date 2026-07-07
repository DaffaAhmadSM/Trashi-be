<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Office::all());
    }

    public function show(Office $office): JsonResponse
    {
        return response()->json($office);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'office_name' => ['required', 'string', 'max:255'],
            'office_address' => ['required', 'string'],
            'office_phone' => ['required', 'string', 'max:20'],
            'address_latitude' => ['required', 'numeric', 'between:-90,90'],
            'address_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $office = Office::create($validator->validated());

        return response()->json($office, 201);
    }

    public function update(Request $request, Office $office): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'office_name' => ['sometimes', 'string', 'max:255'],
            'office_address' => ['sometimes', 'string'],
            'office_phone' => ['sometimes', 'string', 'max:20'],
            'address_latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'address_longitude' => ['sometimes', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $office->update($validator->validated());

        return response()->json($office);
    }

    public function destroy(Office $office): JsonResponse
    {
        $office->delete();

        return response()->json(['message' => 'Office deleted.']);
    }
}
