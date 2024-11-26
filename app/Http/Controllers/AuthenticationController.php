<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;


class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login']);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'institution' => 'required|exists:institutions,id',
            'password' => 'required|string|min:8|max:100',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::where('email', $request->email)->orWhereHas('secondaryEmails', function ($q) use ($request) {
            $q->where('email', $request->email);
        })->whereHas('institutions', function ($q) use ($request) {
            $q->where('id', $request->institution);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => [__("auth.credentials")],
            ]);
        }

        // Delete the token if it exists a session with the same device name
        foreach ($user->tokens as $token) {
            if ($token->name == $request->device_name) {
                $token->delete();
            }
        }

        $token = $user->createToken(json_encode(["device" => $request->device_name, "institution" => $request->institution]))->plainTextToken;
        $user['registerCompleted'] = $user->isRegisterCompleted($request->institution);
        $user['role'] = $user->role()->where('institution_id', $request->institution)->first();
        $active_incident = Institution::find($request->institution)->activeIncidents()->first();
        if ($active_incident) {
            $user['incident_reported'] = $user->userReports()->where('incident_id', $active_incident->id)->first();
        }

        return response()->json(['message' => __("auth.success"), 'token' => $token, 'user' => $user], 200);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        $request->user("sanctum")->currentAccessToken()->delete();
        return response()->json(['message' => __("auth.logout_success")], 200);
    }
}
