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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("devisionId")->unsigned();
            $table->integer("userId");
            $table->string("projectNo");
            $table->date("startdate");
            $table->date("targetdate");
            $table->integer("cost");
            $table->string("status");
            $table->boolean("rowStatus");
            $table->string("address");
            $table->string("latitude");
            $table->string("longtitude");
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
        Schema::dropIfExists('projects');
    }
};
