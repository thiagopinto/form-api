<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use stdClass;
use DateTime;
use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Throwable;

class Dataset extends Model
{
    use HasFactory;

    public $keys = [];
    public $col_date_dataset = '';
    public $col_date_dataset_format = '';
    public $format_date = '';
    public $prefix = '';
    public $alias = '';

    protected $fillable = [
        'year',
        'source',
        'system',
        'initial',
        'alias',
        'file_name',
        'color',
        'user_id',
    ];

    public $omniRetroMetro = [
        '#ea5545',
        '#f46a9b',
        '#ef9b20',
        '#edbf33',
        '#ede15b',
        '#bdcf32',
        '#87bc45',
        '#27aeef',
        '#b33dc6',
    ];

    public $omniDutchField = [
        '#e60049',
        '#0bb4ff',
        '#50e991',
        '#e6d800',
        '#9b19f5',
        '#ffa300',
        '#dc0ab4',
        '#b3d4ff',
        '#00bfa0',
    ];

    public $omniRiverNights = [
        '#b30000',
        '#7c1158',
        '#4421af',
        '#1a53ff',
        '#0d88e6',
        '#00b7c7',
        '#5ad45a',
        '#8be04e',
        '#ebdc78',
    ];

    public $omniSpringPastels = [
        '#fd7f6f',
        '#7eb0d5',
        '#b2e061',
        '#bd7ebe',
        '#ffb55a',
        '#ffee65',
        '#beb9db',
        '#fdcce5',
        '#8bd3c7',
    ];

    public static $months = [
        '01' => 'Janeiro',
        '02' => 'Fevereiro',
        '03' => 'Março',
        '04' => 'Abril',
        '05' => 'Maio',
        '06' => 'Junho',
        '07' => 'Julho',
        '08' => 'Agosto',
        '09' => 'Setembro',
        '10' => 'Outubro',
        '11' => 'Novembro',
        '12' => 'Dezembro',
    ];

    public static $abbreviated_months = [
        '01' => 'Jan',
        '02' => 'Fev',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'Mai',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Set',
        '10' => 'Out',
        '11' => 'Nov',
        '12' => 'Dez',
    ];

    public static $days_week = [
        'Domingo',
        'Segunda-Feira',
        'Terça-Feira',
        'Quarta-Feira',
        'Quinta-Feira',
        'Sexta-Feira',
        'Sábado',
    ];

    public static $short_days_week = [
        'Domingo',
        'Segunda',
        'Terça',
        'Quarta',
        'Quinta',
        'Sexta',
        'Sábado',
    ];

    public static $abbreviation_days_week = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

    public $fieldNull = ['', 'SN', 'null', 'NULL', 'NU', 'nu', 'S/N', 'D', 'sn', 's', 'N', 'S N'];

    public static $keyStrings = [
        'NU_NUMERO',
        'NU_TELEFON',
        'ID_OCUPA_N',
        'ID_DIGIT',
        'ID_EMAIL',
        'ID_FAX',
        'CODINST',
        'CODIFICADO',
        'NUMRES',
        'NUMENDOCOR',
        'NU_ESTAB',
        'NUM_LOGRAD',
        'NU_CPF',
        'ID_AGRAVO',
        'IDENT_MICR',
        'CODANOMAL',
        'CODOCUPMAE',
        'COMPLRES',
        'CONTATO',
        'CONFPESO',
        'CONFIDADE',
        'CONFCAUSA',
        'CONFCIDADE',
        'COMPLOCOR',
        'COMPLACID',
        'COMPARA_CB',
        'COMPLNASC',
        'COREN',
        'NUDOCRESP',
        'NUMENDNASC'
    ];

    public static $keyBigInteger = [
        'AIH',
        'N_AIH',
        'ID_CNS_SUS',
        'CO_UNI_NOT',
        'NU_NOTIFIC',
        'NU_CGC_UNI',
        'NUMSUS',
        'CODENDRES',
        'COD_UNID',
        'NUM_REGIST',
        'CODESTAB',
        'CNES',
        'NUMREGCART',
        'NUMSUSMAE'
    ];

    public static $keyInteger = [
        'CLASSI_FIN',
        'PESO',
        'MUNIC_RES',
        'MUNIC_MOV',
        'CODBAIRES',
        'CODBAIOCOR',
        'IDADE'
    ];

    public static $keyDouble = ['ID_GEO1', 'ID_GEO2'];
    public static $keyPrefixInteger = ['NU', 'ID', 'CO'];
    public static $keyPrefixDate = ['DT'];

    public $faixa_etaria1 = [
        '0 < 01' => [null, 1],
        '01, 04' => [1, 4],
        '05, 09' => [5, 9],
        '10, 14' => [10, 14],
        '15, 19' => [15, 19],
        '20, 24' => [20, 24],
        '25, 29' => [25, 29],
        '30, 34' => [30, 34],
        '35, 39' => [35, 39],
        '40, 44' => [40, 44],
        '45, 49' => [45, 49],
        '50, 54' => [50, 54],
        '55, 59' => [55, 59],
        '60, 64' => [60, 64],
        '65, 69' => [65, 69],
        '70, 74' => [70, 74],
        '75, 79' => [75, 79],
        '80 e+' => [80, null],
    ];

    public $faixa_etaria2 = [
        '0 < 01' => [null, 1],
        '01, 04' => [1, 4],
        '05, 09' => [5, 9],
        '10, 14' => [10, 14],
        '15, 19' => [15, 19],
        '20, 29' => [20, 29],
        '30, 39' => [30, 39],
        '40, 49' => [40, 49],
        '50, 59' => [50, 59],
        '60, 69' => [60, 69],
        '70, 79' => [70, 79],
        '80 e+' => [80, null],
    ];

    public $faixa_etaria_pd = [
        '0 < 01' => [001, 400],
        '01, 04' => [401, 404],
        '05, 09' => [405, 409],
        '10, 14' => [410, 414],
        '15, 19' => [415, 419],
        '20, 29' => [420, 429],
        '30, 39' => [430, 439],
        '40, 49' => [440, 449],
        '50, 59' => [450, 459],
        '60, 69' => [460, 469],
        '70, 79' => [470, 479],
        '80 e+' => [480, 599],
    ];

    public $faixa_etaria_pd5 = [
        'Ignorado' => [000, 999],
        '00, 04' => [001, 404],
        '05, 09' => [405, 409],
        '10, 14' => [410, 414],
        '15, 19' => [415, 419],
        '20, 24' => [420, 424],
        '25, 29' => [425, 429],
        '30, 34' => [430, 434],
        '35, 39' => [435, 439],
        '40, 44' => [440, 444],
        '45, 49' => [445, 449],
        '50, 54' => [450, 454],
        '55, 59' => [455, 459],
        '60, 64' => [460, 464],
        '65, 69' => [465, 469],
        '70, 74' => [470, 474],
        '75, 79' => [475, 479],
        '80 e+' => [480, 599],
    ];

    public $faixa_etaria_detalhada_short = [
        '< 1 hora' => [001, 100],
        '1 hora' => [101, 101],
        '2 hora' => [102, 102],
        '3 hora' => [103, 103],
        '4 hora' => [104, 104],
        '5 hora' => [105, 105],
        '6 hora' => [106, 106],
        '7 hora' => [107, 107],
        '8 hora' => [108, 108],
        '9 hora' => [109, 109],
        '10 hora' => [110, 110],
        '11 hora' => [111, 111],
        '12 hora' => [112, 112],
        '13 hora' => [113, 113],
        '14 hora' => [114, 114],
        '15 hora' => [115, 115],
        '16 hora' => [116, 116],
        '17 hora' => [117, 117],
        '18 hora' => [118, 118],
        '19 hora' => [119, 119],
        '20 hora' => [120, 120],
        '21 hora' => [121, 121],
        '22 hora' => [122, 122],
        '23 hora' => [123, 123],
        '< 1 dia, horas ign' => [200, 200],
        '1 dia' => [130, 130],
        '1 dia' => [201, 201],
        '2 dia' => [202, 202],
        '3 dia' => [203, 203],
        '4 dia' => [204, 204],
        '5 dia' => [205, 205],
        '6 dia' => [206, 206],
        '7 dia' => [207, 207],
        '8 dia' => [208, 208],
        '9 dia' => [209, 209],
        '10 dia' => [210, 210],
        '11 dia' => [211, 211],
        '12 dia' => [212, 212],
        '13 dia' => [213, 213],
        '14 dia' => [214, 214],
        '15 dia' => [215, 215],
        '16 dia' => [216, 216],
        '17 dia' => [217, 217],
        '18 dia' => [218, 218],
        '19 dia' => [219, 219],
        '20 dia' => [220, 220],
        '21 dia' => [221, 221],
        '22 dia' => [222, 222],
        '23 dia' => [223, 223],
        '24 dia' => [224, 224],
        '25 dia' => [225, 225],
        '26 dia' => [226, 226],
        '27 dia' => [227, 227],
        '28 dia' => [228, 228],
        '29 dia' => [229, 229],
        '< 1 mes, dias ign' => [300, 300],
        '1 mes' => [230, 230],
        '1 mes' => [301, 301],
        '2 meses' => [302, 302],
        '3 meses' => [303, 303],
        '4 meses' => [304, 304],
        '5 meses' => [305, 305],
        '6 meses' => [306, 306],
        '7 meses' => [307, 307],
        '8 meses' => [308, 308],
        '9 meses' => [309, 309],
        '10 meses' => [310, 310],
        '11 meses' => [311, 311],
        'menor de 1 ano ign' => [400, 400],
        '1 ano' => [401, 401],
        '2 ano' => [402, 402],
        '3 ano' => [403, 403],
        '4 ano' => [404, 404],
        '5 ano' => [405, 405],
        '6 anos' => [406, 406],
        '7 anos' => [407, 407],
        '8 anos' => [408, 408],
        '9 anos' => [409, 409],
        '10 anos' => [410, 410],
        '11 anos' => [411, 411],
        '12 anos' => [412, 412],
        '13 anos' => [413, 413],
        '14 anos' => [414, 414],
        '15 anos' => [415, 415],
        '16 anos' => [416, 416],
        '17 anos' => [417, 417],
        '18 anos' => [418, 418],
        '19 anos' => [419, 419],
        '20 anos' => [420, 420],
        '21 anos' => [421, 421],
        '22 anos' => [422, 422],
        '23 anos' => [423, 423],
        '24 anos' => [424, 424],
        '25 anos' => [425, 425],
        '26 anos' => [426, 426],
        '27 anos' => [427, 427],
        '28 anos' => [428, 428],
        '29 anos' => [429, 429],
        '30 anos' => [430, 430],
        '31 anos' => [431, 431],
        '32 anos' => [432, 432],
        '33 anos' => [433, 433],
        '34 anos' => [434, 434],
        '35 anos' => [435, 435],
        '36 anos' => [436, 436],
        '37 anos' => [437, 437],
        '38 anos' => [438, 438],
        '39 anos' => [439, 439],
        '40 anos' => [440, 440],
        '41 anos' => [441, 441],
        '42 anos' => [442, 442],
        '43 anos' => [443, 443],
        '44 anos' => [444, 444],
        '45 anos' => [445, 445],
        '46 anos' => [446, 446],
        '47 anos' => [447, 447],
        '48 anos' => [448, 448],
        '49 anos' => [449, 449],
        '50 anos' => [450, 450],
        '51 anos' => [451, 451],
        '52 anos' => [452, 452],
        '53 anos' => [453, 453],
        '54 anos' => [454, 454],
        '55 anos' => [455, 455],
        '56 anos' => [456, 456],
        '57 anos' => [457, 457],
        '58 anos' => [458, 458],
        '59 anos' => [459, 459],
        '60 anos' => [460, 460],
        '61 anos' => [461, 461],
        '62 anos' => [462, 462],
        '63 anos' => [463, 463],
        '64 anos' => [464, 464],
        '65 anos' => [465, 465],
        '66 anos' => [466, 466],
        '67 anos' => [467, 467],
        '68 anos' => [468, 468],
        '69 anos' => [469, 469],
        '70 anos' => [470, 470],
        '71 anos' => [471, 471],
        '72 anos' => [472, 472],
        '73 anos' => [473, 473],
        '74 anos' => [474, 474],
        '75 anos' => [475, 475],
        '76 anos' => [476, 476],
        '77 anos' => [477, 477],
        '78 anos' => [478, 478],
        '79 anos' => [479, 479],
        '80 anos' => [480, 480],
        '81 anos' => [481, 481],
        '82 anos' => [482, 482],
        '83 anos' => [483, 483],
        '84 anos' => [484, 484],
        '85 anos' => [485, 485],
        '86 anos' => [486, 486],
        '87 anos' => [487, 487],
        '88 anos' => [488, 488],
        '89 anos' => [489, 489],
        '90 anos' => [490, 490],
        '91 anos' => [491, 491],
        '92 anos' => [492, 492],
        '93 anos' => [493, 493],
        '94 anos' => [494, 494],
        '95 anos' => [495, 495],
        '96 anos' => [496, 496],
        '97 anos' => [497, 497],
        '98 anos' => [498, 498],
        '99 anos' => [499, 499],
        '100 anos' => [500, 500],
        '101 anos' => [501, 501],
        '102 anos' => [502, 502],
        '103 anos' => [503, 503],
        '104 anos' => [504, 504],
        '105 anos' => [505, 505],
        '106 anos' => [506, 506],
        '107 anos' => [507, 507],
        '108 anos' => [508, 508],
        '109 anos' => [509, 509],
        '110 anos' => [510, 510],
        '111 anos' => [511, 511],
        '112 anos' => [512, 512],
        '113 anos' => [513, 513],
        '114 anos' => [514, 514],
        '115 anos' => [515, 515],
        '116 anos' => [516, 516],
        '117 anos' => [517, 517],
        '118 anos' => [518, 518],
        '119 anos' => [519, 519],
        '120 anos' => [520, 520],
    ];

    public $faixa_etaria_detalhada = [
        '< 1 hora' => [001, 100],
        '1 hora' => [101, 101],
        '2 hora' => [102, 102],
        '3 hora' => [103, 103],
        '4 hora' => [104, 104],
        '5 hora' => [105, 105],
        '6 hora' => [106, 106],
        '7 hora' => [107, 107],
        '8 hora' => [108, 108],
        '9 hora' => [109, 109],
        '10 hora' => [110, 110],
        '11 hora' => [111, 111],
        '12 hora' => [112, 112],
        '13 hora' => [113, 113],
        '14 hora' => [114, 114],
        '15 hora' => [115, 115],
        '16 hora' => [116, 116],
        '17 hora' => [117, 117],
        '18 hora' => [118, 118],
        '19 hora' => [119, 119],
        '20 hora' => [120, 120],
        '21 hora' => [121, 121],
        '22 hora' => [122, 122],
        '23 hora' => [123, 123],
        '< 1 dia, horas ign' => [200, 200],
        '1 dia' => [130, 130],
        '1 dia' => [201, 201],
        '2 dia' => [202, 202],
        '3 dia' => [203, 203],
        '4 dia' => [204, 204],
        '5 dia' => [205, 205],
        '6 dia' => [206, 206],
        '7 dia' => [207, 207],
        '8 dia' => [208, 208],
        '9 dia' => [209, 209],
        '10 dia' => [210, 210],
        '11 dia' => [211, 211],
        '12 dia' => [212, 212],
        '13 dia' => [213, 213],
        '14 dia' => [214, 214],
        '15 dia' => [215, 215],
        '16 dia' => [216, 216],
        '17 dia' => [217, 217],
        '18 dia' => [218, 218],
        '19 dia' => [219, 219],
        '20 dia' => [220, 220],
        '21 dia' => [221, 221],
        '22 dia' => [222, 222],
        '23 dia' => [223, 223],
        '24 dia' => [224, 224],
        '25 dia' => [225, 225],
        '26 dia' => [226, 226],
        '27 dia' => [227, 227],
        '28 dia' => [228, 228],
        '29 dia' => [229, 229],
        '< 1 mes, dias ign' => [300, 300],
        '1 mes' => [230, 230],
        '1 mes' => [301, 301],
        '2 meses' => [302, 302],
        '3 meses' => [303, 303],
        '4 meses' => [304, 304],
        '5 meses' => [305, 305],
        '6 meses' => [306, 306],
        '7 meses' => [307, 307],
        '8 meses' => [308, 308],
        '9 meses' => [309, 309],
        '10 meses' => [310, 310],
        '11 meses' => [311, 311],
        'menor de 1 ano ign' => [400, 400],
        '1 ano' => [401, 401],
        '2 ano' => [402, 402],
        '3 ano' => [403, 403],
        '4 ano' => [404, 404],
        '5 ano' => [405, 405],
        '6 anos' => [406, 406],
        '7 anos' => [407, 407],
        '8 anos' => [408, 408],
        '9 anos' => [409, 409],
        '10 anos' => [410, 410],
        '11 anos' => [411, 411],
        '12 anos' => [412, 412],
        '13 anos' => [413, 413],
        '14 anos' => [414, 414],
        '15 anos' => [415, 415],
        '16 anos' => [416, 416],
        '17 anos' => [417, 417],
        '18 anos' => [418, 418],
        '19 anos' => [419, 419],
        '20 anos' => [420, 420],
        '21 anos' => [421, 421],
        '22 anos' => [422, 422],
        '23 anos' => [423, 423],
        '24 anos' => [424, 424],
        '25 anos' => [425, 425],
        '26 anos' => [426, 426],
        '27 anos' => [427, 427],
        '28 anos' => [428, 428],
        '29 anos' => [429, 429],
        '30 anos' => [430, 430],
        '31 anos' => [431, 431],
        '32 anos' => [432, 432],
        '33 anos' => [433, 433],
        '34 anos' => [434, 434],
        '35 anos' => [435, 435],
        '36 anos' => [436, 436],
        '37 anos' => [437, 437],
        '38 anos' => [438, 438],
        '39 anos' => [439, 439],
        '40 anos' => [440, 440],
        '41 anos' => [441, 441],
        '42 anos' => [442, 442],
        '43 anos' => [443, 443],
        '44 anos' => [444, 444],
        '45 anos' => [445, 445],
        '46 anos' => [446, 446],
        '47 anos' => [447, 447],
        '48 anos' => [448, 448],
        '49 anos' => [449, 449],
        '50 anos' => [450, 450],
        '51 anos' => [451, 451],
        '52 anos' => [452, 452],
        '53 anos' => [453, 453],
        '54 anos' => [454, 454],
        '55 anos' => [455, 455],
        '56 anos' => [456, 456],
        '57 anos' => [457, 457],
        '58 anos' => [458, 458],
        '59 anos' => [459, 459],
        '60 anos' => [460, 460],
        '61 anos' => [461, 461],
        '62 anos' => [462, 462],
        '63 anos' => [463, 463],
        '64 anos' => [464, 464],
        '65 anos' => [465, 465],
        '66 anos' => [466, 466],
        '67 anos' => [467, 467],
        '68 anos' => [468, 468],
        '69 anos' => [469, 469],
        '70 anos' => [470, 470],
        '71 anos' => [471, 471],
        '72 anos' => [472, 472],
        '73 anos' => [473, 473],
        '74 anos' => [474, 474],
        '75 anos' => [475, 475],
        '76 anos' => [476, 476],
        '77 anos' => [477, 477],
        '78 anos' => [478, 478],
        '79 anos' => [479, 479],
        '80 anos' => [480, 480],
        '81 anos' => [481, 481],
        '82 anos' => [482, 482],
        '83 anos' => [483, 483],
        '84 anos' => [484, 484],
        '85 anos' => [485, 485],
        '86 anos' => [486, 486],
        '87 anos' => [487, 487],
        '88 anos' => [488, 488],
        '89 anos' => [489, 489],
        '90 anos' => [490, 490],
        '91 anos' => [491, 491],
        '92 anos' => [492, 492],
        '93 anos' => [493, 493],
        '94 anos' => [494, 494],
        '95 anos' => [495, 495],
        '96 anos' => [496, 496],
        '97 anos' => [497, 497],
        '98 anos' => [498, 498],
        '99 anos' => [499, 499],
        '100 anos' => [500, 500],
        '101 anos' => [501, 501],
        '102 anos' => [502, 502],
        '103 anos' => [503, 503],
        '104 anos' => [504, 504],
        '105 anos' => [505, 505],
        '106 anos' => [506, 506],
        '107 anos' => [507, 507],
        '108 anos' => [508, 508],
        '109 anos' => [509, 509],
        '110 anos' => [510, 510],
        '111 anos' => [511, 511],
        '112 anos' => [512, 512],
        '113 anos' => [513, 513],
        '114 anos' => [514, 514],
        '115 anos' => [515, 515],
        '116 anos' => [516, 516],
        '117 anos' => [517, 517],
        '118 anos' => [518, 518],
        '119 anos' => [519, 519],
        '120 anos' => [520, 520],
        '121 anos' => [521, 521],
        '122 anos' => [522, 522],
        '123 anos' => [523, 523],
        '124 anos' => [524, 524],
        '125 anos' => [525, 525],
        '126 anos' => [526, 526],
        '127 anos' => [527, 527],
        '128 anos' => [528, 528],
        '129 anos' => [529, 529],
        '130 anos' => [530, 530],
        '131 anos' => [531, 531],
        '132 anos' => [532, 532],
        '133 anos' => [533, 533],
        '134 anos' => [534, 534],
        '135 anos' => [535, 535],
        '136 anos' => [536, 536],
        '137 anos' => [537, 537],
        '138 anos' => [538, 538],
        '139 anos' => [539, 539],
        '140 anos' => [540, 540],
        '141 anos' => [541, 541],
        '142 anos' => [542, 542],
        '143 anos' => [543, 543],
        '144 anos' => [544, 544],
        '145 anos' => [545, 545],
        '146 anos' => [546, 546],
        '147 anos' => [547, 547],
        '148 anos' => [548, 548],
        '149 anos' => [549, 549],
        '150 anos' => [550, 550],
        '151 anos' => [551, 551],
        '152 anos' => [552, 552],
        '153 anos' => [553, 553],
        '154 anos' => [554, 554],
        '155 anos' => [555, 555],
        '156 anos' => [556, 556],
        '157 anos' => [557, 557],
        '158 anos' => [558, 558],
        '159 anos' => [559, 559],
        '160 anos' => [560, 560],
        '161 anos' => [561, 561],
        '162 anos' => [562, 562],
        '163 anos' => [563, 563],
        '164 anos' => [564, 564],
        '165 anos' => [565, 565],
        '166 anos' => [566, 566],
        '167 anos' => [567, 567],
        '168 anos' => [568, 568],
        '169 anos' => [569, 569],
        '170 anos' => [570, 570],
        '171 anos' => [571, 571],
        '172 anos' => [572, 572],
        '173 anos' => [573, 573],
        '174 anos' => [574, 574],
        '175 anos' => [575, 575],
        '176 anos' => [576, 576],
        '177 anos' => [577, 577],
        '178 anos' => [578, 578],
        '179 anos' => [579, 579],
        '180 anos' => [580, 580],
        '181 anos' => [581, 581],
        '182 anos' => [582, 582],
        '183 anos' => [583, 583],
        '184 anos' => [584, 584],
        '185 anos' => [585, 585],
        '186 anos' => [586, 586],
        '187 anos' => [587, 587],
        '188 anos' => [588, 588],
        '189 anos' => [589, 589],
        '190 anos' => [590, 590],
        '191 anos' => [591, 591],
        '192 anos' => [592, 592],
        '193 anos' => [593, 593],
        '194 anos' => [594, 594],
        '195 anos' => [595, 595],
        '196 anos' => [596, 596],
        '197 anos' => [597, 597],
        '198 anos' => [598, 598],
        '199 anos' => [599, 599],
    ];

    public $faixa_etaria_inf1 = [
        '< 7d' => [001, 206],
        '07-27' => [207, 227],
        '28d-<1m' => [228, 311],
    ];

    public $faixa_etaria_inf2 = [
        '< 1H' => [001, 123],
        '01-06d' => [200, 206],
        '07-27d' => [207, 227],
        '28d-<1m' => [228, 311],
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

    public function formatDate($date)
    {
        try {
            /*
             *   Formatos
             * --------------------------------
             * yyyy 4 char
             * ddmmyyyy 8 char
             * dd/mm/yy 8 char
             * dd-mm-yy 8 char
             * dd/mm/yyyy 10 char
             * yyyy/mm/dd 10 char
             * dd-mm-yyyy 10 char
             * yyyy-mm-dd 10 char
             *
             */

            $pos = strpos($this->format_date, 'BR');

            if (strpos($this->format_date, 'BR') === false) {
                $oldDate = $date;
                try {
                    if ($date != null) {
                        $date = DateTime::createFromFormat(
                            $this->format_date,
                            $date
                        );
                        $date = $date->format('Y-m-d');
                        return $date;
                    } else {
                        return null;
                    }
                } catch (Throwable $e) {
                    throw $e;
                    return false;
                }
            } else {
                $stringFormat = str_replace('BR', '', $this->format_date);

                foreach (self::$abbreviated_months as $key => $value) {
                    if (strpos($date, $value) === false) {
                        continue;
                    } else {
                        $stringFormat = str_replace($value, $key, $date);
                        $date = DateTime::createFromFormat(
                            $stringFormat,
                            $date
                        );
                        $date = $date->format('Y-m-d');
                        return $date;
                    }
                }
            }

            $countDate = strlen($date);

            if ($countDate == 4) {
                $date = DateTime::createFromFormat('Y', $date);
                $date = $date->format('Y-m-d');
                return $date;
            } elseif ($countDate == 8) {
                if (
                    strpos($date, '/') === false &&
                    strpos($date, '-') === false
                ) {
                    $date = DateTime::createFromFormat('dmY', $date);
                    $date = $date->format('Y-m-d');
                    return $date;
                } elseif (!(strpos($date, '/') === false)) {
                    $date = DateTime::createFromFormat('d/m/y', $date);
                    $date = $date->format('Y-m-d');
                    return $date;
                } elseif (!(strpos($date, '-') === false)) {
                    $date = DateTime::createFromFormat('d-m-y', $date);
                    $date = $date->format('Y-m-d');
                    return $date;
                }
            } elseif ($countDate == 10) {
                if (!(strpos($date, '/') === false)) {
                    $dateArray = explode('/', $date);

                    if (strlen($dateArray[0]) == 4) {
                        $date = DateTime::createFromFormat('Y/m/d', $date);
                        $date = $date->format('Y-m-d');
                        return $date;
                    } elseif (strlen($dateArray[0]) == 2) {
                        $date = DateTime::createFromFormat('d/m/Y', $date);
                        $date = $date->format('Y-m-d');
                        return $date;
                    }
                } elseif (!(strpos($date, '-') === false)) {
                    $dateArray = explode('-', $date);

                    if (strlen($dateArray[0]) == 4) {
                        $date = DateTime::createFromFormat('Y-m-d', $date);
                        $date = $date->format('Y-m-d');
                        return $date;
                    } elseif (strlen($dateArray[0]) == 2) {
                        $date = DateTime::createFromFormat('d-m-Y', $date);
                        $date = $date->format('Y-m-d');
                        return $date;
                    }
                }
            }

            return $date;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function cleanString($text)
    {
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        ];
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }

    //cria tabela dinamicamente
    protected function createTable($tableName, $path, $keyColumns = [])
    {
        $db = dbase_open(storage_path('app/' . $path), 0);

        if ($db) {
            $columns = dbase_get_header_info($db);
            try {
                Schema::create($tableName, function (Blueprint $table) use (
                    $columns,
                    $keyColumns,
                    $tableName
                ) {
                    $laravel_type['memo'] = 'text';
                    $laravel_type['character'] = 'string';
                    $laravel_type['number'] = 'integer';
                    $laravel_type['float'] = 'float';
                    $laravel_type['date'] = 'date';

                    $keyStrings = array_map('strtolower', self::$keyStrings);
                    $keyBigInteger = array_map(
                        'strtolower',
                        self::$keyBigInteger
                    );
                    $keyInteger = array_map('strtolower', self::$keyInteger);
                    $keyDouble = array_map('strtolower', self::$keyDouble);
                    $keyPrefixInteger = array_map(
                        'strtolower',
                        self::$keyPrefixInteger
                    );
                    $keyPrefixDate = array_map(
                        'strtolower',
                        self::$keyPrefixDate
                    );

                    $table->increments('id');

                    foreach ($columns as $column) {
                        $prefix = str_split($column['name'], 2);

                        if (
                            in_array(strtolower($column['name']), $keyStrings)
                        ) {
                            $table
                                ->string(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif (
                            in_array(
                                strtolower($column['name']),
                                $keyBigInteger
                            )
                        ) {
                            $table
                                ->bigInteger(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif (
                            in_array(strtolower($column['name']), $keyInteger)
                        ) {
                            $table
                                ->integer(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif (
                            in_array(strtolower($column['name']), $keyDouble)
                        ) {
                            $table
                                ->double(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif (
                            in_array(strtolower($prefix[0]), $keyPrefixInteger)
                        ) {
                            $table
                                ->integer(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif (
                            in_array(strtolower($prefix[0]), $keyPrefixDate)
                        ) {
                            $table
                                ->date(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif ($laravel_type[$column['type']] == 'string') {
                            $table
                                ->string(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif ($laravel_type[$column['type']] == 'integer') {
                            if ($column['precision'] > 0) {
                                $table
                                    ->float(strtolower($column['name']))
                                    ->nullable();
                            } else {
                                $table
                                    ->integer(strtolower($column['name']))
                                    ->nullable();
                            }
                            continue;
                        } elseif ($laravel_type[$column['type']] == 'text') {
                            $table
                                ->text(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif ($laravel_type[$column['type']] == 'float') {
                            $table
                                ->float(strtolower($column['name']))
                                ->nullable();
                            continue;
                        } elseif ($laravel_type[$column['type']] == 'date') {
                            $table
                                ->date(strtolower($column['name']))
                                ->nullable();
                            continue;
                        }
                    }

                    $table->double('lat')->nullable();
                    $table->double('lng')->nullable();

                    $table->timestamps();
                    if (count($keyColumns) > 0) {
                        $table->unique(
                            $keyColumns,
                            $tableName . '_unique_register'
                        );
                    }
                });

                dbase_close($db);
                return true;
            } catch (Exception $e) {
                dbase_close($db);
                Storage::delete($path);

                throw $e;
                return false;
            }
        }
    }

    protected function changeCurrentTable(
        $year,
        $path,
        $source,
        $system,
        $initial,
        $user,
        $keyColumns
    ) {
        $colors = array_merge(
            $this->omniRetroMetro,
            $this->omniDutchField,
            $this->omniRiverNights,
            $this->omniSpringPastels
        );
        try {
            $tableName = "{$year}_{$initial}_{$system}_{$source}";

            if (!Schema::hasTable($tableName)) {
                $this->createTable($tableName, $path, $keyColumns);
                $isUpdate = false;
            } else {
                $isUpdate = true;
                $db = dbase_open(storage_path('app/' . $path), 0);
                if ($db) {
                    $columns_dbf = dbase_get_header_info($db);
                    $columns_schema = DB::getSchemaBuilder()->getColumnListing(
                        $tableName
                    );

                    // 4 columns extra create, update, lat, lng
                    if (count($columns_dbf) + 4 > count($columns_schema)) {
                        Schema::dropIfExists($tableName);
                        $this->createTable($tableName, $path, $keyColumns);
                        $isUpdate = false;
                    }
                }
            }

            $dataset = Dataset::updateOrCreate(
                [
                    'year' => $year,
                    'source' => $source,
                    'system' => $system,
                    'initial' => $initial,
                ],
                [
                    'alias' => $this->alias,
                    'file_name' => $path,
                    'color' => $colors[rand(0, count($colors) - 1)],
                    'user_id' => $user->id,
                ]
            );

            $data = new stdClass();
            $data->tableName = $tableName;
            $data->year = $year;
            $data->isUpdate = $isUpdate;
            return $data;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function upsert(
        $tableName,
        $dataset,
        $keyColumns,
        $updateColumns,
        $quantity = 200
    ) {
        if (count($dataset) > 1) {
            if ($quantity > 1) {
                $quantity = intdiv($quantity, 2);
            }

            $datas = array_chunk($dataset, $quantity);
        } else {
            $datas = $dataset;
        }

        foreach ($datas as $data) {
            try {
                DB::table($tableName)->upsert(
                    $data,
                    $keyColumns,
                    $updateColumns
                );
            } catch (Throwable $e) {
                if ($quantity == 1) {
                    throw $e;
                    return false;
                }

                $this->upsert(
                    $tableName,
                    $data,
                    $keyColumns,
                    $updateColumns,
                    $quantity
                );
            }
        }
    }

    public function getYear($record)
    {
        if (
            array_key_exists(
                $this->col_date_dataset,
                $record
            )
        ) {
            try {
                $date = DateTime::createFromFormat(
                    $this->col_date_dataset_format,
                    $record[$this->col_date_dataset]
                );
                $year = $date->format('Y');
            } catch (\Throwable $th) {
                var_dump($record);
            }
        } elseif (
            array_key_exists(
                strtolower($this->col_date_dataset),
                $record
            )
        ) {
            $date = DateTime::createFromFormat(
                $this->col_date_dataset_format,
                $record[strtolower($this->col_date_dataset)]
            );
            $year = $date->format('Y');
        } elseif (
            array_key_exists(
                strtoupper($this->col_date_dataset),
                $record
            )
        ) {
            $date = DateTime::createFromFormat(
                $this->col_date_dataset_format,
                $record[strtoupper($this->col_date_dataset)]
            );
            $year = $date->format('Y');
        }

        return $year;
    }

    public function clearKeyAndField($string)
    {
        $string = str_replace("'", "\'", $string);
        $string = trim($string);

        //$string = utf8_encode($string);

        $string = mb_convert_encoding(
            $string,
            'UTF-8',
            'CP850'
        );

        // $string = iconv("CP850", "UTF-8", $string);
        return $string;
    }

    public function formatField($key, $field)
    {
        $prefix = str_split($key, 2);
        $keyPrefixDate = array_map(
            'strtolower',
            self::$keyPrefixDate
        );

        if (empty($field) || in_array($field, $this->fieldNull)) {
            return null;
        }

        if (in_array(strtolower($prefix[0]), $keyPrefixDate)) {
            $field = str_replace(" ", "", $field);
            return $this->formatDate($field);
        } else {
            $keyBigInteger = array_map(
                'strtolower',
                self::$keyBigInteger
            );
            $keyInteger = array_map('strtolower', self::$keyInteger);

            if (
                in_array(
                    strtolower($key),
                    $keyBigInteger
                ) || in_array(
                    strtolower($key),
                    $keyInteger
                )
            ) {
                $field = str_replace(" ", "", $field);
            }

            return $field;
        }

        return $field;
    }

    public function rowHandler($row)
    {
        return $row;
    }

    //carregar dados
    public function loadFileDbf($path, $source, $system, $initial, $user)
    {
        $currentYear = null;

        $db = dbase_open(storage_path('app/' . $path), 0);

        if ($db) {
            $num_rows = dbase_numrecords($db);
            $tablesDataSets = [];

            $columns = dbase_get_header_info($db);
            $keyColumns = $this->keys;

            foreach ($columns as $index => $column) {
                $column['name'] = strtolower($column['name']);
                $column['name']  = $this->clearKeyAndField($column['name']);

                foreach ($keyColumns as $key) {
                    if ($column['name'] == $key) {
                        unset($columns[$index]);
                        break;
                    }
                }
            }

            foreach ($columns as $column) {
                $updateColumns[] = strtolower($column['name']);
            }

            try {
                # Loop the Dbase records
                for ($index = 1; $index <= $num_rows; $index++) {
                    # Get one record
                    $record = dbase_get_record_with_names($db, $index);
                    # Ignore deleted fields
                    if ($record['deleted'] != '1') {
                        $record = array_change_key_case($record, CASE_LOWER);

                        $year = $this->getYear($record);

                        if ($year != $currentYear) {
                            $table = $this->changeCurrentTable(
                                $year,
                                $path,
                                $source,
                                $system,
                                $initial,
                                $user,
                                $keyColumns
                            );
                            $currentYear = $year;
                        }

                        foreach ($record as $key => $field) {
                            if ($key !== 'deleted') {
                                try {
                                    $field = $this->clearKeyAndField($field);
                                    $key = $this->clearKeyAndField($key);

                                    $data[$key] = $this->formatField($key, $field);
                                } catch (Throwable $e) {
                                    echo $e->getMessage();
                                    throw $e;
                                }
                            }
                        }

                        $data = $this->rowHandler($data);

                        $data['created_at'] = date('Y-m-d H:i:s');
                        $data['updated_at'] = date('Y-m-d H:i:s');

                        $tablesDataSets[$table->tableName][] = $data;
                    }
                }

                foreach ($tablesDataSets as $tableName => $dataset) {
                    $this->upsert(
                        $tableName,
                        $dataset,
                        $keyColumns,
                        $updateColumns
                    );
                }
                dbase_close($db);
                // DB::commit();
            } catch (Throwable $e) {
                dbase_close($db);
                throw $e;
                return false;
            }
        }
        return true;
    }

    public function loadFile(
        $request,
        $path,
        $source,
        $system,
        $initial,
        $extension,
        $user
    ) {
        if (strcmp($extension, 'dbf') == 0) {
            return $this->loadFileDbf($path, $source, $system, $initial, $user);
        } else {
            throw new Exception("Unsupported file ${$extension}");
            return false;
        }
    }

    public function getTotal(Request $request, $source, $system, $initial, $id)
    {
        $dataset = DataSet::find($id);

        $year = $dataset->year;
        $initial = $dataset->initial;
        $source = $dataset->source;

        return DB::table("{$year}_{$initial}_{$system}_{$source}")
            ->where(function ($query) use ($request) {
                return $this->createWhere($query, $request);
            })
            ->count();
    }

    public static function getClass($source, $system, $initial)
    {
        $class = 'App\Models\\';
        $class .= ucfirst($source) . '\\';
        $class .= ucfirst($system) . '\\';
        $class .= ucfirst($initial) . '\\';
        $class .= ucfirst($initial);
        $class .= ucfirst($system);
        $class .= ucfirst($source);

        return $class;
    }

    public function filterTerm($query, $request)
    {
        $columnFilter = $request->get('column_filter');
        $termFilter = $request->get('term_filter');
        if ($request->has('operator_filter')) {
            $operatorFilter = $request->get('operator_filter');
            if (count($columnFilter) == count($termFilter)) {
                for ($i = 0; $i < count($columnFilter); $i++) {
                    $query->where(
                        $columnFilter[$i],
                        $operatorFilter[$i],
                        $termFilter[$i]
                    );
                }
                return $query;
            }
        } else {
            if (count($columnFilter) == count($termFilter)) {
                for ($i = 0; $i < count($columnFilter); $i++) {
                    $query->where($columnFilter[$i], $termFilter[$i]);
                }
                return $query;
            }
        }
    }

    public function filterTermsIn($query, $request)
    {
        $columnFilterIn = $request->get('column_filter_in');
        $termFiltersIn = $request->get('term_filters_in');
        return $query->whereIn($columnFilterIn, $termFiltersIn);
    }

    public function filterTermOr($query, $request)
    {
        $columnFilterOr = $request->get('column_filter_or');
        $termFilterOr = $request->get('term_filter_or');
        if (count($columnFilterOr) == count($termFilterOr)) {
            $query->where(
                function ($query) use ($columnFilterOr, $termFilterOr) {
                    for ($i = 0; $i < count($columnFilterOr); $i++) {
                        $query->orWhere($columnFilterOr[$i], $termFilterOr[$i]);
                    }
                }
            );
            return $query;
        }
    }

    public function filterTermOrRange($query, $request)
    {
        $columnFilterOrRange = $request->get('column_filter_or_range');
        $termFilterOrRange = $request->get('term_filter_or_range');

        $query->where(
            function ($query) use ($request, $columnFilterOrRange, $termFilterOrRange) {
                $query->when(
                    $request->has('column_filters') &&
                    $request->has('term_filters'),
                    function ($query) use ($request) {
                        $column_filter = $request->get('column_filters');
                        $term_filters = $request->get('term_filters');
                        return $query->orWhere(function ($query) use (
                            $column_filter,
                            $term_filters
                        ) {
                            return $query->whereIn(
                                $column_filter,
                                $term_filters
                            );
                        });
                    }
                );
                if (count($columnFilterOrRange) == count($termFilterOrRange)) {
                    for ($i = 0; $i < count($columnFilterOrRange); $i++) {
                        $query->orWhere(
                            function ($query) use ($columnFilterOrRange, $termFilterOrRange, $i) {
                                $query->whereBetween(
                                    $columnFilterOrRange[$i],
                                    json_decode($termFilterOrRange[$i], true)
                                );
                            }
                        );
                    }
                } elseif (count($columnFilterOrRange) == 1 && count($termFilterOrRange) > count($columnFilterOrRange)) {
                    for ($i = 0; $i < count($termFilterOrRange); $i++) {
                        $query->orWhere(
                            function ($query) use ($columnFilterOrRange, $termFilterOrRange, $i) {
                                $query->whereBetween(
                                    $columnFilterOrRange[0],
                                    json_decode($termFilterOrRange[$i], true)
                                );
                            }
                        );
                    }
                }

                return $query;
            }
        );

        return $query;
    }

    public function filterBetween($query, $request)
    {
        $columnFilterBetween = $request->get('column_filter_between');
        $termsFilterBetween = $request->get('terms_filter_between');
        return $query->whereBetween($columnFilterBetween, $termsFilterBetween);
    }

    public function filterNotBetween($query, $request)
    {
        $columnFilterNotBetween = $request->get('column_filter_not_between');
        $termsFilterNotBetween = $request->get('terms_filter_not_between');
        return $query->whereNotBetween($columnFilterNotBetween, $termsFilterNotBetween);
    }

    public function createWhere($query, $request, $per = 'id')
    {
        return $query->when(
            $request->has('column_filter') && $request->has('term_filter'),
            function ($query) use ($request) {
                return $this->filterTerm($query, $request);
            }
        )
        ->when(
            $request->has('column_filter_or') &&
                $request->has('term_filter_or'),
            function ($query) use ($request) {
                return $this->filterTermOr($query, $request);
            }
        )
        ->when(
            $request->has('column_filter_or_range') &&
                $request->has('term_filter_or_range'),
            function ($query) use ($request) {
                return $this->filterTermOrRange($query, $request);
            }
        )
        ->when(
            $request->has('column_filter_in') &&
                $request->has('term_filters_in'),
            function ($query) use ($request) {
                return $this->filterTermsIn($query, $request);
            }
        )
        ->when(
            $request->has('column_is_null'),
            function ($query) use ($request) {
                $columnIsNull = $request->get('column_is_null');
                return $query->whereNull($columnIsNull);
            }
        )
        ->when(
            $request->has('column_is_not_null'),
            function ($query) use ($request) {
                $columnIsNotNull = $request->get('column_is_not_null');
                return $query->whereNotNull($columnIsNotNull);
            }
        )
        ->when(
            $request->has('column_filter_between') &&
                $request->has('terms_filter_between'),
            function ($query) use ($request) {
                return $this->filterBetween($query, $request);
            }
        )
        ->when(
            $request->has('column_filter_not_between') &&
                $request->has('terms_filter_not_between'),
            function ($query) use ($request) {
                return $this->filterNotBetween($query, $request);
            }
        )
        ->whereNotNull("{$per}");
    }

    public function getSeriePer(Request $request, $id)
    {
        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        $per = $request->get('per');
        $dataset = DataSet::find($id);
        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $serie = DB::table("{$year}_{$initial}_{$system}_{$source}")
            ->select(DB::raw("{$per}, {$operation}({$rating}) as {$operation}"))
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->groupBy("{$per}")
            ->orderBy("{$per}")
            ->paginate($per_page);

        return $serie;
    }


    public function getSerieRange(Request $request, $id)
    {
        $dataset = DataSet::find($id);
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        if ($request->has('per') && $request->has('ranger')) {
            $per = $request->get('per');
            $ranger = $request->get('ranger');
            $rating = $request->get('rating');

            $selectArray = [];
            $select = '';

            $select = implode(', ', $selectArray);

            $query = DB::table("{$year}_{$initial}_{$system}_{$source}")
                ->where(function ($query) use ($request, $per) {
                    return $this->createWhere($query, $request, $per);
                });

            foreach ($this->{$ranger} as $key => $item) {
                if (count($item) > 1) {
                    if ($item[0] == null) {
                        $query->selectRaw(
                            "count(*) filter (where {$per} < {$item[1]}) as \"{$key}\""
                        );
                    } elseif ($item[1] == null) {
                        $query->selectRaw(
                            "count(*) filter (where {$per} >= {$item[0]}) as \"{$key}\""
                        );
                    } else {
                        $query->selectRaw(
                            "count(*) filter (where {$per} >= {$item[0]} and {$per} <= {$item[1]}) as \"{$key}\""
                        );
                    }
                } elseif (count($item) == 1) {
                    if (is_array($item[0])) {
                        $clauses = [];
                        foreach ($item[0] as $keyClause => $clause) {
                            if ($clause == null) {
                                $clauses[$keyClause] = 'is null';
                            } else {
                                $clauses[$keyClause] = "= {$clause}";
                            }
                        }

                        $query->selectRaw(
                            "count(*) filter (where {$per} {$clauses[0]} or {$per} {$clauses[1]}) as \"{$key}\""
                        );
                    } else {
                        $clause = $item[0];
                        if ($clause == null) {
                            $clause = 'is null';
                        } else {
                            $clause = "= {$clause}";
                        }

                        $query->selectRaw(
                            "count(*) filter (where {$per} {$clause}) as \"{$key}\""
                        );
                    }
                } else {
                    $query->selectRaw(
                        "count(*) filter (where {$per} = {$item[0]}) as \"{$key}\""
                    );
                }
            }

            $serie = $query->get();

            return $serie;
        }
    }

    public function getSerieCnes(Request $request, $id)
    {
        $dataset = DataSet::find($id);
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $per = $request->get('per');

        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        $tableName = "{$year}_{$initial}_{$system}_{$source}";
        $serie = DB::table($tableName)
            ->select(
                DB::raw(
                    "\"{$tableName}\".{$per} as code, {$operation}({$rating}) as {$operation}, alias_company_name as name"
                )
            )
            ->join(
                'health_units',
                "{$tableName}.{$per}",
                '=',
                'health_units.cnes_code'
            )
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->groupBy("{$tableName}.{$per}", 'alias_company_name')
            ->orderBy("{$operation}", 'desc')
            ->whereNotNull("{$rating}")
            ->paginate($per_page);

        return $serie;
    }

    public function getSerieCids(Request $request, $id)
    {
        $dataset = DataSet::find($id);
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $per = $request->get('per');

        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        $tableName = "{$year}_{$initial}_{$system}_{$source}";
        $serie = DB::table($tableName)
            ->select(DB::raw("\"{$tableName}\".{$per} as code, {$operation}({$rating}) as {$operation}, cids.description as name"))
            ->join(
                'cids',
                "{$tableName}.{$per}",
                '=',
                'cids.code'
            )
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->when($request->has('dnc'), function ($query) use ($request) {
                $query->where('cids.compulsory_notification', true);
            })
            ->groupBy("{$tableName}.{$per}", "cids.description")
            ->orderBy("{$operation}", "desc")
            ->whereNotNull("{$rating}")
            ->paginate($per_page);

        return $serie;
    }

    public function getSerieCbo(Request $request, $id)
    {
        $dataset = DataSet::find($id);
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $per = $request->get('per');

        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        $tableName = "{$year}_{$initial}_{$system}_{$source}";
        $serie = DB::table($tableName)
            ->select(
                DB::raw(
                    "\"{$tableName}\".{$per} as code, {$operation}({$rating}) as {$operation}, ds_ocupacao as name"
                )
            )
            ->join(
                'cbo_datasus',
                "{$tableName}.{$per}",
                '=',
                'cbo_datasus.co_cbo'
            )
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->groupBy("{$tableName}.{$per}", 'ds_ocupacao')
            ->orderBy("{$operation}", 'desc')
            ->whereNotNull("{$rating}")
            ->paginate($per_page);

        return $serie;
    }

    public function getSerieByDate(Request $request, $id)
    {
        $per_page = 12;
        if ($request->has('per_page')) {
            $per_page = $request->get('per_page');
        }

        $per = $request->get('per');
        $dataset = DataSet::find($id);
        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $serie = DB::table("{$year}_{$initial}_{$system}_{$source}")
            ->select(DB::raw("date_part('month', date_trunc('month', {$per})) as month, {$operation}({$rating}) as {$operation}"))
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->groupBy("month")
            ->orderBy("month")
            ->paginate($per_page);


        return $serie;
    }
}
