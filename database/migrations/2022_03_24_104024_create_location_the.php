<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationThe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createNeighborhoodZones();
        $this->createNeighborhoods();
        $this->createNeighborhoodZoneGeographies();
        $this->createNeighborhoodGeographies();
        $this->createNeighborhoodSpellingVariations();
        $this->createNeighborhoodPopulations();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropNeighborhoodPopulations();
        $this->dropNeighborhoodSpellingVariations();
        $this->dropNeighborhoodGeographies();
        $this->dropNeighborhoodZoneGeographies();
        $this->dropNeighborhoods();
        $this->dropNeighborhoodZones();
    }

    /**
     * Run the create the_neighborhood_zones.
     *
     * @return void
     */
    public function createNeighborhoodZones()
    {
        Schema::create('the_neighborhood_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('standardized');
            $table->string('metaphone');
            $table->string('soundex');
            $table->integer('gid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the the_neighborhood_zones.
     *
     * @return void
     */
    public function dropNeighborhoodZones()
    {
        Schema::dropIfExists('the_neighborhood_zones');
    }

    /**
     * Run the create the_neighborhoods.
     *
     * @return void
     */
    public function createNeighborhoods()
    {
        Schema::create('the_neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('standardized');
            $table->string('metaphone');
            $table->string('soundex');
            $table->integer('gid');
            $table->unsignedBigInteger('the_neighborhood_zone_id')->nullable();
            $table->timestamps();
            $table->foreign('the_neighborhood_zone_id')->references('id')->on('the_neighborhood_zones');
        });
    }

    /**
     * Reverse the the_neighborhoods.
     *
     * @return void
     */
    public function dropNeighborhoods()
    {
        Schema::dropIfExists('the_neighborhoods');
    }

    /**
     * Run the create the_neighborhood_zone_geographies.
     *
     * @return void
     */
    public function createNeighborhoodZoneGeographies()
    {
        Schema::create('the_neighborhood_zone_geographies', function (Blueprint $table) {
            $table->id();
            $table->geometry('area');
            $table->unsignedBigInteger('the_neighborhood_zone_id');
            $table->timestamps();
            $table->foreign('the_neighborhood_zone_id')->references('id')->on('the_neighborhood_zones');
        });
    }

    /**
     * Reverse the the_neighborhood_zone_geographies.
     *
     * @return void
     */
    public function dropNeighborhoodZoneGeographies()
    {
        Schema::dropIfExists('the_neighborhood_zone_geographies');
    }

    /**
     * Run the create the_neighborhood_geographies.
     *
     * @return void
     */
    public function createNeighborhoodGeographies()
    {
        Schema::create('the_neighborhood_geographies', function (Blueprint $table) {
            $table->id();
            $table->geometry('area');
            $table->unsignedBigInteger('the_neighborhood_id');
            $table->timestamps();
            $table->foreign('the_neighborhood_id')->references('id')->on('the_neighborhoods');
        });
    }

    /**
     * Reverse the the_neighborhood_geographies.
     *
     * @return void
     */
    public function dropNeighborhoodGeographies()
    {
        Schema::dropIfExists('the_neighborhood_geographies');
    }

    /**
     * Run the create the_neighborhood_spelling_variations.
     *
     * @return void
     */
    public function createNeighborhoodSpellingVariations()
    {
        Schema::create('the_neighborhood_spelling_variations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('standardized');
            $table->string('metaphone');
            $table->string('soundex');
            $table->unsignedInteger('the_neighborhood_id')->nullable();
            $table->timestamps();
            $table->foreign('the_neighborhood_id')->references('id')->on('the_neighborhoods');
        });
    }

    /**
     * Reverse the the_neighborhood_spelling_variations.
     *
     * @return void
     */
    public function dropNeighborhoodSpellingVariations()
    {
        Schema::dropIfExists('the_neighborhood_spelling_variations');
    }

    /**
     * Run the create the_neighborhood_spelling_variations.
     *
     * @return void
     */
    public function createNeighborhoodPopulations()
    {
        Schema::create('the_neighborhood_populations', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->bigInteger('population');
            $table->unsignedBigInteger('the_neighborhood_id');
            $table->timestamps();
            $table->foreign('the_neighborhood_id')->references('id')->on('the_neighborhoods');
        });
    }

    /**
     * Reverse the the_neighborhood_spelling_variations.
     *
     * @return void
     */
    public function dropNeighborhoodPopulations()
    {
        Schema::dropIfExists('the_neighborhood_populations');
    }
}
