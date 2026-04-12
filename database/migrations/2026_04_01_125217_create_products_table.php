<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->uuid('ameen_guid')->nullable(); 
            $table->string('name');
            $table->decimal('retail_price', 10, 2)->default(0); // سعر المفرق (القطعة)
            $table->decimal('wholesale_price', 10, 2)->default(0); // سعر الجملة
            $table->float('quantity')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
