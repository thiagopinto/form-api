<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CboDatasus;

class CboDatasusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/tb_ocupacao.csv';
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

            // "co_ocupacao","ds_ocupacao","co_cbo","tp_escolaridade","st_restricao_idade"

            foreach ($datas as $data) {
                $occupation = new CboDatasus();
                $occupation->co_ocupacao = (empty($data['co_ocupacao'])) ? null : $data['co_ocupacao'];
                $occupation->ds_ocupacao = (empty($data['ds_ocupacao'])) ? null : $data['ds_ocupacao'];
                $occupation->co_cbo = (empty($data['co_cbo'])) ? null : $data['co_cbo'];
                $occupation->tp_escolaridade = (empty($data['tp_escolaridade'])) ? null : $data['tp_escolaridade'];
                $occupation->st_restricao_idade = (empty($data['st_restricao_idade'])) ? null : $data['st_restricao_idade'];
                $occupation->save();
            }
        }
    }
}
