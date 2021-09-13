<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBornAliveFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('born_alive_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('number');
            $table->unsignedBigInteger('range_number_start')->nullable();
            $table->unsignedBigInteger('range_number_end')->nullable();
            $table->string('name')->nullable();
            $table->date('event_date')->nullable();
            $table->string('responsible')->nullable();
            $table->integer('status')->comment('1 - stoque, 2 - distribuido, 3 - recebido, 4 - nula');
            $table->unsignedBigInteger('cnes_code')->nullable();
            $table->unsignedBigInteger('cnes_code_devolution')->nullable();
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
        Schema::dropIfExists('born_alive_forms');
    }
}
