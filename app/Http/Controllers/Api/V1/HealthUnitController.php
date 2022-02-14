<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HealthUnit;
use Illuminate\Http\Request;

class HealthUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('per_page')) {
            $perPage = $request->get('per_page');
        } else {
            $perPage = 5;
        }

        if ($request->has('per_page')) {

            $healthUnits = HealthUnit::when($request->has('search'), function ($query) use ($request) {

                $query->where(function ($query) use ($request) {
                    $search = $request->get('search');
                    return $query->orWhereRaw(
                        "left(cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                    )->orWhereRaw(
                        "unaccent(alias_company_name) ilike unaccent('%{$search}%')"
                    );
                });

            })->when($request->has('cnes_code'), function ($query) use ($request) {
                $cnes_code = $request->get('cnes_code');
                return $query->where(
                    'cnes_code',
                    $cnes_code
                );
            })->orderBy('id')->paginate($perPage);

        } else {

            $healthUnits = HealthUnit::when($request->has('search'), function ($query) use ($request) {

                $query->where(function ($query) use ($request) {
                    $search = $request->get('search');
                    return $query->orWhereRaw(
                        "left(cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                    )->orWhereRaw(
                        "unaccent(alias_company_name) ilike unaccent('%{$search}%')"
                    );
                });

            })->when($request->has('cnes_code'), function ($query) use ($request) {
                $cnes_code = $request->get('cnes_code');
                return $query->where(
                    'cnes_code',
                    $cnes_code
                );
            })->orderBy('id')->get();
        }
        return $healthUnits;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'unit_code' => 'required|unique:health_units',
            'cnes_code' => 'required|unique:health_units',
            'alias_company_name' => 'required'
        ]);

        $healthUnitRequest = $request->all();

        $healthUnit = HealthUnit::create(
            $healthUnitRequest
        );

        return response()->json(['messages' => 'Unidade dadastrada!.'], 201);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            'unit_code' => 'required|unique:health_units',
            'cnes_code' => 'required|unique:health_units',
            'alias_company_name' => 'required'
        ]);

        $healthUnit = HealthUnit::find($id);

        $healthUnitRequest = $request->all();

        $healthUnit->update(
            $healthUnitRequest
        );

        return response()->json(['messages' => 'Unidade dadastrada!.'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $healthUnit = HealthUnit::where('cnes_code', $id)->first();

        return $healthUnit;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function partial_update(Request $request, $id = null)
    {
        if ($request->has('action')) {
            $action = $request->get('action');
            if ($action == 'geo_reference') {
                foreach ($request->ids as $id) {
                    $healthUnit = HealthUnit::find($id);
                    $healthUnit->geocodeAddressFull();
                }
                return response()->json(
                    [
                        'status' => 'Success',
                        'message' => 'Created',
                        'data' => 'Update GEOCode',
                        'code' => 201,
                    ],
                    200
                )->header('Content-Type', 'text/plain');
            }

            if ($action == 'set_stock') {

                $healthUnit = HealthUnit::find($request->id);
                $healthUnit->stock_form_death = $request->stock_form_death;
                $healthUnit->stock_form_alive = $request->stock_form_alive;
                $healthUnit->save();

                return response()->json(
                    [
                        'status' => 'Success',
                        'message' => 'Created',
                        'data' => 'Update GEOCode',
                        'code' => 201,
                    ],
                    200
                )->header('Content-Type', 'text/plain');
            }
        }

        $healthUnit = HealthUnit::find($id);

        return $healthUnit;
    }
}
