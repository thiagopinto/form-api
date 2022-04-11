<?php

namespace App\Http\Controllers\Api\V1\Location;

use App\Http\Controllers\Controller;
use App\Models\Location\The\TheNeighborhood;
use Illuminate\Http\Request;

class NeighborhoodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $neighborhoods = TheNeighborhood::get();
        return $neighborhoods;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function map(Request $request)
    {

        $neighborhoods = TheNeighborhood::select('*')->selectRaw('ST_AsGeoJSON(the_neighborhood_geographies.area) AS geojson')
        ->join(
            'the_neighborhood_geographies',
            'the_neighborhood_geographies.the_neighborhood_id',
            '=',
            'the_neighborhoods.id'
        )
        ->get();

        $year = $request->get('year');
        foreach ($neighborhoods as $neighborhood) {
            $neighborhood->ibge_id = $neighborhood->id;
            $neighborhood->population = $neighborhood->populationByYear($year);
        }

        return $neighborhoods;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationDvs\TheNeighborhood $TheNeighborhood
     * @return \Illuminate\Http\Response
     */
    public function show(TheNeighborhood $TheNeighborhood)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationDvs\TheNeighborhood $TheNeighborhood
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TheNeighborhood $TheNeighborhood)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationDvs\TheNeighborhood $TheNeighborhood
     * @return \Illuminate\Http\Response
     */
    public function destroy(TheNeighborhood $TheNeighborhood)
    {
        //
    }
}
