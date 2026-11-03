<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Product::firstOrCreate(
            ['code' => 'PRD-MOJ-COP'],
            [
                'name' => 'Mojito - Cocopandan',
                'category' => 'Minuman',
                'price' => 5000,
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['code' => 'PRD-MOJ-MEL'],
            [
                'name' => 'Mojito - Melon',
                'category' => 'Minuman',
                'price' => 5000,
                'sort_order' => 11,
                'is_active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Product::whereIn('code', ['PRD-MOJ-COP', 'PRD-MOJ-MEL'])->delete();
    }
};
