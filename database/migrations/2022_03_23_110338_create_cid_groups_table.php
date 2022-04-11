<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCidGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cid_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('starting_code');
            $table->string('final_code');
            $table->unsignedBigInteger('cid_chapter_id');
            $table->foreign('cid_chapter_id')->references('id')->on('cid_chapters');
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
        Schema::dropIfExists('cid_groups');
    }
}
