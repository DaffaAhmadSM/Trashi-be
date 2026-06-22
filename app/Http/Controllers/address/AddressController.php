<?php

namespace App\Http\Controllers\address;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();

        return response()->json($addresses);
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = Address::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json($address, 201);
    }

    public function show(Address $address): JsonResponse
    {
        return response()->json($address);
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        $address->update($request->validated());

        return response()->json($address);
    }

    public function destroy(Address $address): JsonResponse
    {
        $address->delete();

        return response()->json(['message' => 'Address deleted.']);
    }
}
