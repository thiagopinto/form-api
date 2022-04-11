<?php

namespace Database\Seeders;

use App\Models\CidChapter;
use Illuminate\Database\Seeder;

class CidChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/cid-10-capitulos.csv';
        $file = file_get_contents($path, true);

        $delimiter = ';';
        $enclosure = '';

        if ($file) {
            $header = null;
            $datas = array();
            $rows = explode("\n", $file);

            foreach ($rows as $row) {
                $item = str_getcsv($row, $delimiter, $enclosure, "\n");
                if (!$header) {
                    $header = $item;
                    $header = array_map('strtolower', $header);
                    continue;
                }
                if (count($header) == count($item)) {
                    $datas[] = array_combine($header, $item);
                }
            }

            foreach ($datas as $data) {
                try {
                    $cidChapter = new CidChapter();
                    $cidChapter->name = $data['descricao'];
                    $cidChapter->starting_code = $data['catinic'];
                    $cidChapter->final_code = $data['catfim'];
                    $cidChapter->save();
                } catch (\Throwable $th) {
                    dd($data);
                }

            }
        }
    }
}
