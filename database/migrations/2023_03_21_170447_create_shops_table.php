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
        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('avatar')->default('https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcQjYmlp9JDeNMaFZzw9S3G1dVztGqF_2vq9nA&usqp=CAU&fbclid=IwAR2SQAloFwGL7-bZGs_T9QGN3INYsQXs1krNAuofn0qt7-vjfu-GPgIjYuA');
            $table->string('phone')->nullable();
            $table->date('birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('renewal')->default(true);
            $table->integer('times_renewal')->default(0);
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
