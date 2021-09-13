<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BrasilIo;
use App\Models\DataSet;
use App\Models\HealthUnit;
use App\Models\OccupationOfHealthUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class DataSetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $source, $datasetName)
    {
        if ($request->has('dates_load_data')) {
            $year = $request->get('dates_load_data');
            $dates = DB::table("{$year}_{$datasetName}_{$source}")->select('date')->groupBy('date')->orderBy('date', 'desc')->get();
            $dataSet = DataSet::where(
                [
                    'year' => $year,
                    'source' => $source,
                    'name' => $datasetName,
                ]
            )->first();
            $dataSet->dates = $dates;
            return $dataSet;
        } elseif ($request->has('data_frame')) {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            if ($datasetName == "occupation_of_health_units") {
                $year = $request->get('data_frame');
                $datas = DB::table("{$year}_{$datasetName}_{$source}")
                    ->orderBy('updated_at', 'desc')
                    ->paginate($perPage);

                foreach ($datas as $data) {
                    $healthUnit = HealthUnit::where('cnes_code', $data->cnes_code)->first();
                    $state = $healthUnit->state();
                    $city = $healthUnit->city();

                    $data->name = $healthUnit->alias_company_name;
                    $data->state = $state->name;
                    $data->city = $city->name;
                }

                return $datas;
            }
        } elseif ($request->has('limit')) {
            $limit = $request->get('limit');
            $dataSets = DataSet::where(
                [
                    'source' => $source,
                    'name' => $datasetName,
                ]
            )->limit($limit)->orderBy('year', 'desc')->get();

            if ($datasetName == "occupation_of_health_units") {
                foreach ($dataSets as $dataset) {
                    $dataset->dates = DB::table("{$dataset->year}_{$datasetName}_{$source}")->select('date')->groupBy('date')->orderBy('date', 'desc')->get();
                }
            }

            return $dataSets;
        } else {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $dataSets = DataSet::where(
                [
                    'source' => $source,
                    'name' => $datasetName,
                ]
            )->paginate($perPage);

            return $dataSets;
        }
        return null;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $source, $datasetName)
    {
        $user = $request->user();

        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('type')) {
            $type = $request->get('type');
            if ($type == 'single') {
                if ($datasetName == "occupation_of_health_units") {
                    if (OccupationOfHealthUnit::createItem($request, $datasetName, $source, $user)) {
                        return response()->json(
                            [
                                'status' => 'Success',
                                'message' => 'Created',
                                'data' => 'Load data',
                                'code' => 201,
                            ],
                            201
                        )->header('Content-Type', 'text/plain');
                    } else {
                        return response()->json(
                            [
                                'status' => 'Error',
                                'message' => 'Insufficient Storage',
                                'data' => null,
                                'code' => 507,
                            ],
                            507
                        )->header('Content-Type', 'text/plain');
                    }
                }
            }
        }

        $fileName = null;
        $request->validate(
            [
                'dataset' => 'required|file',
            ]
        );

        $file = $request->file('dataset');
        $user = $request->user();

        $name = uniqid(date('HisYmd'));
        $extension = strtolower($file->getClientOriginalExtension());
        $nameFile = "{$name}.{$extension}";
        $path = $file->storeAs("{$source}_{$datasetName}", $nameFile);
        if (!$path) {
            return response()->json(
                [
                    'status' => 'Error',
                    'message' => 'Insufficient Storage',
                    'data' => null,
                    'code' => 507,
                ],
                507
            )->header('Content-Type', 'text/plain');
        }

        if ($source == 'brasilio') {
            if (BrasilIo::loadFile($path, $datasetName, $source, $extension, $user)) {
                return response()->json(
                    [
                        'status' => 'Success',
                        'message' => 'Created',
                        'data' => 'Load data',
                        'code' => 201,
                    ],
                    201
                )->header('Content-Type', 'text/plain');
            } else {
                return response()->json(
                    [
                        'status' => 'Error',
                        'message' => 'Insufficient Storage',
                        'data' => null,
                        'code' => 507,
                    ],
                    507
                )->header('Content-Type', 'text/plain');
            }
        } elseif ($source == 'sesapi') {
            if ($datasetName == 'occupation_of_health_units') {
                $date = $request->get('date');
                if (OccupationOfHealthUnit::loadFile($path, $datasetName, $source, $extension, $user, $date)) {
                    return response()->json(
                        [
                            'status' => 'Success',
                            'message' => 'Created',
                            'data' => 'Load data',
                            'code' => 201,
                        ],
                        201
                    )->header('Content-Type', 'text/plain');
                } else {
                    return response()->json(
                        [
                            'status' => 'Error',
                            'message' => 'Insufficient Storage',
                            'data' => null,
                            'code' => 507,
                        ],
                        507
                    )->header('Content-Type', 'text/plain');
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $source, $datasetName, $id)
    {
        $user = $request->user();

        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('type')) {
            $type = $request->get('type');
            if ($type == 'single') {
                if ($datasetName == "occupation_of_health_units") {
                    if (OccupationOfHealthUnit::updateItem($request, $datasetName, $source, $user, $id)) {
                        return response()->json(
                            [
                                'status' => 'Success',
                                'message' => 'Created',
                                'data' => 'Load data',
                                'code' => 201,
                            ],
                            201
                        )->header('Content-Type', 'text/plain');
                    } else {
                        return response()->json(
                            [
                                'status' => 'Error',
                                'message' => 'Insufficient Storage',
                                'data' => null,
                                'code' => 507,
                            ],
                            507
                        )->header('Content-Type', 'text/plain');
                    }
                }
            }
        }

        if ($request->has('type')) {
            $type = $request->get('type');
            if ($type == 'single') {
                if ($datasetName == "occupation_of_health_units") {
                    if (OccupationOfHealthUnit::update($request, $datasetName, $source, $user, $id)) {
                        return response()->json(
                            [
                                'status' => 'Success',
                                'message' => 'Created',
                                'data' => 'Load data',
                                'code' => 201,
                            ],
                            201
                        )->header('Content-Type', 'text/plain');
                    } else {
                        return response()->json(
                            [
                                'status' => 'Error',
                                'message' => 'Insufficient Storage',
                                'data' => null,
                                'code' => 507,
                            ],
                            507
                        )->header('Content-Type', 'text/plain');
                    }
                }
            }
            $dates = DB::table("{$year}_{$datasetName}_{$source}")->select('date')->groupBy('date')->get();
            $dataSet = DataSet::where(
                [
                    'year' => $year,
                    'source' => $source,
                    'name' => $datasetName,
                ]
            )->first();
            $dataSet->dates = $dates;
            return $dataSet;
        }

        $dataSet = DataSet::find($id);
        if ($request->has('color')) {
            $dataSet->color = $request->get('color');
            $dataSet->save();
        }

        if ($source == 'brasilio') {
            if ($request->has('date')) {
                if (BrasilIo::getDataByApi($request->get('date'), $datasetName, $source, $user)) {
                    $dataSet->updated_at = now();
                }
            }
        }
        return $dataSet;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user, $source, $datasetName, $id)
    {
        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('year')) {
            $year = $request->get('year');
            DB::table("{$year}_{$datasetName}_{$source}")->where('id', $id)->delete();

            return response()->json(
                [
                    'status' => 'Success',
                    'message' => 'Delete',
                    'data' => 'Delete',
                    'code' => 200,
                ],
                200
            )->header('Content-Type', 'text/plain');
        }

        $dataSet = DataSet::find($id);
        $tableName = "{$dataSet->year}_{$dataSet->name}_{$dataSet->source}";

        if ($dataSet->delete()) {
            Schema::dropIfExists($tableName);
        }

        return response()->json(
            [
                'status' => 'Success',
                'message' => 'Delete',
                'data' => 'Delete dataset',
                'code' => 200,
            ],
            200
        )->header('Content-Type', 'text/plain');
    }
}
