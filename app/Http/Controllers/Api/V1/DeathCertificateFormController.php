<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeathCertificateForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use \PDF;

class DeathCertificateFormController extends Controller
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
                $forms = DeathCertificateForm::leftJoin(
                    'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'death_certificate_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn('status', $status)
                    ->whereRaw(
                        "left(death_certificate_forms.number::text, length('{$search}')) ilike unaccent('%{$search}%')"
                    )->orWhereRaw(
                    "left(death_certificate_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "death_certificate_forms.name ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "health_units.alias_company_name ilike unaccent('%{$search}%')"
                )->whereBetween(
                    'event_date', [$start, $end]
                )->orderBy('id')->paginate($perPage);
            } else {
                $forms = DeathCertificateForm::leftJoin(
                    'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'death_certificate_forms.*',
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
                $forms = DeathCertificateForm::leftJoin(
                    'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'death_certificate_forms.*',
                    'health_units.alias_company_name AS alias_company_name',
                )->whereIn('status', $status)
                    ->whereRaw(
                        "left(death_certificate_forms.number::text, length('{$search}')) ilike unaccent('%{$search}%')"
                    )->orWhereRaw(
                    "left(death_certificate_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "death_certificate_forms.name ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "health_units.alias_company_name ilike unaccent('%{$search}%')"
                )->orderBy('id')->paginate($perPage);
            } else {
                $forms = DeathCertificateForm::leftJoin(
                    'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
                )->select(
                    'death_certificate_forms.*',
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
            $forms = DeathCertificateForm::join(
                'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
            )->select(
                'death_certificate_forms.cnes_code',
                DB::raw('count("death_certificate_forms"."cnes_code") AS count'),
                'health_units.alias_company_name AS name',
                'health_units.longitude AS longitude',
                'health_units.latitude AS latitude',
                'health_units.stock_form_alive AS stock_form_alive',
                'health_units.stock_form_death AS stock_form_death'

            )->whereNotNull(
                'death_certificate_forms.cnes_code'
            )->where(
                'death_certificate_forms.status', '2'
            )->whereRaw(
                "left(death_certificate_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
            )->orWhereRaw(
                "health_units.alias_company_name ilike unaccent('%{$search}%')"
            )->groupBy(
                'death_certificate_forms.cnes_code',
                'health_units.alias_company_name',
                'health_units.longitude',
                'health_units.latitude',
                'health_units.stock_form_alive',
                'health_units.stock_form_death'
            )->orderBy(
                'death_certificate_forms.cnes_code'
            )->paginate($perPage);
        } else {
            $forms = DeathCertificateForm::join(
                'health_units', 'death_certificate_forms.cnes_code', '=', 'health_units.cnes_code'
            )->select(
                'death_certificate_forms.cnes_code',
                DB::raw('count("death_certificate_forms"."cnes_code") AS count'),
                'health_units.alias_company_name AS name',
                'health_units.longitude AS longitude',
                'health_units.latitude AS latitude',
                'health_units.stock_form_alive AS stock_form_alive',
                'health_units.stock_form_death AS stock_form_death'
            )->whereNotNull(
                'death_certificate_forms.cnes_code'
            )->where(
                'death_certificate_forms.status', '2'
            )->groupBy(
                'death_certificate_forms.cnes_code',
                'health_units.alias_company_name',
                'health_units.longitude',
                'health_units.latitude',
                'health_units.stock_form_alive',
                'health_units.stock_form_death'
            )->orderBy(
                'death_certificate_forms.cnes_code'
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
                $form = new DeathCertificateForm();
                $form->number = $i;
                $form->cnes_code = $request->cnes_code;
                $form->status = 1;
                $form->save();
            }
        } else {
            $form = new DeathCertificateForm();
            $form->number = $request->start;
            $form->cnes_code = $request->cnes_code;
            $form->status = 1;
            $form->save();
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
                $form = DeathCertificateForm::where('number', $i)->first();
                $form->cnes_code = $request->cnes_code;
                $form->range_number_start = $request->start;
                $form->range_number_end = $request->end;
                $form->responsible = $request->responsible;
                $form->status = 2;
                $form->save();
            }
        } elseif ($request->start != null) {
            $form = DeathCertificateForm::where('number', $request->start)->first();
            $form->cnes_code = $request->cnes_code;
            $form->range_number_start = $request->start;
            $form->responsible = $request->responsible;
            $form->status = 2;
            $form->save();
        } elseif ($request->name != null && $request->event_date != null) {

            $form = DeathCertificateForm::find($request->id);
            $form->name = $request->name;
            $form->event_date = $request->event_date;
            $form->cnes_code_devolution = $request->cnes_code_devolution;
            $form->status = $request->status;
            $form->save();
        }

        return response()->json(['messages' => 'create forms.'], 201);
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
        $form = DeathCertificateForm::find($id);

        $form->status = $request->status;
        $form->save();

    }

    public function receipt(Request $request, $id)
    {
        $user = Auth::user();
        $today = date("m-d-Y");
        $form = DeathCertificateForm::with(['healthUnit'])->find($id);
        $countForms = DeathCertificateForm::where('cnes_code', $form->cnes_code)
            ->where('status', '<>', '3')
            ->where('status', '<>', '4')->count();

        return PDF::loadView('receipt',
            [
                'form' => $form,
                'countForms' => $countForms,
                'type' => 'declaração de óbito',
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
        return DeathCertificateForm::where('status', $status)->count();
    }

}
