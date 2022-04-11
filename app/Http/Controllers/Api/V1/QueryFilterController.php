<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\QueryFilter;
use App\Models\QueryFilterCid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;


class QueryFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $headers = $request->headers->all();

        if (isset($headers['authorization'])) {
            $authorization = str_replace('Bearer ', '', $headers['authorization']);
            $token = explode('|', $authorization[0]);
            $token = PersonalAccessToken::findToken($token[1]);
            $user = $token->tokenable;

            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $queryFilters = QueryFilter::with('filterCids.cid')->where("is_public", true)
                ->orWhere("user_id", $user->id)->orderBy('id', 'desc')->paginate($perPage);

            return $queryFilters;
        } else {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $queryFilters = QueryFilter::with('filterCids.cid')->where("is_public", true)->orderBy('id', 'desc')->paginate($perPage);

            return $queryFilters;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $queryFilter = QueryFilter::create([
            'name' => $request->name,
            'is_public' => $request->is_public,
            'user_id' => $user->id
        ]);

        foreach ($request->cids as $cid) {
            $queryFilter->filterCids[] = QueryFilterCid::create([
                'cid_id' => $cid,
                'query_filter_id' => $queryFilter->id
            ]);
        }
        return $queryFilter;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QueryFilter  $queryFilter
     * @return \Illuminate\Http\Response
     */
    public function show(QueryFilter $queryFilter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QueryFilter  $queryFilter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QueryFilter $queryFilter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QueryFilter  $queryFilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $queryFilter = QueryFilter::with('filterCids')->find($id);

        foreach ($queryFilter->filterCids as $filterCid) {
            $filterCid->delete();
        }
        $queryFilter->delete();
        return true;
    }
}
