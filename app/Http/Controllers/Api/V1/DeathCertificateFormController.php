<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeathCertificateForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PDF;
use DateTime;

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

        $forms = DeathCertificateForm::leftJoin(
            'health_units',
            'death_certificate_forms.cnes_code',
            '=',
            'health_units.cnes_code'
        )->select(
            'death_certificate_forms.*',
            'health_units.alias_company_name AS alias_company_name',
        )->when($request->has('status'), function ($query) use ($request) {
            $status = explode(',', $request->status);

            $query->whereIn('status', $status);
        })->when(($request->has('start') && $request->has('end')), function ($query) use ($request) {
            $start = $request->start;
            $end = $request->end;
            $query->whereBetween(
                'event_date',
                [$start, $end]
            );
        })->when($request->has('search'), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $search = $request->search;
                $query->orWhereRaw(
                    "left(death_certificate_forms.number::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "left(death_certificate_forms.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "death_certificate_forms.name ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "health_units.alias_company_name ilike unaccent('%{$search}%')"
                );
            });
        })->paginate($perPage);

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

        $tableName = 'death_certificate_forms';

        $forms = DeathCertificateForm::select(
            "{$tableName}.cnes_code as cnes_code",
            'health_units.alias_company_name as name',
            'health_units.longitude as longitude',
            'health_units.latitude as latitude',
            'health_units.stock_form_alive as stock_form_alive',
            'health_units.stock_form_death as stock_form_death',
        )->selectRaw(
            "count({$tableName}.cnes_code) AS received"
        )->selectRaw(
            "count(*) filter (where status = 2) as stock"
        )->selectRaw(
            "count(*) filter (where status = 3) as used"
        )->selectRaw(
            "count(*) filter (where status = 4) as canceled"
        )->join(
            'health_units',
            "{$tableName}.cnes_code",
            '=',
            'health_units.cnes_code'
        )->whereNotNull(
            "{$tableName}.cnes_code"
        )->when(($request->has('start') && $request->has('end')), function ($query) use ($request, $tableName) {

            $start = $request->start;
            $end = $request->end;

            $query->whereBetween(
                "{$tableName}.updated_at",
                [$start, $end]
            );
        })->when($request->has('search'), function ($query) use ($request) {

            $query->where(function ($query) use ($request) {
                $search = $request->get('search');
                return $query->orWhereRaw(
                    "left(health_units.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "unaccent(health_units.alias_company_name) ilike unaccent('%{$search}%')"
                );
            });
        })->groupBy(
            "{$tableName}.cnes_code",
            'health_units.alias_company_name',
            'health_units.longitude',
            'health_units.latitude',
            'health_units.stock_form_alive',
            'health_units.stock_form_death'
        )->orderBy(
            "{$tableName}.cnes_code"
        )->paginate($perPage);

        foreach ($forms as $item) {
            try {
                $item->last_receipt = DeathCertificateForm::select('receipt_date')
                    ->whereNotNull('receipt_date')
                    ->where('cnes_code', $item->cnes_code)
                    ->orderBy('receipt_date', 'DESC')
                    ->first()->receipt_date;
            } catch (\Throwable $th) {
                $item->last_receipt = null;
            }

            if ($item->last_receipt != null) {
                $last_receipt = new DateTime($item->last_receipt);
                $today = new DateTime();
                $days = $last_receipt->diff($today);
                $item->last_receipt_day = $days->format('%a');
            } else {
                $item->last_receipt_day = null;
            }
        }

        return $forms;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'start' => 'required',
            'cnes_code' => 'required'
        ]);

        if ($request->end != null) {
            if ($request->end < $request->start) {
                return response()->json(['error' => 'final number is less than initial.'], 500);
            }
            ini_set('max_execution_time', -1);
            ini_set('max_input_time', -1);
            ini_set('max_input_time', -1);
            for ($i = $request->start; $i <= $request->end; $i++) {
                if (!DeathCertificateForm::where('number', '=', $i)->exists()) {
                    $form = new DeathCertificateForm();
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
            if (!DeathCertificateForm::where('number', '=', $request->start)->exists()) {
                $form = new DeathCertificateForm();
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

            $request->validate([
                'cnes_code' => 'required'
            ]);

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

            $request->validate([
                'cnes_code' => 'required'
            ]);

            $form = DeathCertificateForm::where('number', $request->start)->first();
            $form->cnes_code = $request->cnes_code;
            $form->range_number_start = $request->start;
            $form->responsible = $request->responsible;
            $form->status = 2;
            $form->save();
        } elseif ($request->name != null && $request->event_date != null) {

            $request->validate([
                'cnes_code_devolution' => 'required'
            ]);

            $form = DeathCertificateForm::find($request->id);
            $form->name = $request->name;
            $form->event_date = $request->event_date;
            $form->receipt_date = $request->receipt_date;
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
        $form = DeathCertificateForm::find($id);
        if ($request->status == 2 ) {
            $form->receipt_date = null;
            $form->event_date = null;
            $form->name = null;
        }
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

        return PDF::loadView(
            'receipt',
            [
                'form' => $form,
                'countForms' => $countForms,
                'type' => 'declaração de óbito',
                'user' => $user->name,
                'today' => $today,
            ]
        )->download('Documento de cessão.pdf');
        //return view('receipt');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        if (!Gate::authorize('is-staff')) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        if ($request->has('per_page')) {
            $perPage = $request->input('per_page');
        } else {
            $perPage = 5;
        }

        $tableName = 'death_certificate_forms';

        //"\"{$tableName}\".{$per} as code, {$operation}({$rating}) as {$operation}, ds_ocupacao as name"

        $forms = DeathCertificateForm::select(
            "{$tableName}.cnes_code as cnes_code",
            'health_units.alias_company_name as name',
            'health_units.longitude as longitude',
            'health_units.latitude as latitude',
            'health_units.stock_form_alive as stock_form_alive',
            'health_units.stock_form_death as stock_form_death',
        )->selectRaw(
            "count({$tableName}.cnes_code) AS received"
        )->selectRaw(
            "count(*) filter (where status = 3) as used"
        )->selectRaw(
            "count(*) filter (where status = 4) as canceled"
        )->selectRaw(
            "count(*) filter (where status = 2) as stock"
        )->join(
            'health_units',
            "{$tableName}.cnes_code",
            '=',
            'health_units.cnes_code'
        )->whereNotNull(
            "{$tableName}.cnes_code"
        )->when(($request->has('start') && $request->has('end')), function ($query) use ($request, $tableName) {

            $start = $request->start;
            $end = $request->end;

            $query->whereBetween(
                "{$tableName}.updated_at",
                [$start, $end]
            );
        })->when($request->has('search'), function ($query) use ($request) {

            $query->where(function ($query) use ($request) {
                $search = $request->get('search');
                return $query->orWhereRaw(
                    "left(health_units.cnes_code::text, length('{$search}')) ilike unaccent('%{$search}%')"
                )->orWhereRaw(
                    "unaccent(health_units.alias_company_name) ilike unaccent('%{$search}%')"
                );
            });
        })->groupBy(
            "{$tableName}.cnes_code",
            'health_units.alias_company_name',
            'health_units.longitude',
            'health_units.latitude',
            'health_units.stock_form_alive',
            'health_units.stock_form_death'
        )->orderBy(
            "{$tableName}.cnes_code"
        )->paginate($perPage);

        foreach ($forms as $item) {
            try {
                $item->last_receipt = DeathCertificateForm::select('receipt_date')
                    ->whereNotNull('receipt_date')
                    ->where('cnes_code', $item->cnes_code)
                    ->orderBy('receipt_date', 'DESC')
                    ->first()->receipt_date;
            } catch (\Throwable $th) {
                $item->last_receipt = null;
            }

            if ($item->last_receipt != null) {
                $last_receipt = new DateTime($item->last_receipt);
                $today = new DateTime();
                $days = $last_receipt->diff($today);
                $item->last_receipt_day = $days->format('%a');
            } else {
                $item->last_receipt_day = null;
            }
        }

        $today = date("m-d-Y");

        return PDF::loadView(
            'report',
            [
                'forms' => $forms,
                'today' => $today

            ]
        )->download('Documento de cessão.pdf');
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastSend(Request $request, $cnes_code)
    {
        return DeathCertificateForm::where('cnes_code', $cnes_code)->orderBy('updated_at', 'DESC')->first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reversal(Request $request, $id)
    {
        $form = DeathCertificateForm::find($id);

        if ($form->range_number_end != null) {
            $start = $form->range_number_start;
            $end = $form->range_number_end;

            for ($i = $start; $i <= $end; $i++) {
                $form = DeathCertificateForm::where('number', $i)->first();
                $form->cnes_code = null;
                $form->range_number_start = null;
                $form->range_number_end = null;
                $form->responsible = null;
                $form->status = 1;
                $form->save();
            }
        } else {
            $form->cnes_code = null;
            $form->range_number_start = null;
            $form->range_number_end = null;
            $form->responsible = null;
            $form->status = 1;
            $form->save();
        }
    }
}
