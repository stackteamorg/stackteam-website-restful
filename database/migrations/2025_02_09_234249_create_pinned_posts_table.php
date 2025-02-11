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
        Schema::create('pinned_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained();
            $table->unsignedTinyInteger('position')->default(1); // 1, 2, or 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_posts');
    }
};
