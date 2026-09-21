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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("userId")->unsigned();
            $table->bigInteger("mediaAttendaceId")->unsigned();
            $table->bigInteger("mediaOfWorkId")->unsigned();
            $table->bigInteger("projectId")->unsigned();
            $table->string("latitude");
            $table->string("longtitude");
            $table->date("date");
            $table->time("time");
            $table->string("type");
            $table->timestamps();

            $table->foreign("userId")->references("id")->on("users");
            $table->foreign("mediaAttendaceId")->references("id")->on("medias");
            $table->foreign("mediaOfWorkId")->references("id")->on("medias");
            $table->foreign("projectId")->references("id")->on("projects");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
