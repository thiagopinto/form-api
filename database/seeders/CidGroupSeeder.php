<?php

namespace Database\Seeders;

use App\Models\CidChapter;
use App\Models\CidGroup;
use Illuminate\Database\Seeder;

class CidGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/cid-10-groups.csv';
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
                    $cidGroup = new CidGroup();
                    $cidGroup->name = $data['descricao'];
                    $cidGroup->starting_code = $data['catinic'];
                    $cidGroup->final_code = $data['catfim'];
                    $cidChapter = CidChapter::where('starting_code', '<=', $cidGroup->starting_code)
                        ->where('final_code', '>=', $cidGroup->starting_code)
                        ->where('starting_code', '<=', $cidGroup->final_code)
                        ->where('final_code', '>=', $cidGroup->final_code)
                        ->first();
                    if ($cidChapter != null) {
                        $cidGroup->cid_chapter_id = $cidChapter->id;
                        $cidGroup->save();
                    }
                } catch (\Throwable $th) {
                    dd($th);
                }
            }
        }
    }
}
