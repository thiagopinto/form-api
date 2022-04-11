<?php

namespace Database\Seeders\Location\The;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Location\The\TheNeighborhoodZone;
use App\Models\Location\The\TheNeighborhood;
use App\Models\Location\The\TheNeighborhoodGeography;

class NeighborhoodSeeder extends Seeder
{
    /*
    * Exceptions in lower case are words you don't want converted
    * Exceptions all in upper case are any words you don't want converted to title case
    *   but should be converted to upper case, e.g.:
    *   king henry viii or king henry Viii should be King Henry VIII
    */

    public function nameCase($string, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("de", "da", "dos", "das", "do", "I", "II", "III", "IV", "V", "VI"))
    {
        $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        foreach ($delimiters as $dlnr => $delimiter) {
            $words = explode($delimiter, $string);
            $newwords = array();
            foreach ($words as $wordnr => $word) {
                if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, "UTF-8");
                } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, "UTF-8");
                } elseif (!in_array($word, $exceptions)) {
                    // convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = join($delimiter, $newwords);
        }//foreach
        return $string;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/../../files/the/Neighborhood.geojson';
        $file = file_get_contents($path, true);

        if ($file) {
            $fileNeighborhood = json_decode($file, true);

            $features = $fileNeighborhood['features'];

            foreach ($features as $feature) {
                $geometry = json_encode($feature['geometry']);
                $zone = TheNeighborhoodZone::where(
                    'standardized',
                    $this->nameCase($feature['properties']['ZONA'])
                )->first();
                $neighborhood = new TheNeighborhood();

                $neighborhood->name = $feature['properties']['nomebairro'];
                $neighborhood->standardized = $this->nameCase($feature['properties']['nomebairro']);
                $neighborhood->metaphone = $neighborhood->getPhraseMetaphone($feature['properties']['nomebairro']);
                $neighborhood->soundex = soundex($feature['properties']['nomebairro']);

                $neighborhood->gid = $feature['properties']['gid'];
                $neighborhood->the_neighborhood_zone_id = $zone->id;

                $neighborhoodGeography = new TheNeighborhoodGeography();
                $neighborhoodGeography->area = DB::raw("ST_TRANSFORM(ST_GeomFromGeoJSON('{$geometry}'), 4326)");
                $neighborhood->save();
                $neighborhood->geography()->save($neighborhoodGeography);
            }
        }

        $path_urban_center = __DIR__ . '/../../files/the/UrbanCenters.geojson';
        $file_urban_center = file_get_contents($path_urban_center, true);

        if ($file_urban_center) {
            $fileUrbanCenter = json_decode($file_urban_center, true);

            $features = $fileUrbanCenter['features'];

            foreach ($features as $feature) {
                $geometry = json_encode($feature['geometry']);
                $zone = TheNeighborhoodZone::where(
                    'standardized',
                    $this->nameCase($feature['properties']['zona'])
                )->first();
                $neighborhood = new TheNeighborhood();

                $neighborhood->name = $feature['properties']['nomebairro'];
                $neighborhood->standardized = $this->nameCase($feature['properties']['nomebairro']);
                $neighborhood->metaphone = $neighborhood->getPhraseMetaphone($feature['properties']['nomebairro']);
                $neighborhood->soundex = soundex($feature['properties']['nomebairro']);

                $neighborhood->gid = $feature['properties']['gid'];
                $neighborhood->the_neighborhood_zone_id = $zone->id;

                $neighborhoodGeography = new TheNeighborhoodGeography();
                $neighborhoodGeography->area = DB::raw("ST_TRANSFORM(ST_GeomFromGeoJSON('{$geometry}'), 4326)");
                $neighborhood->save();
                $neighborhood->geography()->save($neighborhoodGeography);
            }
        }
    }
}
