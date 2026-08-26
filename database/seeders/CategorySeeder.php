<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $mainCategories = ['Masculino', 'Feminino', 'Bebês'];
        $subCategories = ['Camisetas', 'Camisas', 'Casacos', 'Calças', 'Bermudas', 'Conjuntos', 'Tudo'];

        foreach ($mainCategories as $mainName) {
            $parent = Category::create([
                'name' => $mainName,
                'slug' => Str::slug($mainName),
                'parent_id' => null,
            ]);

            foreach ($subCategories as $subName) {
                Category::create([
                    'name' => $subName,
                    'slug' => Str::slug($mainName . ' ' . $subName),
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}