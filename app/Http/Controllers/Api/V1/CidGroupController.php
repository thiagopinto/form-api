<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CidGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CidGroupController extends Controller
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
            $cidGroups = CidGroup::whereRaw(
                "unaccent(starting_code) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(final_code) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(name) ilike unaccent('%{$keyword}%')"
            )->limit(30)->get();

            return $cidGroups;
        } else {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $cidGroups = CidGroup::paginate($perPage);

            return $cidGroups;
        }
        return null;
    }
}
