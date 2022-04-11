<?php

namespace Database\Seeders;

use App\Models\HealthUnit;
use App\Models\Location\State;
use Illuminate\Database\Seeder;

class HealthUnitSeeder extends Seeder
{
    public function checkNull($value) {
        if (is_null($value) | empty($value) | strcmp($value, 'undefined') == 0) {
            return true;
        }
        return false;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = __DIR__ . '/files/tbEstabelecimento.csv';
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
                    continue;
                }
                if (count($header) == count($item)) {
                    $datas[] = array_combine($header, $item);
                }
            }

            foreach ($datas as $data) {
                $initials = explode(',', env('STATES'));

                foreach ($initials as $initial) {

                    $state = State::where('initials', trim($initial))->first();

                    if ($data['CO_ESTADO_GESTOR'] == $state->ibge_id) {
                        $healthUnit = new HealthUnit();
                        $healthUnit->unit_code = (empty($data['CO_UNIDADE'])) ? null : $data['CO_UNIDADE'];
                        $healthUnit->cnes_code = (empty($data['CO_CNES'])) ? null : $data['CO_CNES'];
                        $healthUnit->cnpj_maintainer_code = (empty($data['NU_CNPJ_MANTENEDORA'])) ? null : $data['NU_CNPJ_MANTENEDORA'];
                        $healthUnit->company_name = (empty($data['NO_RAZAO_SOCIAL'])) ? null : $data['NO_RAZAO_SOCIAL'];
                        $healthUnit->alias_company_name = (empty($data['NO_FANTASIA'])) ? null : $data['NO_FANTASIA'];
                        $healthUnit->company_type = (empty($data['CO_NATUREZA_JUR'])) ? null : $data['CO_NATUREZA_JUR'];
                        $healthUnit->ibge_state_id = (empty($data['CO_ESTADO_GESTOR'])) ? null : $data['CO_ESTADO_GESTOR'];
                        $healthUnit->ibge_city_id = (empty($data['CO_MUNICIPIO_GESTOR'])) ? null : $data['CO_MUNICIPIO_GESTOR'];
                        $healthUnit->address = (empty($data['NO_LOGRADOURO'])) ? null : $data['NO_LOGRADOURO'];
                        $healthUnit->address_number = (empty($data['NU_ENDERECO'])) ? null : $data['NU_ENDERECO'];
                        $healthUnit->address_complement = (empty($data['NO_COMPLEMENTO'])) ? null : $data['NO_COMPLEMENTO'];
                        $healthUnit->neighborhood = (empty($data['NO_BAIRRO'])) ? null : $data['NO_BAIRRO'];
                        $healthUnit->cep_code = (empty($data['CO_CEP'])) ? null : $data['CO_CEP'];
                        $healthUnit->latitude = ($this->checkNull($data['NU_LATITUDE'])) ? null : str_replace(',', '.', $data['NU_LATITUDE']);
                        $healthUnit->longitude = ($this->checkNull($data['NU_LONGITUDE'])) ? null : str_replace(',', '.', $data['NU_LONGITUDE']);
                        $healthUnit->save();
                    }
                }
            }
        }
    }
}
