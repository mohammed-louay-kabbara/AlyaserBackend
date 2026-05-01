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
        Schema::table('products', function (Blueprint $table) {
                // رفع عدد الخانات الإجمالي إلى 15، منها 2 بعد الفاصلة
                $table->decimal('retail_price', 15, 2)->change();
                
                // إذا كان لديك سعر جملة، يفضل تعديله أيضاً
                if (Schema::hasColumn('products', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 15, 2)->change();
                }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
