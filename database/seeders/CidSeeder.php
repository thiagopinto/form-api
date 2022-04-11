<?php

namespace Database\Seeders;

use App\Models\Cid;
use App\Models\CidChapter;
use App\Models\CidGroup;
use App\Models\CidCategory;
use Illuminate\Database\Seeder;

class CidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/tb_agravo.csv';
        $file = file_get_contents($path, true);

        $delimiter = ',';
        $enclosure = '';

        if ($file) {
            $header = null;
            $datas = array();
            $rows = explode("\n", $file);

            foreach ($rows as $row) {
                $item = str_getcsv($row, $delimiter, $enclosure, "\n");
                if (!$header) {
                    $header = $item;
                    continue;
                }
                if (count($header) == count($item)) {
                    $datas[] = array_combine($header, $item);
                }
            }

            foreach ($datas as $data) {
                $cid = new Cid();
                $cid->code = str_replace('.', '', $data['co_cid']);
                $cid->code_dot = $data['co_cid'];
                $cid->description = $data['no_agravo'];
                $cid->deadline = (empty($data['nu_prazo_encerramento'])) ? null : $data['nu_prazo_encerramento'];
                $cid->compulsory_notification = false;
                $code = $cid->code;
                if (strlen($code) > 3) {
                    $code = substr($code, 0, 3);
                }
                $cidChapter = CidChapter::where('starting_code', '<=', $code)
                    ->where('final_code', '>=', $code)
                    ->first();
                $cidGroup = CidGroup::where('starting_code', '<=', $code)
                    ->where('final_code', '>=', $code)
                    ->first();
                $cidCategory = CidCategory::where('code', $code)
                    ->first();

                if ($cidChapter != null and $cidGroup != null and $cidCategory != null) {
                    $cid->cid_chapter_id = $cidChapter->id;
                    $cid->cid_group_id = $cidGroup->id;
                    $cid->cid_category_id = $cidCategory->id;
                    $cid->save();
                } elseif ($cidChapter == null) {
                    try {
                        $cidChapter = new CidChapter();
                        $cidChapter->name = $cid->description;
                        $cidChapter->starting_code = $cid->code;
                        $cidChapter->final_code = $cid->code;
                        $cidChapter->save();

                        $cidGroup = new CidGroup();
                        $cidGroup->name = $cid->description;
                        $cidGroup->starting_code = $cid->code;
                        $cidGroup->final_code = $cid->code;
                        $cidGroup->cid_chapter_id = $cidChapter->id;
                        $cidGroup->save();

                        $cidCategory = new CidCategory();
                        $cidCategory->name = $cid->description;
                        $cidCategory->code = $cid->code;
                        $cidCategory->cid_chapter_id = $cidChapter->id;
                        $cidCategory->cid_group_id = $cidGroup->id;
                        $cidCategory->save();

                        $cid->cid_chapter_id = $cidChapter->id;
                        $cid->cid_group_id = $cidGroup->id;
                        $cid->cid_category_id = $cidCategory->id;
                        $cid->save();
                    } catch (\Throwable $th) {
                        dd($data);
                    }
                } elseif ($cidGroup == null) {
                    $cidGroup = new CidGroup();
                    $cidGroup->name = $cid->description;
                    $cidGroup->starting_code = $cid->code;
                    $cidGroup->final_code = $cid->code;
                    $cidChapter = CidChapter::where('starting_code', '<=', $cidGroup->starting_code)
                        ->where('final_code', '>=', $cidGroup->starting_code)
                        ->where('starting_code', '<=', $cidGroup->final_code)
                        ->where('final_code', '>=', $cidGroup->final_code)
                        ->first();
                    $cidGroup->cid_chapter_id = $cidChapter->id;
                    $cidGroup->save();

                    $cidCategory = new CidCategory();
                    $cidCategory->name = $cid->description;
                    $cidCategory->code = $cid->code;
                    $cidCategory->cid_chapter_id = $cidChapter->id;
                    $cidCategory->cid_group_id = $cidGroup->id;
                    $cidCategory->save();

                    $cid->cid_chapter_id = $cidChapter->id;
                    $cid->cid_group_id = $cidGroup->id;
                    $cid->cid_category_id = $cidCategory->id;
                    $cid->save();
                } elseif ($cidCategory != null) {
                    $cidCategory = new CidCategory();
                    $cidCategory->name = $cid->description;
                    $cidCategory->code = $cid->code;
                    $cidChapter = CidChapter::where('starting_code', '<=', $cidCategory->code)
                        ->where('final_code', '>=', $cidCategory->code)
                        ->first();
                    $cidGroup = CidGroup::where('starting_code', '<=', $cidCategory->code)
                        ->where('final_code', '>=', $cidCategory->code)
                        ->first();
                    $cidCategory->cid_chapter_id = $cidChapter->id;
                    $cidCategory->cid_group_id = $cidGroup->id;
                    $cidCategory->save();

                    $cid->cid_chapter_id = $cidChapter->id;
                    $cid->cid_group_id = $cidGroup->id;
                    $cid->cid_category_id = $cidCategory->id;
                    $cid->save();
                } else {
                    var_dump($code);
                }
            }
        }
    }
}
