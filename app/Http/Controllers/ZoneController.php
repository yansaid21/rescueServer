<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution)
    {
        $zones = $institution->zones();
        $query = $request->query();

        if (isset($query['name'])) {
            $zones->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $zones->count();
        $zones = $zones->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $zones->items(),
            'pagination' => [
                'total' => $zones->total(),
                'per_page' => $zones->perPage(),
                'current_page' => $zones->currentPage(),
                'total_pages' => $zones->lastPage(),
                'last_page' => $zones->lastPage(),
                'next_page_url' => $zones->nextPageUrl(),
                'prev_page_url' => $zones->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreZoneRequest $request, Institution $institution)
    {
        $request['institution_id'] = $institution->id;
        $zone = Zone::create($request->all());

        return response()->json(['data' => $zone, 'message' =>  __('messages.created', ['Model' => __('zone')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, Zone $zone)
    {
        if ($zone->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('zone')])], 404);
        }

        return response()->json(['data' => $zone], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateZoneRequest $request, Institution $institution, Zone $zone)
    {
        if ($zone->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('zone')])], 404);
        }

        $zone->update($request->all());

        return response()->json(['data' => $zone, 'message' => __('messages.updated', ['Model' => __('zone')])], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, Zone $zone)
    {
        if ($zone->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('zone')])], 404);
        }

        try {
            $zone->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('zone')])], 200);
        } catch (\Exception $e) {
            $resources = [];

            if ($zone->rooms->count() > 0) {
                $resources[] = __('rooms');
            }
            if ($zone->meetPoints->count() > 0) {
                $resources[] = __('meet points');
            }
            $resourceList = implode(', ', $resources);

            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('zone'), 'resources' => $resourceList])], 400);
        }
    }
}
