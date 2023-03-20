<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('email_verified')->default(false);
            $table->boolean('expired_time')->default(false);
            $table->string('confirmation_code')->nullable();
            $table->dateTime('confirmation_code_expired_in')->nullable();
            $table->string('avatar')->default('https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcQjYmlp9JDeNMaFZzw9S3G1dVztGqF_2vq9nA&usqp=CAU&fbclid=IwAR2SQAloFwGL7-bZGs_T9QGN3INYsQXs1krNAuofn0qt7-vjfu-GPgIjYuA');
            $table->string('phone')->nullable();
            $table->date('birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('renewal')->default(false);
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
