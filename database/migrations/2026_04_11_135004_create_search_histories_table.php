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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            // user_id يمكن أن يكون null إذا كنت تسمح بالبحث للزوار غير المسجلين
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('term'); // الكلمة المبحوث عنها
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
