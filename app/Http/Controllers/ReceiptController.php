<?php

namespace App\Http\Controllers;

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
        return PDF::loadView('receipt')->download('Documento de cessão.pdf');
        //return view('receipt');
    }
}
