<?php

namespace App\Http\Controllers;

use App\Events\IncidentCreation;
use App\Events\IncidentFinalization;
use App\Models\Incident;
use App\Models\RiskSituation;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Role;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution, RiskSituation $riskSituation)
    {
        $incidents = $riskSituation->incidents();
        $query = $request->query();

        if (isset($query['initial_date']) && isset($query['final_date'])) {
            $incidents->whereBetween('created_at', [$query['initial_date'], $query['final_date']]);
        }

        if (isset($query['initial_date'])) {
            $incidents->where('created_at', '>=', $query['initial_date']);
        }

        if (isset($query['final_date'])) {
            $incidents->where('created_at', '<=', $query['final_date']);
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $incidents->count();
        $incidents = $incidents->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $incidents->load(['riskSituation', 'informer', 'userReports', 'userReports.user', 'brigadiers', 'brigadiers.brigadier', 'brigadiers.meetPoint']),
            'pagination' => [
                'total' => $incidents->total(),
                'per_page' => $incidents->perPage(),
                'current_page' => $incidents->currentPage(),
                'total_pages' => $incidents->lastPage(),
                'last_page' => $incidents->lastPage(),
                'next_page_url' => $incidents->nextPageUrl(),
                'prev_page_url' => $incidents->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIncidentRequest $request, Institution $institution, RiskSituation $riskSituation)
    {
        $activeIncident = $institution->activeIncidents()->first();
        if ($activeIncident) {
            return response()->json(['message' => __('messages.active_incident')], 400);
        }

        /** @var User */
        $user = Auth::user();
        $role = $user->role()->where('institution_id', $institution->id)->first()?->name;
        if (!in_array($role, ['Administrator'])) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        $request['initial_date'] = now();
        $request['risk_situation_id'] = $riskSituation->id;
        $request['informer_id'] = Auth::id();
        $incident = Incident::create($request->all());
        IncidentCreation::dispatch($riskSituation);
        return response()->json(['data' => $incident->load(['riskSituation', 'informer']), 'message' =>  __('messages.created', ['Model' => __('incident')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        return response()->json(['data' => $incident->load(['riskSituation', 'informer', 'userReports', 'userReports.user', 'brigadiers', 'brigadiers.brigadier', 'brigadiers.meetPoint'])]);
    }

    public function getIncidentStatistics(Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        $incident['total_safe'] = $incident->userReports()->where('state', 'safe')->count();
        $incident['total_at_risk'] = $incident->userReports()->where('state', 'at_risk')->count();
        $incident['total_reports'] = $incident->userReports()->count();
        $incident['total_users'] = $institution->users()->count();
        return response()->json(['data' => $incident]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncidentRequest $request, Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        if ($incident->risk_situation_id !== $riskSituation->id) {
            return response()->json(['message' => __('messages.not_found_in_risk_situation', ['Model' => __('incident')])], 404);
        }

        /** @var User */
        $user = Auth::user();
        $role = $user->role()->where('institution_id', $institution->id)->first()?->name;
        if (!in_array($role, ['Administrator'])) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        if (!$incident->final_date) {
            $request['final_date'] = now();
            IncidentFinalization::dispatch($riskSituation);
        }

        $incident->update($request->all());
        return response()->json(['data' => $incident->load(['riskSituation', 'informer']), 'message' => __('messages.updated', ['Model' => __('incident')])]);
    }

    /**
     * Get all brigadiers of an institution that are assigned to a meet point in an active incident.
     */
    public function indexActiveBrigadiers(Request $request, Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        $query = $request->query();
        $users = $institution->users()->where('is_active', true)->wherePivot('role_id', 2)->whereHas('brigadierMeetPoints', function ($q) use ($incident) {
            return $q->where('incident_id', $incident->id);
        });

        if (isset($query['search'])) {
            $search = $query['search'];
            $users->where('name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
        }

        if (isset($query['name'])) {
            $users->where('name', 'like', '%' . $query['name'] . '%');
        }

        if (isset($query['last_name'])) {
            $users->where('last_name', 'like', '%' . $query['last_name'] . '%');
        }

        if (isset($query['email'])) {
            $users->where('email', 'like', '%' . $query['email'] . '%');
        }

        if (isset($query['id_card'])) {
            $users->where('id_card', $query['id_card']);
        }

        if (isset($query['rhgb'])) {
            $users->where('rhgb', $query['rhgb']);
        }

        if (isset($query['social_security'])) {
            $users->where('social_security', 'like', '%' . $query['social_security'] . '%');
        }

        if (isset($query['phone_number'])) {
            $users->where('phone_number', 'like', '%' . $query['phone_number'] . '%');
        }

        if (isset($query['is_active'])) {
            $users->where('is_active', $query['is_active']);
        }

        if (isset($query['code'])) {
            $users->whereHas('institutions', function ($q) use ($query) {
                $q->where('institution_users.code', 'like', '%' . $query['code'] . '%');
            });
        }

        $allowedOrderFields = ['name', 'last_name', 'email', 'id_card', 'rhgb', 'social_security', 'phone_number', 'is_active'];
        $orderBy = isset($query['order_by']) && in_array($query['order_by'], $allowedOrderFields) ? $query['order_by'] : 'updated_at';

        $allowedOrderDirections = ['asc', 'desc'];
        $orderDirection = isset($query['order_direction']) && in_array($query['order_direction'], $allowedOrderDirections) ? $query['order_direction'] : 'desc';

        $perPage = isset($query['per_page']) && $query['per_page'] > 0 ? $query['per_page'] : $users->count();
        $users = $users->orderBy($orderBy, $orderDirection)->paginate($perPage)->withQueryString();

        $usersResource = $users->map(function ($user) use ($incident) {
            $user->code = $user->pivot->code;
            $role = Role::find($user->pivot->role_id);
            $user->role = $role;
            $user->secondary_emails = $user->secondaryEmails()->pluck('email');
            $user->meet_point = $user->brigadierMeetPoints()->where('incident_id', $incident->id)->first()->meetPoint;
            return $user;
        });

        return response()->json([
            'data' => $usersResource->makeHidden('pivot'),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'last_page' => $users->lastPage(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        try {
            $incident->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('incident')])]);
        } catch (\Exception $e) {

            $resources = [];

            if ($incident->userReports->count() > 0) {
                $resources[] = __('user reports');
            }

            if ($incident->brigadiers->count() > 0) {
                $resources[] = __('brigadiers');
            }

            if ($incident->meetPoints->count() > 0) {
                $resources[] = __('meet points');
            }

            $resourceList = implode(', ', $resources);
            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('incident'), 'resources' => $resourceList])], 400);
        }
    }
}
