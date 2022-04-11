<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CidChapter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CidChapterController extends Controller
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
            $cidChapters = CidChapter::whereRaw(
                "unaccent(starting_code) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(final_code) ilike unaccent('%{$keyword}%')"
            )->orWhereRaw(
                "unaccent(name) ilike unaccent('%{$keyword}%')"
            )->limit(30)->get();

            return $cidChapters;
        } else {
            if ($request->has('per_page')) {
                $perPage = $request->input('per_page');
            } else {
                $perPage = 5;
            }

            $cidChapters = CidChapter::paginate($perPage);

            return $cidChapters;
        }
        return null;
    }
}
