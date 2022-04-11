<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueryFilterCidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('query_filter_cids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid_id');
            $table->unsignedBigInteger('query_filter_id');
            $table->foreign('cid_id')->references('id')->on('cids');
            $table->foreign('query_filter_id')->references('id')->on('query_filters');
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
        Schema::dropIfExists('query_filter_cids');
    }
}
