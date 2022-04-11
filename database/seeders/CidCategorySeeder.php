<?php

namespace Database\Seeders;

use App\Models\CidChapter;
use App\Models\CidGroup;
use App\Models\CidCategory;
use Illuminate\Database\Seeder;

class CidCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/cid-10-categorias.csv';
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
                    $cidCategory = new CidCategory();
                    $cidCategory->name = $data['descricao'];
                    $cidCategory->code = $data['cat'];
                    $cidChapter = CidChapter::where('starting_code', '<=', $cidCategory->code)
                        ->where('final_code', '>=', $cidCategory->code)
                        ->first();
                    $cidGroup = CidGroup::where('starting_code', '<=', $cidCategory->code)
                        ->where('final_code', '>=', $cidCategory->code)
                        ->first();
                    if ($cidChapter != null and $cidGroup != null) {
                        $cidCategory->cid_chapter_id = $cidChapter->id;
                        $cidCategory->cid_group_id = $cidGroup->id;
                        $cidCategory->save();
                    }
                } catch (\Throwable $th) {
                    dd($th);
                }
            }
        }
    }
}
