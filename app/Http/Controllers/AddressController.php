<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function __construct(private AddressService $addressService) {}

    // lista enderecos do usuario logado
    public function index(): JsonResponse
    {
        $addresses = request()->user()->addresses()->get();
        return response()->json(['data' => $addresses]);
    }

    // salva um novo endereco
    public function store(AddressRequest $request): JsonResponse
    {
        $address = $this->addressService->createAddress($request->user(), $request->validated());
        return response()->json($address, 201);
    }

    // atualiza um endereco existente do usuario logado
    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'nao autorizado'], 403);
        }

        $updatedAddress = $this->addressService->updateAddress($address, $request->validated());
        return response()->json($updatedAddress);
    }

    // remove um endereco
    public function destroy(Address $address): JsonResponse
    {
        if ($address->user_id !== request()->user()->id) {
            return response()->json(['message' => 'nao autorizado'], 403);
        }

        $this->addressService->deleteAddress($address);
        return response()->json(null, 204);
    }
}