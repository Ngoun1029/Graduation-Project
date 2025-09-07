<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('seller_id')->constrained('sellers');
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock');
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->enum('status', ['inactive', 'active', 'draft', 'pending'])->default('pending');
            $table->unsignedInteger('sale_count')->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->json('image')->default(DB::raw('(JSON_OBJECT())'))->nullable();
            $table->boolean('free_delivery')->default(false);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->boolean('in_stock')->default(value: true);
            $table->enum('badge', ['none', 'top_sale', 'best_selling', 'new_arrival'])
                ->default('none');
            $table->json('attributes')->nullable()->comment('{"colors":["Red","Black"],"sizes":["S","M","L"]}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
