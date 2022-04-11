<?php

namespace App\Models\Datasus\Sim;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Dataset;
use Throwable;
use DateTime;

class SimDatasus extends Dataset
{
    use HasFactory;

    public $sexo = [
        'Masculino' => [['\'1\'', '\'M\'']],
        'Feminino' => [['\'2\'', '\'F\'']],
        'Não consta' => [[null, '\'I\'']],
    ];

    public $keys = ['contador'];
    public $col_date_dataset = 'dtobito';
    public $col_date_dataset_format = 'dmY';
    public $format_date = 'dmY';
    public $prefix = 'do';
    public $alias = 'Declaração de óbito';

    protected $table = 'datasets';

    public function getSerie(Request $request, $id)
    {
        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        if ($request->has('per') && $request->has('operation')) {
            $per = $request->get('per');



            if ($per == 'dtobito') {
                return $this->getSerieByDate($request, $id);
            } elseif ($per == 'codestab') {
                return $this->getSerieCnes($request, $id);
            } elseif ($per == 'causabas') {
                return $this->getSerieCids($request, $id);
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
