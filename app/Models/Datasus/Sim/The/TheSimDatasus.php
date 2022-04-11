<?php

namespace App\Models\Datasus\Sim\The;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Location\The\TheNeighborhood;
use App\Models\Location\The\TheNeighborhoodSpellingVariation;
use App\Models\Datasus\Sim\SimDatasus;
use App\Models\Dataset;

class TheSimDatasus extends SimDatasus
{
    use HasFactory;

    public $keys = ['numerodo', 'dtobito'];

    public $ibgeCodeCity = '2211001';
    public $ibgeCodeCityShot = '221100';

    public function filterIsResident($query)
    {
        return $query->orWhere(
            'codmunres',
            $this->ibgeCodeCity
        )->orWhere(
            'codmunres',
            $this->ibgeCodeCityShot
        );
    }

    public function createWhere($query, $request, $per = 'id')
    {
        $query->when(
            $request->has('is_resident') && ($request->get('is_resident') === "true"),
            function ($query) {
                return $this->filterIsResident($query);
            }
        );

        return parent::createWhere($query, $request, $per);
    }

    public function getTheNeighborhood($alias)
    {
        $neighborhood = null;

        $neighborhood = TheNeighborhood::where('standardized', 'ilike', "%{$alias->standardized}%")->first();

        if (!is_null($neighborhood)) {
            return $neighborhood->id;
        }

        if (is_null($neighborhood)) {
            $neighborhood = TheNeighborhood::where('metaphone', 'ilike', "%{$alias->metaphone}%")->first();

            if (!is_null($neighborhood)) {
                return $neighborhood->id;
            }
        }

        if (is_null($neighborhood)) {
            $neighborhood = TheNeighborhood::where('soundex', 'ilike', "%{$alias->soundex}%")->first();

            if (!is_null($neighborhood)) {
                return $neighborhood->id;
            }
        }

        return null;
    }

    public function rowHandler($row)
    {
        if ($row['codmunres'] == $this->ibgeCodeCity) {
            if (!is_null($row['baires'])) {
                $neighborhoodAlias = new TheNeighborhoodSpellingVariation();
                $alias = null;

                $standardized = $neighborhoodAlias->nameCase($row['baires']);
                $metaphone = $neighborhoodAlias->getPhraseMetaphone($row['baires']);
                $soundex = soundex($row['baires']);

                $alias = TheNeighborhoodSpellingVariation::where('standardized', 'ilike', "%{$standardized}%")->first();

                if (is_null($alias)) {
                    $alias = TheNeighborhoodSpellingVariation::where('metaphone', 'ilike', "%{$metaphone}%")->first();
                }

                if (is_null($alias)) {
                    $alias = TheNeighborhoodSpellingVariation::where('soundex', 'ilike', "%{$soundex}%")->first();
                }

                if (is_null($alias)) {
                    $alias = new TheNeighborhoodSpellingVariation();
                    $alias->name = $row['baires'];
                    $alias->standardized = $standardized;
                    $alias->metaphone = $metaphone;
                    $alias->soundex = $soundex;
                    $alias->the_neighborhood_id = $this->getTheNeighborhood($alias);
                    $alias->save();
                }

                $row['codbaires'] = $alias->id;
                return $row;
            } else {
                $row['codbaires'] = null;
                return $row;
            }
        } else {
            return $row;
        }
    }

    public static function getClassNeighborhood($initial)
    {
        $class = 'App\Models\Location\\';
        $class .= ucfirst($initial) . '\\';
        $class .= ucfirst($initial);
        $class .= 'Neighborhood';

        return $class;
    }

    public static function getClassNeighborhoodSpellingVariation($initial)
    {
        $class = 'App\Models\Location\\';
        $class .= ucfirst($initial) . '\\';
        $class .= ucfirst($initial);
        $class .= 'NeighborhoodSpellingVariation';

        return $class;
    }

    public function getSerieByLocationType(Request $request, $id)
    {
        // neighborhood
        $byLocationType = $request->get('by_location_type');

        $per = $request->get('per');
        $dataset = DataSet::find($id);
        $operation = $request->get('operation');
        $rating = $request->get('rating');
        $year = $dataset->year;
        $initial = $dataset->initial;
        $system = $dataset->system;
        $source = $dataset->source;

        $classNeighborhood =
            $this->getClassNeighborhood($initial);

        $classNeighborhoodSpellingVariation =
            $this->getClassNeighborhoodSpellingVariation($initial);

        $neighborhoods = $classNeighborhood::get();

        foreach ($neighborhoods as $neighborhood) {
            $spellingVariations =
                $classNeighborhoodSpellingVariation::select('id')->where(
                    'the_neighborhood_id',
                    $neighborhood->id
                )->get();
            $ids = [];
            foreach ($spellingVariations as $spelling) {
                $ids[] = $spelling->id;
            }

            $count = DB::table("{$year}_{$initial}_{$system}_{$source}")
            ->whereIn($per, $ids)
            ->where(function ($query) use ($request, $per) {
                return $this->createWhere($query, $request, $per);
            })
            ->groupBy("{$per}")
            ->orderBy("{$per}")
            ->count();
            $neighborhood->ibge_id = $neighborhood->id;
            $neighborhood->idsSpellings = $ids;
            $neighborhood->count = $count;
        }


        return $neighborhoods;

        // return $locations;
    }

    public function getSerieByLocationTypeCity(Request $request, $id)
    {
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
            ->when(
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
                $request->has('column_filter_or') &&
                    $request->has('term_filter_or_range'),
                function ($query) use ($request) {
                    return $this->filterTermOrRange($query, $request);
                }
            )
            ->when(
                $request->has('column_filters') &&
                    $request->has('term_filters'),
                function ($query) use ($request) {
                    return $this->filterTerms($query, $request);
                }
            )
            ->whereNotNull("{$per}")
            ->groupBy("{$per}")
            ->orderBy("{$per}")
            ->get();

        $locations = [];

        foreach ($serie as $key => $item) {
            $city = City::where(function ($query) use ($request, $per, $item) {
                if (strlen((string)($item->$per)) == 6) {
                    return $query->where('ibge_id_short', $item->$per);
                } else {
                    return $query->where('ibge_id', $item->$per);
                }
            })->first();

            if ($city != null) {
                $item->id = $city->id;
                $locations[] = $item;
            }
        }

        return $locations;
    }
}
