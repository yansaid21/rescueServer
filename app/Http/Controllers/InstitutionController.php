<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreInstitutionRequest;
use App\Http\Requests\UpdateInstitutionRequest;
use App\Models\MeetPointBrigadier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class InstitutionController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['userReportsActiveIncident']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $institutions = Institution::query();
        $query = $request->query();

        if (isset($query['name'])) {
            $institutions->where('name', 'like', '%' . $query['name'] . '%');
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $institutions->count();
        $institutions = $institutions->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        $institutions->each(function ($institution) {
            $institution->active_incident = $institution->activeIncidents()->first();
            $institution->makeHidden('riskSituations');
        });

        return response()->json([
            'data' => $institutions->items(),
            'pagination' => [
                'total' => $institutions->total(),
                'per_page' => $institutions->perPage(),
                'current_page' => $institutions->currentPage(),
                'total_pages' => $institutions->lastPage(),
                'last_page' => $institutions->lastPage(),
                'next_page_url' => $institutions->nextPageUrl(),
                'prev_page_url' => $institutions->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstitutionRequest $request)
    {
        $institution = Institution::create($request->all());

        return response()->json(['data' => $institution, 'message' =>  __('messages.created', ['Model' => __('institution')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution)
    {
        $institution->active_incident = $institution->activeIncidents()->first();
        $institution->makeHidden('riskSituations');
        return response()->json(['data' => $institution], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstitutionRequest $request, Institution $institution)
    {
        $institution->update($request->all());

        return response()->json(['data' => $institution, 'message' => __('messages.updated', ['Model' => __('institution')])], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution)
    {
        try {
            $institution->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('institution')])], 200);
        } catch (\Exception $e) {
            $resources = [];

            if ($institution->users->count() > 0) {
                $resources[] = __('users');
            }
            if ($institution->zones->count() > 0) {
                $resources[] = __('zones');
            }
            if ($institution->levels->count() > 0) {
                $resources[] = __('levels');
            }
            if ($institution->riskSituations->count() > 0) {
                $resources[] = __('risk_situations');
            }
            if ($institution->meetPoints->count() > 0) {
                $resources[] = __('meet_points');
            }

            $resourceList = implode(', ', $resources);

            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('institution'), 'resources' => $resourceList])], 400);
        }
    }

    /**
     * Get all rooms of an institution.
     */
    public function rooms(Request $request, Institution $institution)
    {
        $rooms = $institution->zones()->with(['rooms' => function ($query) use ($request) {
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->query('name') . '%');
            }
            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->query('code') . '%');
            }
        }, 'rooms.level'])->get()->pluck('rooms')->flatten();


        return response()->json(['data' => $rooms], 200);
    }

    /**
     * Get all protocols of an institution.
     */
    public function protocols(Request $request, Institution $institution)
    {
        $protocols = $institution->riskSituations()->with(['protocols' => function ($query) use ($request) {
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->query('name') . '%');
            }
        }, 'protocols.riskSituation'])->get()->pluck('protocols')->flatten();

        return response()->json(['data' => $protocols], 200);
    }

    /**
     * Get all incidents of an institution.
     */
    public function incidents(Request $request, Institution $institution)
    {
        $incidents = $institution->riskSituations()->with([
            'incidents' => function ($query) use ($request) {
                if ($request->has('initial_date') && $request->has('final_date')) {
                    $query->whereBetween('created_at', [$request->query('initial_date'), $request->query('final_date')]);
                }
            },
            'incidents.riskSituation',
            'incidents.informer'
        ])->get()->pluck('incidents')->flatten();

        return response()->json(['data' => $incidents], 200);
    }

    /**
     * Get all users of an institution.
     */
    public function users(Request $request, Institution $institution)
    {
        $query = $request->query();
        $users = $institution->users();

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

        if (isset($query['role_id'])) {
            $users->whereHas('role', function ($q) use ($query) {
                $q->where('role_id', $query['role_id']);
            });
        }

        $allowedOrderFields = ['name', 'last_name', 'email', 'id_card', 'rhgb', 'social_security', 'phone_number', 'is_active'];
        $orderBy = isset($query['order_by']) && in_array($query['order_by'], $allowedOrderFields) ? $query['order_by'] : 'updated_at';

        $allowedOrderDirections = ['asc', 'desc'];
        $orderDirection = isset($query['order_direction']) && in_array($query['order_direction'], $allowedOrderDirections) ? $query['order_direction'] : 'desc';

        $perPage = isset($query['per_page']) && $query['per_page'] > 0 ? $query['per_page'] : $users->count();
        $users = $users->orderBy($orderBy, $orderDirection)->paginate($perPage)->withQueryString();

        $users = $users->map(function ($user) use ($institution) {
            $user->code = $user->pivot->code;
            $role = Role::find($user->pivot->role_id);
            $user->role = $role;
            $user->secondary_emails = $user->secondaryEmails()->pluck('email');
            return $user;
        });

        return response()->json(['data' => $users->makeHidden('pivot')], 200);
    }

    public function user(Institution $institution, User $user)
    {
        $user = $institution->users()->firstWhere("id", $user->id);
        if (!$user) {
            return response()->json(['message' => __("messages.not_found", ['Model' => __("user")])], 404);
        }

        $user = [
            ...$user->makeHidden("pivot")->toArray(),
            "code" => $user->pivot->code,
            "secondary_emails" => $user->secondaryEmails()->pluck('email'),
            "role" => Role::find($user->pivot->role_id),
            "registerCompleted" => $user->isRegisterCompleted($institution->id),
        ];

        return response()->json(['data' => $user]);
    }

    /**
     * Get all reports of a user in an incident.
     */
    public function userReports(Request $request, Institution $institution, User $user)
    {
        $reports = $user->userReports()->whereHas('incident', function ($q) use ($institution) {
            $q->whereHas('riskSituation', function ($q) use ($institution) {
                $q->where('institution_id', $institution->id);
            });
        });
        $query = $request->query();

        if (isset($query['state'])) {
            $reports->where('state', $query['state']);
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $reports->count();
        $reports = $reports->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();
        $reportsResource = $reports->map(function ($report) use ($reports) {
            $report['total_safe'] = $reports->where("state", "safe")->count();
            $report['total_risk'] = $reports->where("state", "at_risk")->count();
            $report['total_out'] = $reports->where("state", "outside")->count();
            return $report;
        });

        return response()->json([
            'data' => $reportsResource->load(['user', 'zone', 'resolution', 'incident', 'incident.riskSituation']),
            'pagination' => [
                'total' => $reports->total(),
                'per_page' => $reports->perPage(),
                'current_page' => $reports->currentPage(),
                'total_pages' => $reports->lastPage(),
                'last_page' => $reports->lastPage(),
                'next_page_url' => $reports->nextPageUrl(),
                'prev_page_url' => $reports->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Assign users to be brigadiers of an institution.
     */
    public function assignBrigadiers(Request $request, Institution $institution)
    {
        Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($institution) {
                    if (!$institution->users()->where('id', $value)->exists()) {
                        $fail(__('messages.not_found_in_institution', ['Model' => __('user')]));
                    }
                },
            ],
        ])->validate();

        $users = User::find($request->users);
        $brigadierRole = Role::where('name', 'Brigadier')->firstOrFail();

        $users->each(function ($user) use ($institution, $brigadierRole) {
            if ($user->role()->where('institution_id', $institution->id)->first()?->name === 'Administrator') {
                return;
            }
            $user->institutions()->updateExistingPivot($institution->id, ['role_id' => $brigadierRole->id]);
        });

        return response()->json(['message' => __('messages.assigned', ['Model' => __('user'), 'other' => __('brigadier')])], 200);
    }

    /**
     * Remove the brigadier role from users.
     */
    public function removeBrigadiers(Request $request, Institution $institution)
    {
        Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($institution) {
                    if (!$institution->users()->where('id', $value)->exists()) {
                        $fail(__('messages.not_found_in_institution', ['Model' => __('user')]));
                    }
                },
            ],
        ])->validate();

        $users = User::find($request->users);
        $final_user_role = Role::where('name', 'Final User')->first();
        $force = $request->query('force', '0') === '1';

        $errors = [];
        $users_administrators = [];
        $users->each(function ($user) use ($institution, &$errors, $force, &$users_administrators) {
            if ($user->role()->where('institution_id', $institution->id)->first()?->name === 'Administrator') {
                $users_administrators[] = $user->id;
            } else if (!$force) {
                if ($user->brigadierMeetPoints()->whereHas('meetPoint', function ($query) use ($institution) {
                    $query->where('institution_id', $institution->id);
                })->first()) {
                    array_push(
                        $errors,
                        __('messages.brigadier_assigned', ['email' => $user->email])
                    );
                }
            }
        });

        if (!empty($errors)) {
            return response()->json(['message' => __('messages.brigadier_has_meet_points'), 'errors' => $errors], 400);
        }

        $users = $users->reject(function ($user) use ($users_administrators) {
            return in_array($user->id, $users_administrators);
        });

        $users->each(function ($user) use ($institution, $final_user_role, $force) {
            if ($force) {
                $user->brigadierMeetPoints()->whereHas('meetPoint', function ($query) use ($institution) {
                    $query->where('institution_id', $institution->id);
                })->delete();
            }
            $user->institutions()->updateExistingPivot($institution->id, ['role_id' => $final_user_role->id]);
        });

        return response()->json(['message' => __('messages.removed', ['Model' => __('brigadier rol')])], 200);
    }

    /**
     * Assign brigadier to meet point
     */
    public function assignBrigadier(Request $request, Institution $institution, User $user)
    {
        Validator::make($request->all(), [
            'meet_point' => 'required|exists:meet_points,id'
        ])->validate();

        if (!$institution->users()->where('id', $user->id)->exists()) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('user')])], 404);
        }

        $meetPoint = $institution->meetPoints()->where('id', $request->meet_point)->first();
        if (!$meetPoint) {
            return response()->json(['message' => __('messages.not_found_in_institution', ['Model' => __('meet point')])], 404);
        }

        if ($user->role()->where('institution_id', $institution->id)->first()?->name != 'Brigadier') {
            return response()->json(['message' => __('messages.user_not_brigadier')], 400);
        }

        $assignedMeetPoint = $user->brigadierMeetPoints()->where('incident_id', null)->whereHas('meetPoint', function ($query) use ($institution) {
            $query->where('institution_id', $institution->id);
        })->first();

        if ($assignedMeetPoint) {
            return response()->json(['message' => __('messages.already_assigned')], 400);
        }

        $meetPointBrigadier = MeetPointBrigadier::create([
            'brigadier_id' => $user->id,
            'meet_point_id' => $meetPoint->id,
        ]);

        $user->brigadier_meet_point = $meetPointBrigadier;

        return response()->json(['data' => $user, 'message' => __('messages.assigned', ['Model' => __('brigadier'), 'other' => __('meet point')])], 200);
    }

    /**
     * Assign users to be administrators of an institution.
     */
    public function assignAdministrators(Request $request, Institution $institution)
    {
        Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*' => 'required|integer|exists:users,id',
        ])->validate();

        $users = User::find($request->users);
        $administratorRole = Role::where('name', 'Administrator')->firstOrFail();

        $users->each(function ($user) use ($institution, $administratorRole) {
            if ($user->role()->where('institution_id', $institution->id)->first()?->name === 'Brigadier') {
                return;
            }
            $user->institutions()->updateExistingPivot($institution->id, ['role_id' => $administratorRole->id]);
        });

        return response()->json(['message' => __('messages.assigned', ['Model' => __('user'), 'other' => __('administrator')])], 200);
    }

    /**
     * Get the status of users in an institution during the active incident.
     * Indicate the state of the user putted in the user_reports table (Or if the user is not in the table, the state is 'No report')
     */
    public function usersStats(Request $request, Institution $institution)
    {
        $users = $institution->users()->with(['userReports' => function ($query) use ($institution) {
            $query->whereHas('incident', function ($query) use ($institution) {
                $query->where('id', $institution->activeIncidents()->first()->id);
            });
        }])->get();

        $users = $users->map(function ($user) {
            $user->state = $user->userReports->count() > 0 ? $user->userReports->first()->state : 'No report';
            $user->location = $user->userReports->count() > 0 ? $user->userReports->first()->location : null;
            $user->report_description = $user->userReports->count() > 0 ? $user->userReports->first()->description : null;
            return $user;
        });

        return response()->json([
            'data' => $users->makeHidden(['pivot', 'userReports'])->load('role')
        ], 200);
    }

    public function userReportsActiveIncident(Request $request, Institution $institution)
    {
        /** @var User */
        $user = Auth::user();

        $incident = $institution->activeIncidents()->first();

        $userReport = $user->userReports()->whereHas('incident', function ($query) use ($incident) {
            $query->where('id', $incident->id);
        })->first();

        if ($userReport) {
            $userReport->load(['resolution', 'zone', 'zone.meetPoints']);
        }

        return response()->json(['data' => $userReport]);
    }
}
