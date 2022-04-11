<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCboDatasusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cbo_datasus', function (Blueprint $table) {
            $table->id();
            $table->integer('co_ocupacao');
            $table->string('ds_ocupacao');
            $table->string('co_cbo');
            $table->integer('tp_escolaridade')->nullable();
            $table->integer('st_restricao_idade')->nullable();
            $table->timestamps();
            $table->index('co_cbo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbo_datasus');
    }
}
