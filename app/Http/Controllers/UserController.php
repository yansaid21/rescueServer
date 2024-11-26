<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Institution;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['me', 'updateMe']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query();
        $query = $request->query();

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

        $perPage = isset($query['per_page']) && $query['per_page'] > 0 ? $query['per_page'] : $users->count();
        $users = $users->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        $users->each(function ($user) {
            $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        });

        return response()->json([
            'data' => $users->items(),
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
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::where('id_card', $request->id_card)->orWhere('email', $request->email)->first();

        // Check if the user already exists
        if ($user) {

            // Check if the user is already associated with the institution
            $institution = $user->institutions()->where('institution_id', $request->institution_id)->first();
            if ($institution) {
                return response()->json(['message' => __('messages.user_already_associated')], 400);
            }

            // Check if the email is already associated with the user
            if ($user->email != $request->email) {

                // Check if the email is already associated with the user as a secondary email
                $secondaryEmail = $user->secondaryEmails()->where('email', $request->email)->first();

                if (!$secondaryEmail) {

                    // Check if the email is already associated with another user
                    $emailUser = User::where('email', $request->email)->orWhereHas('secondaryEmails', function ($q) use ($request) {
                        $q->where('email', $request->email);
                    })->first();

                    if ($emailUser) {
                        return response()->json(['message' => __('messages.email_already_associated')], 400);
                    } else {
                        $user->secondaryEmails()->create(['email' => $request->email]);
                    }
                }
            }
        } else {
            $user = User::create($request->all());

            if ($request->hasFile('photo')) {
                $name = $user->id . '.' . $request->file('photo')->extension();
                $request->file('photo')->storeAs('public/users', $name);
                $user->photo_path = '/storage/users/' . $name;
                $user->save();
            }
        }

        $final_user_role = Role::where('name', 'Final User')->first();
        $user->institutions()->attach($request->institution_id, ['code' => $request->code, 'role_id' => $final_user_role->id]);
        $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        return response()->json(['data' => $user, 'message' => __('messages.created', ['Model' => __('user')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        return response()->json(['data' => $user, 'message' => __('messages.retrieved', ['Model' => __('user')])], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($request->has('email')) {
            // Check if the email is already associated with another user
            $emailUser = User::where('id', '!=', $user->id)->where('email', $request->email)->orWhereHas('secondaryEmails', function ($q) use ($request) {
                $q->where('email', $request->email);
            })->first();

            if ($emailUser) {
                throw ValidationException::withMessages([
                    'email' => [__('messages.email_already_associated', ['email' => $request->email])]
                ]);
            }

            error_log("Hola");

            if (!$request->has('secondary_emails')) {
                $secondaryEmail = $user->secondaryEmails()->where('email', $request->email)->first();
                if ($secondaryEmail) {
                    throw ValidationException::withMessages([
                        'email' => [__('messages.email_set_as_secondary', ['email' => $request->email])]
                    ]);
                }
            }
        }

        if ($request->has('secondary_emails')) {

            // Check if the email is already associated with another user
            $errors = [];
            foreach ($request->secondary_emails as $email) {
                $secondaryEmail = User::where('id', '!=', $user->id)->where('email', $email)
                    ->orWhereHas('secondaryEmails', function ($q) use ($email) {
                        $q->where('email', $email);
                    })->first();

                if ($secondaryEmail) {
                    array_push($errors, [
                        __('messages.email_already_associated', ['email' => $email])
                    ]);
                } else {
                    $emailUser = $request->email ?? $user->email;
                    if ($emailUser == $email) {
                        array_push($errors, [
                            __('messages.email_set_as_primary', ['email' => $email])
                        ]);
                    }
                }
            }

            // If there are errors, return them
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            $user->secondaryEmails()->delete();
            foreach ($request->secondary_emails as $email) {
                $user->secondaryEmails()->create(['email' => $email]);
            }
        }

        $user->update($request->all());

        if ($request->has('code')) {
            $user->institutions()->updateExistingPivot($request->institution_id, ['code' => $request->code]);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $name = $user->id . '.' . $request->file('photo')->extension();
            $request->file('photo')->storeAs('public/users', $name);
            $user->photo_path = '/storage/users/' . $name;
            $user->save();
        }

        $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        return response()->json(['data' => $user, 'message' => __('messages.updated', ['Model' => __('user')])], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->update(["is_active" => false])) {
            return response()->json(['data' => $user, 'message' => __('messages.deleted', ['Model' => __('user')])], 200);
        } else {
            return response()->json(['message' => __('messages.not_deleted', ['Model' => __('user')])], 400);
        }
    }

    /**
     * Get the information of the user authenticated.
     */
    public function me(Request $request)
    {
        /** @var User */
        $user = Auth::user();
        $tokenInfo = json_decode($user->currentAccessToken()->name);

        error_log(json_encode($tokenInfo));
        if (!$user->is_active) {
            return response()->json(['message' => __('messages.user_not_active')], 400);
        }

        $institution_id = $tokenInfo->institution;
        $user['registerCompleted'] = $user->isRegisterCompleted($institution_id);
        $user['role'] = $user->role()->where('institution_id', $institution_id)->first();
        $active_incident = Institution::find($institution_id)->activeIncidents()->first();
        if ($active_incident) {
            $user['incident_reported'] = $user->userReports()->where('incident_id', $active_incident->id)->first();
        }

        $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        return response()->json(['data' => $user, 'message' => __('messages.retrieved', ['Model' => __('user')])], 200);
    }

    /**
     * Update the information of the user authenticated.
     */
    public function updateMe(UpdateUserRequest $request)
    {
        /** @var User */
        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json(['message' => __('messages.user_not_active')], 400);
        }

        $request->offsetUnset('is_active');

        if ($request->has('email')) {
            // Check if the email is already associated with another user
            $emailUser = User::where('id', '!=', $user->id)->where('email', $request->email)->orWhereHas('secondaryEmails', function ($q) use ($request) {
                $q->where('email', $request->email);
            })->first();

            if ($emailUser) {
                throw ValidationException::withMessages([
                    'email' => [__('messages.email_already_associated', ['email' => $request->email])]
                ]);
            }

            error_log("Hola");

            if (!$request->has('secondary_emails')) {
                $secondaryEmail = $user->secondaryEmails()->where('email', $request->email)->first();
                if ($secondaryEmail) {
                    throw ValidationException::withMessages([
                        'email' => [__('messages.email_set_as_secondary', ['email' => $request->email])]
                    ]);
                }
            }
        }

        if ($request->has('secondary_emails')) {

            // Check if the email is already associated with another user
            $errors = [];
            foreach ($request->secondary_emails as $email) {
                $secondaryEmail = User::where('id', '!=', $user->id)->where('email', $email)
                    ->orWhereHas('secondaryEmails', function ($q) use ($email) {
                        $q->where('email', $email);
                    })->first();

                if ($secondaryEmail) {
                    array_push($errors, [
                        __('messages.email_already_associated', ['email' => $email])
                    ]);
                } else {
                    $emailUser = $request->email ?? $user->email;
                    if ($emailUser == $email) {
                        array_push($errors, [
                            __('messages.email_set_as_primary', ['email' => $email])
                        ]);
                    }
                }
            }

            // If there are errors, return them
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            $user->secondaryEmails()->delete();
            foreach ($request->secondary_emails as $email) {
                $user->secondaryEmails()->create(['email' => $email]);
            }
        }

        $user->update($request->all());

        if ($request->has('code')) {
            $user->institutions()->updateExistingPivot($request->institution_id, ['code' => $request->code]);
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $name = $user->id . '.' . $request->file('photo')->extension();
            $request->file('photo')->storeAs('public/users', $name);
            $user->photo_path = '/storage/users/' . $name;
            $user->save();
        }

        $user->secondary_emails = $user->secondaryEmails()->pluck('email');
        return response()->json(['data' => $user, 'message' => __('messages.updated', ['Model' => __('user')])], 200);
    }
}
