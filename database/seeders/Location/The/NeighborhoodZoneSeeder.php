<?php

namespace Database\Seeders\Location\The;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Location\City;
use App\Models\Location\The\TheNeighborhoodZone;
use App\Models\Location\The\TheNeighborhoodZoneGeography;

class NeighborhoodZoneSeeder extends Seeder
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
        $path = __DIR__ . '/../../files/the/NeighborhoodZone.geojson';
        $file = file_get_contents($path, true);

        if ($file) {
            $fileNeighborhoodZone = json_decode($file, true);

            $zones = $fileNeighborhoodZone['features'];

            foreach ($zones as $zone) {
                $feature = json_encode($zone['geometry']);
                $neighborhoodZone = new TheNeighborhoodZone();
                $neighborhoodZone->name = $zone['properties']['zona'];
                $neighborhoodZone->standardized = $this->nameCase($zone['properties']['zona']);
                $neighborhoodZone->metaphone = $neighborhoodZone->getPhraseMetaphone($zone['properties']['zona']);
                $neighborhoodZone->soundex = soundex($zone['properties']['zona']);
                $neighborhoodZone->gid = $zone['properties']['gid'];
                //$neighborhoodZone->save();
                $neighborhoodZoneGeography = new TheNeighborhoodZoneGeography();
                $neighborhoodZoneGeography->area = DB::raw("ST_TRANSFORM(ST_GeomFromGeoJSON('{$feature}'), 4326)");
                $neighborhoodZone->save();
                $neighborhoodZone->geography()->save($neighborhoodZoneGeography);
            }
        }
    }
}
