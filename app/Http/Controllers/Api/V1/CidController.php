<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $cids = Cid::whereRaw(
                "unaccent(code) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(code_dot) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(description) ilike unaccent('%{$keyword}%')"
            )->limit(30)->get();

            return $cids;
        } else {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $cids = Cid::paginate($perPage);

            return $cids;
        }
        return null;
    }
}
