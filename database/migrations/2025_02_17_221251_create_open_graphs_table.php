<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenGraphsTable extends Migration
{
    public function up()
    {
        Schema::create('open_graphs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // Relates to posts table
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable(); // Path for Open Graph image if needed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('open_graphs');
    }
}
