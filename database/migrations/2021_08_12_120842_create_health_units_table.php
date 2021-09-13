<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('health_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code');
            $table->unsignedBigInteger('cnes_code');
            $table->unsignedBigInteger('cnpj_maintainer_code')->nullable();
            $table->string('company_name')->nullable();
            $table->string('alias_company_name')->nullable();
            $table->string('company_type')->nullable();
            $table->unsignedBigInteger('ibge_state_id')->nullable();
            $table->unsignedBigInteger('ibge_city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('cep_code')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->integer('stock_form_death')->default(100);
            $table->integer('stock_form_alive')->default(100);
            $table->timestamps();
            $table->index('cnes_code');
        });
    }

    /**
     * Reverse the migrations.
     *clear
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('health_units');
    }
}