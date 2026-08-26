<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;

class AddressService
{
    // cria endereco e atualiza padrao se necessario
    public function createAddress(User $user, array $data): Address
    {
        $this->handleDefaultAddress($user, $data);
        return $user->addresses()->create($data);
    }

    // atualiza endereco existente e verifica padrao
    public function updateAddress(Address $address, array $data): Address
    {
        $this->handleDefaultAddress($address->user, $data, $address->id);
        $address->update($data);
        return $address;
    }

    // remove endereco do banco
    public function deleteAddress(Address $address): void
    {
        $address->delete();
    }

    // desmarca outros enderecos caso o novo seja o padrao principal
    private function handleDefaultAddress(User $user, array &$data, ?int $ignoreId = null): void
    {
        $isDefault = $data['is_default'] ?? false;

        if (!$isDefault) {
            return;
        }

        $query = $user->addresses()->where('is_default', true);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $query->update(['is_default' => false]);
    }
}