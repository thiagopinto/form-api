<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PhpParser\Node\Stmt\TryCatch;
use Throwable;

class DatasetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $source, $system, $initial)
    {
        if ($request->has('limit')) {
            $limit = $request->get('limit');
            $datasets = Dataset::where(
                [
                    'source' => $source,
                    'system' => $system,
                    'initial' => $initial,
                ]
            )->limit($limit)->orderBy('year', 'desc')->get();

            $class = Dataset::getClass($source, $system, $initial);
            $object = new $class();

            foreach ($datasets as $dataset) {
                $dataset->total = $object->getTotal($request, $source, $system, $initial, $dataset->id);
            }

            return $datasets;
        }

        if ($request->has('per_page')) {
            $perPage = $request->input('per_page');
        } else {
            $perPage = 10;
        }

        $datasets = Dataset::when($request->has('search'), function ($query) use ($request) {
            $search = $request->query('search');
            return $query->whereRaw(
                "left(datasets.year::text, length('{$search}')) ilike unaccent('%{$search}%')"
            );
        })->where(
            [
                'source' => $source,
                'system' => $system,
                'initial' => $initial,
            ]
        )->orderBy('year', 'desc')->paginate($perPage);

        return $datasets;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $source, $system, $initial)
    {
        $user = $request->user();


        if (!Gate::authorize('is-admin', $user)) {
            return $this->error('Not authorized.', 403);
        }


        $fileName = null;
        $request->validate(
            [
                'datasets' => 'required',
            ]
        );

        /**
         * Carga de dados com arquivo cvs ou dbf
         **/
        $files = $request->file('datasets');
        foreach ($files as $file) {
            if (is_file($file)) {

                $dataSets = Dataset::where([
                    'source' => $source,
                    'system' => $system,
                    'initial' => $initial,
                ])->get();

                foreach ($dataSets as $dataSet) {
                    Storage::delete($dataSet->file_name);
                }

                $user = $request->user();
                $originalName = strtolower($file->getClientOriginalName());

                $prefix = str_split($originalName, 2);
                $prefix = $prefix[0];

                $name = uniqid(date('HisYmd'));
                $extension = strtolower($file->getClientOriginalExtension());
                $nameFile = "{$name}.{$extension}";
                $path = $file->storeAs("{$source}_{$system}_{$initial}", $nameFile);

                if ($extension == 'dbc') {
                    $blast = env('PATH_BLAST', '../tools/blast-dbf/blast-dbf');
                    $storage = env('PATH_BLAST_STORAGE', '../storage/app/');
                    $newPath = str_replace(".dbc", ".dbf", $path);
                    echo exec("{$blast} {$storage}{$path} {$storage}/{$newPath}");
                    Storage::delete($path);
                    $extension = 'dbf';
                    $path = $newPath;
                }

                if (!$path) {
                    return $this->error('Insufficient Storage', 507);
                }

                $class = DataSet::getClass($source, $system, $initial);

                $object = new $class();

                try {
                    $object->loadFile($request, $path, $source, $system, $initial, $extension, $user);
                    return $this->success(
                        [
                            'status' => 'Success',
                            'message' => 'Created',
                            'data' => 'Load data',
                            'code' => 201,
                        ],
                        null,
                        201
                    );
                } catch (Throwable $th) {
                    dd($th);
                    return $th;
                    //return $this->error($th->getMessage(), 500);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dataset = Dataset::find($id);

        return $dataset;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function showYear(Request $request, $source, $system, $initial, $year)
    {
        $dataset = Dataset::where([
            'source' => $source,
            'system' => $system,
            'initial' => $initial,
            'year' => $year,
        ])->first();

        $class = Dataset::getClass($source, $system, $initial);

        $object = new $class();

        $dataset->total = $object->getTotal($request, $source, $system, $initial, $dataset->id);

        return $dataset;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $source, $system, $initial, $id)
    {
        $dataset = Dataset::findOrFail($id);

        $user = $request->user();

        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('color')) {
            $dataset->color = $request->get('color');
            $dataset->save();
        }

        return $dataset;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $source, $system, $initial, $id)
    {
        $user = $request->user();

        if (!Gate::authorize('is-admin', $user)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        try {
            $dataset = Dataset::find($id);
            $year = $dataset->year;
            $initial = $dataset->initial;
            $system = $dataset->system;
            $source = $dataset->source;

            $tableName = "{$year}_{$initial}_{$system}_{$source}";

            Schema::drop($tableName);
            $dataset->delete();

            return $this->success(
                [
                    'status' => 'Success',
                    'message' => 'Delete',
                    'data' => 'Delete',
                    'code' => 200,
                ]
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSerie(Request $request, $source, $system, $initial, $id)
    {
        $class = DataSet::getClass($source, $system, $initial);

        $object = new $class();

        return $object->getSerie($request, $id);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRange(Request $request, $source, $system, $initial, $id)
    {
        $class = DataSet::getClass($source, $system, $initial);

        $object = new $class();

        return $object->getRange($request, $id);
    }
}
