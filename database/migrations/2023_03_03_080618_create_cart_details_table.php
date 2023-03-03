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
        Schema::create('cart_details', function (Blueprint $table) {
            $table->increments('id');
            $table->text('note');
            $table->string('city');
            $table->string('address');
            $table->unsignedInteger('cart_id');
            $table->foreign('cart_id')
                ->references('id')
                ->on('carts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_details');
    }
};
