<?php

namespace Database\Seeders\Location\The;

use App\Models\Location\The\TheNeighborhood;
use App\Models\Location\The\TheNeighborhoodPopulation;
use App\Models\MetaPhone;
use Illuminate\Database\Seeder;

class NeighborhoodPopulationSeeder extends Seeder
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
        } //foreach
        return $string;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/../../files/the/NeighborhoodPopulation.csv';
        $file = file_get_contents($path, true);

        $delimiter = ',';
        $enclosure = '';

        if ($file) {
            $header = null;
            $datas = array();
            $rows = explode("\n", $file);

            foreach ($rows as $row) {
                $item = str_getcsv($row, $delimiter, $enclosure, "\n");

                for ($i = 0; $i < count($item); $i++) {
                    $item[$i] = trim($item[$i]);
                }

                if (!$header) {
                    $header = $item;
                    continue;
                }
                if (count($header) == count($item)) {
                    $datas[] = array_combine($header, $item);
                }
            }

            foreach ($datas as $data) {
                $neighborhood = TheNeighborhood::where('standardized', $data['name'])->first();

                if ($neighborhood == null) {
                    $neighborhood = TheNeighborhood::where('standardized', $this->nameCase($data['name']))->first();
                }

                if ($neighborhood == null) {
                    $metaphone = new Metaphone();
                    $neighborhood = TheNeighborhood::where('metaphone', $metaphone->getPhraseMetaphone($data['name']))->first();
                }

                if ($neighborhood == null) {
                    $neighborhood = TheNeighborhood::where('soundex', soundex($data['name']))->first();
                }

                if ($neighborhood == null) {
                    $neighborhood = new TheNeighborhood();
                    $neighborhood->name = $data['name'];
                    $neighborhood->standardized = $this->nameCase($data['name']);
                    $neighborhood->metaphone = $neighborhood->getPhraseMetaphone($data['name']);
                    $neighborhood->soundex = soundex($data['name']);

                    $neighborhood->gid = $data['gid'];
                    $neighborhood->save();
                }

                $neighborhoodPopulation = TheNeighborhoodPopulation::updateOrCreate(
                    [
                        'the_neighborhood_id' => $neighborhood->id,
                        'year' => '2020'
                    ],
                    [
                        'population' => $data['population_2020']
                    ]
                );

                print($neighborhood->name);
                print($neighborhoodPopulation->population);
            }
        }
    }
}
