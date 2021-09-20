<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BornAliveForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use \PDF;

class BornAliveFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Gate::authorize('is-staff')) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('per_page')) {
            $perPage = $request->input('per_page');
        } else {
            $perPage = 5;
        }

        $status = explode(',', $request->status);

        if ($request->has('start') && $request->has('end')) {

            $start = $request->query('start');
            $end = $request->query('end');

            if ($request->query('search')) {
                $search = $request->query('search');
                $forms = BornAliveForm::leftJoin(
                    'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'born_alive_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn(
                    'status', $status
                )->whereRaw(
                    "left(born_alive_forms.number::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "left(born_alive_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "born_alive_forms.name ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "health_units.alias_company_name ilike unaccent('%{$search}%')"
                )->whereBetween(
                    'event_date', [$start, $end]
                )->orderBy('id')->paginate($perPage);
            } else {
                $forms = BornAliveForm::leftJoin(
                    'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'born_alive_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn(
                    'status', $status
                )->whereBetween(
                    'event_date', [$start, $end]
                )->orderBy('id')->paginate($perPage);
            }

        } else {

            if ($request->query('search')) {
                $search = $request->query('search');
                $forms = BornAliveForm::leftJoin(
                    'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'born_alive_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn('status', $status)
                    ->whereRaw(
                        "left(born_alive_forms.number::text, length('{$search}')) ilike unaccent('%{$search}%')"
                    )->orWhereRaw(
                    "left(born_alive_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "born_alive_forms.name ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "health_units.alias_company_name ilike unaccent('%{$search}%')"
                )->orderBy('id')->paginate($perPage);
            } else {
                $forms = BornAliveForm::leftJoin(
                    'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'born_alive_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn('status', $status)->orderBy('id')->paginate($perPage);
            }

        }

        return $forms;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexReport(Request $request)
    {
        if (!Gate::authorize('is-staff')) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('per_page')) {
            $perPage = $request->input('per_page');
        } else {
            $perPage = 5;
        }

        if ($request->query('search')) {
            $search = $request->query('search');
            $forms = BornAliveForm::join(
                'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
            )->select(
                'born_alive_forms.cnes_code',
                DB::raw('count("born_alive_forms"."cnes_code") AS count'),
                'health_units.alias_company_name AS name',
                'health_units.longitude AS longitude',
                'health_units.latitude AS latitude',
                'health_units.stock_form_alive AS stock_form_alive',
                'health_units.stock_form_death AS stock_form_death'

            )->whereNotNull(
                'born_alive_forms.cnes_code'
            )->where(
                'born_alive_forms.status', '2'
            )->whereRaw(
                "left(born_alive_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
            )->orWhereRaw(
                "health_units.alias_company_name ilike unaccent('%{$search}%')"
            )->groupBy(
                'born_alive_forms.cnes_code',
                'health_units.alias_company_name',
                'health_units.longitude',
                'health_units.latitude',
                'health_units.stock_form_alive',
                'health_units.stock_form_death'
            )->orderBy(
                'born_alive_forms.cnes_code'
            )->paginate($perPage);
        } else {
            $forms = BornAliveForm::join(
                'health_units', 'born_alive_forms.cnes_code', '=', 'health_units.cnes_code'
            )->select(
                'born_alive_forms.cnes_code',
                DB::raw('count("born_alive_forms"."cnes_code") AS count'),
                'health_units.alias_company_name AS name',
                'health_units.longitude AS longitude',
                'health_units.latitude AS latitude',
                'health_units.stock_form_alive AS stock_form_alive',
                'health_units.stock_form_death AS stock_form_death'
            )->whereNotNull(
                'born_alive_forms.cnes_code'
            )->where(
                'born_alive_forms.status', '2'
            )->groupBy(
                'born_alive_forms.cnes_code',
                'health_units.alias_company_name',
                'health_units.longitude',
                'health_units.latitude',
                'health_units.stock_form_alive',
                'health_units.stock_form_death'
            )->orderBy(
                'born_alive_forms.cnes_code'
            )->paginate($perPage);
        }

        return $forms;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->end != null) {
            if ($request->end < $request->start) {
                return response()->json(['error' => 'final number is less than initial.'], 500);
            }
            ini_set('max_execution_time', -1);
            ini_set('max_input_time', -1);
            ini_set('max_input_time', -1);
            for ($i = $request->start; $i <= $request->end; $i++) {
                if (!BornAliveForm::where('number', '=', $i)->exists()) {
                    $form = new BornAliveForm();
                    $form->number = $i;
                    $form->cnes_code = $request->cnes_code;
                    $form->status = 1;
                    $form->save();
                } else {
                    return response()->json(
                        [
                            'status' => 'Error',
                            'message' => 'duplicate record ' . $i,
                            'data' => null,
                            'code' => 500,
                        ],
                        500
                    )->header('Content-Type', 'text/plain');
                }

            }
        } else {
            if (!BornAliveForm::where('number', '=', $request->start)->exists()) {
                $form = new BornAliveForm();
                $form->number = $request->start;
                $form->cnes_code = $request->cnes_code;
                $form->status = 1;
                $form->save();
            } else {
                return response()->json(
                    [
                        'status' => 'Error',
                        'message' => 'duplicate record ' . $request->start,
                        'data' => null,
                        'code' => 500,
                    ],
                    500
                )->header('Content-Type', 'text/plain');
            }
        }

        return response()->json(['messages' => 'create forms.'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = null)
    {
        if ($request->end != null) {
            if ($request->end < $request->start) {
                return response()->json(['error' => 'final number is less than initial.'], 500);
            }
            ini_set('max_execution_time', -1);
            ini_set('max_input_time', -1);
            ini_set('max_input_time', -1);
            for ($i = $request->start; $i <= $request->end; $i++) {
                $form = BornAliveForm::where('number', $i)->first();
                $form->cnes_code = $request->cnes_code;
                $form->range_number_start = $request->start;
                $form->range_number_end = $request->end;
                $form->responsible = $request->responsible;
                $form->status = 2;
                $form->save();
            }
        } elseif ($request->start != null) {
            $form = BornAliveForm::where('number', $request->start)->first();
            $form->cnes_code = $request->cnes_code;
            $form->range_number_start = $request->start;
            $form->responsible = $request->responsible;
            $form->status = 2;
            $form->save();
        } elseif ($request->name != null && $request->event_date != null) {

            $form = BornAliveForm::find($request->id);
            $form->name = $request->name;
            $form->event_date = $request->event_date;
            $form->cnes_code_devolution = $request->cnes_code_devolution;
            $form->status = $request->status;
            $form->save();
        }

        return response()->json(['messages' => 'update forms.'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function partial_update(Request $request, $id = null)
    {
        $form = BornAliveForm::find($id);

        $form->status = $request->status;
        $form->save();

    }

    public function receipt(Request $request, $id)
    {
        $user = Auth::user();
        $today = date("m-d-Y");
        $form = BornAliveForm::with(['healthUnit'])->find($id);
        $countForms = BornAliveForm::where('cnes_code', $form->cnes_code)
            ->where('status', '<>', '3')
            ->where('status', '<>', '4')->count();

        return PDF::loadView('receipt',
            [
                'form' => $form,
                'countForms' => $countForms,
                'type' => 'declaração nascido vivo',
                'user' => $user->name,
                'today' => $today,
            ])->download('Documento de cessão.pdf');
        //return view('receipt');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function count(Request $request, $status)
    {
        return BornAliveForm::where('status', $status)->count();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastSend(Request $request, $cnes_code)
    {
        return BornAliveForm::where('cnes_code', $cnes_code)->orderBy('updated_at', 'DESC')->first();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reversal(Request $request, $id)
    {
        $form = BornAliveForm::find($id);

        if ($form->range_number_end != null) {
            $start = $form->range_number_start;
            $end = $form->range_number_end;
            $forms = [];

            for ($i=$start; $i <= $end; $i++) { 
                $form = BornAliveForm::where('number', $i)->first();
                $form->cnes_code = null;
                $form->range_number_start = null;
                $form->range_number_end = null;
                $form->responsible = null;
                $form->status = 1;
                $form->save();
                $forms[] = $form;
            }
            return $forms;
        } else {
            $form->cnes_code = null;
            $form->range_number_start = null;
            $form->range_number_end = null;
            $form->responsible = null;
            $form->status = 1;
            $form->save();
            return $form;
        }
        
    }


}
