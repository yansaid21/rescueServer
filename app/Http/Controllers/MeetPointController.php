<?php

namespace App\Http\Controllers;

use App\Events\BrigadierAssignment;
use App\Models\MeetPoint;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMeetPointRequest;
use App\Http\Requests\UpdateMeetPointRequest;
use App\Models\Incident;
use App\Models\MeetPointBrigadier;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MeetPointController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['assignBrigadier']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution)
    {
        $meetPoints = $institution->meetPoints();
        $query = $request->query();

        if (isset($query['name'])) {
            $meetPoints->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $meetPoints->count();
        $meetPoints = $meetPoints->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $meetPoints->load('zones'),
            'pagination' => [
                'total' => $meetPoints->total(),
                'per_page' => $meetPoints->perPage(),
                'current_page' => $meetPoints->currentPage(),
                'total_pages' => $meetPoints->lastPage(),
                'last_page' => $meetPoints->lastPage(),
                'next_page_url' => $meetPoints->nextPageUrl(),
                'prev_page_url' => $meetPoints->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMeetPointRequest $request, Institution $institution)
    {
        $request['institution_id'] = $institution->id;
        $meetPoint = MeetPoint::create($request->all());

        $meetPoint->zones()->attach($request->zones);

        return response()->json(['data' => $meetPoint->load('zones'), 'message' =>  __('messages.created', ['Model' => __('meet point')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, MeetPoint $meetPoint)
    {
        if ($meetPoint->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('meet point')])], 404);
        }

        return response()->json(['data' => $meetPoint->load('zones')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeetPointRequest $request, Institution $institution, MeetPoint $meetPoint)
    {
        $meetPoint->update($request->all());

        if ($request->has('zones')) {
            $meetPoint->zones()->sync($request->zones);
        }

        return response()->json(['data' => $meetPoint->load('zones'), 'message' => __('messages.updated', ['Model' => __('meet point')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, MeetPoint $meetPoint)
    {
        try {
            $meetPoint->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('meet point')])]);
        } catch (\Exception $e) {

            $resources = [];

            if ($meetPoint->zones->count() > 0) {
                $resources[] = __('zones');
            }

            if ($meetPoint->brigadiers->count() > 0) {
                $resources[] = __('brigadiers');
            }

            if ($meetPoint->incidents->count() > 0) {
                $resources[] = __('incidents');
            }

            $resourceList = implode(', ', $resources);

            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('meet point'), 'resources' => $resourceList])], 400);
        }
    }

    /**
     * Get the meet point of a user.
     */
    public function userMeetPoint(Institution $institution, User $user)
    {
        $meetPoint = $user->brigadierMeetPoints()->where('incident_id', null)->whereHas('meetPoint', function ($query) use ($institution) {
            $query->where('institution_id', $institution->id);
        })->first()->load('meetPoint')->meetPoint;

        if (!$meetPoint) {
            return response()->json(['message' => __('messages.not_found', ['Model' => __('meet point')])], 404);
        }

        return response()->json(['data' => $meetPoint]);
    }

    /**
     * Get the meet point of a user in an incident.
     */
    public function userMeetPointIncident(Institution $institution, User $user, Incident $incident)
    {
        $meetPoint = $user->brigadierMeetPoints()->where('incident_id', $incident->id)->first()->load('meetPoint')->meetPoint;

        if (!$meetPoint) {
            return response()->json(['message' => __('messages.not_found', ['Model' => __('meet point')])], 404);
        }

        return response()->json(['data' => $meetPoint]);
    }

    /**
     * Assign the authenticated brigadier to a meet point in an incident.
     */
    public function assignBrigadier(Request $request, Institution $institution, MeetPoint $meetPoint)
    {
        /** @var User */
        $user = Auth::user();

        if ($user->role()->where('institution_id', $institution->id)->first()?->name != 'Brigadier') {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        if ($meetPoint->institution_id !== $institution->id) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('meet point')])], 404);
        }

        $incident = $institution->activeIncidents()->first();
        if (!$incident) {
            return response()->json(['message' => __('messages.not_active_incident')], 400);
        }

        $assignedMeetPoint = $user->brigadierMeetPoints()->where('incident_id', $incident->id)->first();
        if ($assignedMeetPoint) {
            return response()->json(['message' => __('messages.already_assigned_incident')], 400);
        }

        MeetPointBrigadier::create([
            'brigadier_id' => $user->id,
            'meet_point_id' => $meetPoint->id,
            'incident_id' => $incident->id,
        ]);
        BrigadierAssignment::dispatch($institution->id, $user->id);
        return response()->json(['message' => __('messages.assigned',  ['Model' => __('brigadier'), 'other' => __('meet point')])]);
    }
}
