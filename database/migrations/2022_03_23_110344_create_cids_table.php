<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cids', function (Blueprint $table) {
            $table->id();
            $table->string("code");
            $table->string("code_dot");
            $table->string("description");
            $table->integer("deadline")->nullable();
            $table->boolean("compulsory_notification");
            $table->unsignedBigInteger('cid_chapter_id');
            $table->unsignedBigInteger('cid_group_id');
            $table->unsignedBigInteger('cid_category_id');
            $table->foreign('cid_group_id')->references('id')->on('cid_groups');
            $table->foreign('cid_chapter_id')->references('id')->on('cid_chapters');
            $table->foreign('cid_category_id')->references('id')->on('cid_categories');
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
        Schema::dropIfExists('cids');
    }
}
