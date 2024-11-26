<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Zone;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution, Zone $zone)
    {
        $rooms = $zone->rooms();
        $query = $request->query();

        if (isset($query['name'])) {
            $rooms->where('name', 'like', '%' . $query['name'] . '%');
        }

        if (isset($query['code'])) {
            $rooms->where('code', 'like', '%' . $query['code'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $rooms->count();
        $rooms = $rooms->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $rooms->load('level'),
            'pagination' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'current_page' => $rooms->currentPage(),
                'total_pages' => $rooms->lastPage(),
                'last_page' => $rooms->lastPage(),
                'next_page_url' => $rooms->nextPageUrl(),
                'prev_page_url' => $rooms->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request, Institution $institution, Zone $zone)
    {

        $request['zone_id'] = $zone->id;
        $room = Room::create($request->all());

        return response()->json(['data' => $room->load('level'), 'message' =>  __('messages.created', ['Model' => __('room')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, Zone $zone, Room $room)
    {
        return response()->json(['data' => $room->load('level')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Institution $institution, Zone $zone, Room $room)
    {
        if ($room->zone_id !== $zone->id) {
            return response()->json(['message' => __('messages.not_found_in_zone', ['Model' => __('room')])], 404);
        }

        $room->update($request->all());

        return response()->json(['data' => $room->load('level'), 'message' =>  __('messages.updated', ['Model' => __('room')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, Zone $zone, Room $room)
    {
        $room->delete();

        return response()->json(['message' => __('messages.deleted', ['Model' => __('room')])]);
    }
}
