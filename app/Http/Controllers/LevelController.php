<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLevelRequest;
use App\Http\Requests\UpdateLevelRequest;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution)
    {
        $levels = $institution->levels();
        $query = $request->query();

        if (isset($query['name'])) {
            $levels->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $levels->count();
        $levels = $levels->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $levels->items(),
            'pagination' => [
                'total' => $levels->total(),
                'per_page' => $levels->perPage(),
                'current_page' => $levels->currentPage(),
                'total_pages' => $levels->lastPage(),
                'last_page' => $levels->lastPage(),
                'next_page_url' => $levels->nextPageUrl(),
                'prev_page_url' => $levels->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLevelRequest $request, Institution $institution)
    {
        $request['institution_id'] = $institution->id;
        $level = Level::create($request->all());

        return response()->json(['data' => $level, 'message' =>  __('messages.created', ['Model' => __('level')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, Level $level)
    {
        if ($level->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('level')])], 404);
        }

        return response()->json(['data' => $level]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLevelRequest $request, Institution $institution, Level $level)
    {
        if ($level->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('level')])], 404);
        }

        $level->update($request->all());

        return response()->json(['data' => $level, 'message' => __('messages.updated', ['Model' => __('level')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, Level $level)
    {
        try {
            $level->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('level')])]);
        } catch (\Exception $e) {
            return response()->json(['message' => __('messages.cannot_delete_level')], 400);
        }
    }
}
