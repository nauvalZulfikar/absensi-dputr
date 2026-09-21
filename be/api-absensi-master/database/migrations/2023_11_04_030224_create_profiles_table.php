<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("userId")->unsigned();
            $table->bigInteger("mediaId")->unsigned()->nullable();
            $table->string("jabatan")->nullable();
            $table->string("name");
            $table->string("gender")->nullable();
            $table->string("address")->nullable();
            $table->string("phoneNumber")->nullable();
            $table->timestamps();

            $table->foreign("userId")->references("id")->on("users");
            $table->foreign("mediaId")->references("id")->on("medias");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
};
