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
        Schema::create('role_has_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("userId")->unsigned();
            $table->bigInteger("roleId")->unsigned();
            $table->timestamps();
            $table->foreign("userId")->references("id")->on("users");
            $table->foreign("roleId")->references("id")->on("roles");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_has_users');
    }
};
