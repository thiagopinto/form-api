<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddPostgisExtensionToPostgresql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis_raster";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis_topology";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis_sfcgal";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "fuzzystrmatch";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "address_standardizer";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "address_standardizer_data_us";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis_tiger_geocoder";');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
/*         DB::statement('DROP EXTENSION IF EXISTS "postgis";');
DB::statement('DROP EXTENSION IF EXISTS "postgis_raster";');
DB::statement('DROP EXTENSION IF EXISTS "postgis_topology";');
DB::statement('DROP EXTENSION IF EXISTS "postgis_sfcgal";');
DB::statement('DROP EXTENSION IF EXISTS "fuzzystrmatch";');
DB::statement('DROP EXTENSION IF EXISTS "address_standardizer";');
DB::statement('DROP EXTENSION IF EXISTS "address_standardizer_data_us";');
DB::statement('DROP EXTENSION IF EXISTS "postgis_tiger_geocoder";'); */
    }
}
