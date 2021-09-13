<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \PDF;


class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $form = $request;
        dd($form->healthUnit);
        return PDF::loadView('receipt', ['form' => $form])->download('Documento de cessão.pdf');
        //return view('receipt');
    }
}
