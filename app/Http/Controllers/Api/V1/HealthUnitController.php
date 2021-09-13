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
            $perPage = $request->input('per_page');
        } else {
            $perPage = 5;
        }

        if ($request->has('search') && $request->has('per_page')) {

            $search = $request->get('search');
            $healthUnits = HealthUnit::whereRaw(
                "left(cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
            )->orWhereRaw(
                "unaccent(alias_company_name) ilike unaccent('%{$search}%')"
            )->orderBy('id')->paginate($perPage);

            return $healthUnits;
        } elseif ($request->has('search')) {
            $search = $request->get('search');
            $healthUnits = HealthUnit::whereRaw(
                "left(cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
            )->orWhereRaw(
                "unaccent(alias_company_name) ilike unaccent('%{$search}%')"
            )->orderBy('id')->get();

            return $healthUnits;
        } elseif ($request->has('cnes_code')) {
            $cnes_code = $request->get('cnes_code');
            $healthUnit = HealthUnit::where(
                'cnes_code', $cnes_code
            )->first();

            return $healthUnit;

        } else {

            $healthUnits = HealthUnit::orderBy('id')->paginate($perPage);

            return $healthUnits;
        }
        return null;
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
