<?php

namespace App\Models\Datasus\Sinasc;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Dataset;

class SinascDatasus extends Dataset
{
    use HasFactory;

    public $idademae = [
        '< 10' => [null, 9],
        '10-14' => [10, 14],
        '15-20' => [15, 20],
        '21-30' => [21, 30],
        '31-40' => [31, 40],
        '41-50' => [41, 50],
        '51-55' => [51, 55],
        '56-60' => [56, 60],
        '61 e+' => [61, null],
    ];

    public $tipo_gravidez = [
        'Única' => ['\'1\''],
        'Dupla' => ['\'2\''],
        'Tripla e+' => ['\'3\''],
    ];

    public $tipo_parto = [
        'Vaginal' => ['\'1\''],
        'Cesário' => ['\'2\''],
        'Não informado' => [[
            null, '\'9\''
        ]],
    ];

    public $peso = [
        '0g a 999g' => [null, 999],
        '1000g a 1499g' => [1000, 1499],
        '1500g a 2499g' => [1500, 2499],
        '2500g a 2999g' => [2500, 2999],
        '3000g a 3999g' => [3000, 3999],
        '4000g e mais' => [4000, null]
    ];

    public $estado_civil = [
        'Não informado' => [null],
        'Solteiro' => ['\'1\''],
        'Casado' => ['\'2\''],
        'Viúvo' => ['\'3\''],
        'Separado judicialmente' => ['\'4\''],
        'Únião estável' => ['\'5\''],
        'Ignorado' => ['\'9\''],
    ];

    public $sexo = [
        'Masculino' => [['\'1\'', '\'M\'']],
        'Feminino' => [['\'2\'', '\'F\'']],
        'Não consta' => [[null, '\'I\'']],
    ];


    public $keys = ['contador'];
    public $col_date_dataset = 'dtnasc';
    public $col_date_dataset_format = 'dmY';
    public $format_date = 'dmY';
    public $prefix = 'dn';
    public $alias = 'Declaração de Nascido Vivo';

    protected $table = 'datasets';

    public function getSerie(Request $request, $id)
    {
        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        if ($request->has('per') && $request->has('operation')) {
            $per = $request->get('per');

            if ($per == 'dtnasc') {
                return $this->getSerieByDate($request, $id);
            } elseif ($per == 'codestab') {
                return $this->getSerieCnes($request, $id);
            } elseif ($per == 'codocupmae') {
                return $this->getSerieCbo($request, $id);
            } elseif ($request->has('by_location_type')) {
                return $this->getSerieByLocationType($request, $id);
            } else {
                return $this->getSeriePer($request, $id);
            }
        }
    }

    public function getRange(Request $request, $id)
    {
        return $this->getSerieRange($request, $id);
    }
}
