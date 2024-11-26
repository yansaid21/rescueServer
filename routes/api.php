<?php

use App\Http\Controllers\{AuthenticationController, RoleController, UserController, InstitutionController, ZoneController, LevelController, RoomController, MeetPointController, RiskSituationController, ProtocolController, IncidentController, UserReportController};
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Localization;

Route::middleware([Localization::class])->group(function () {

    /**
     * Routes of roles.
     * 1. Resource of roles.
     */
    Route::apiResource('roles', RoleController::class);

    /**
     * Routes of users.
     * 1. Resource of users.
     * 2. Get the authenticated user.
     * 3. Update the authenticated user.
     */
    Route::apiResource('users', UserController::class);
    Route::get('profile', [UserController::class, 'me']);
    Route::put('profile', [UserController::class, 'updateMe']);

    /**
     * Routes of institutions.
     * 1. Resource of institutions.
     * 2. Get all rooms of an institution.
     * 3. Get all protocols of an institution.
     * 4. Get all incidents of an institution.
     * 5. Get all users of an institution.
     * 6. Get all reports of a user in an incident.
     * 7. Get the status of users in an institution during the active incident.
     * 8. Get the userReport of the authenticated user in the active incident.
     */
    Route::apiResource('institutions', InstitutionController::class);
    Route::get('institutions/{institution}/rooms', [InstitutionController::class, 'rooms']);
    Route::get('institutions/{institution}/protocols', [InstitutionController::class, 'protocols']);
    Route::get('institutions/{institution}/incidents', [InstitutionController::class, 'incidents']);
    Route::get('institutions/{institution}/users', [InstitutionController::class, 'users']);
    Route::get('institutions/{institution}/users/{user}', [InstitutionController::class, 'user']);
    Route::get('institutions/{institution}/users/{user}/reports', [InstitutionController::class, 'userReports']);
    Route::get('institutions/{institution}/users/stats', [InstitutionController::class, 'usersStats']);
    Route::get('institutions/{institution}/user_reports', [InstitutionController::class, 'userReportsActiveIncident']);

    /**
     * Routes of zones.
     * 1. Resource of zones.
     */
    Route::apiResource('institutions.zones', ZoneController::class);

    /**
     * Routes of levels.
     * 1. Resource of levels.
     */
    Route::apiResource('institutions.levels', LevelController::class);

    /**
     * Routes of rooms.
     * 1. Resource of rooms.
     */
    Route::apiResource('institutions.zones.rooms', RoomController::class);

    /**
     * Routes of meet points.
     * 1. Resource of meet points.
     * 2. Get the meet point of a brigadier.
     * 3. Get the meet point of a brigadier in an incident.
     * 3. Assign the authenticated brigadier to a meet point in an incident.
     */
    Route::apiResource('institutions.meet_points', MeetPointController::class);
    Route::get('institutions/{institution}/meet_points/user/{user}', [MeetPointController::class, 'userMeetPoint']);
    Route::get('institutions/{institution}/meet_points/user/{user}/incident/{incident}', [MeetPointController::class, 'userMeetPointIncident']);
    Route::post('institutions/{institution}/meet_points/{meetPoint}/assign', [MeetPointController::class, 'assignBrigadier']);

    /**
     * Routes of risk situations.
     * 1. Resource of risk situations.
     */
    Route::apiResource('institutions.risk_situations', RiskSituationController::class);

    /**
     * Routes of protocols.
     * 1. Resource of protocols.
     */
    Route::apiResource('institutions.risk_situations.protocols', ProtocolController::class);

    /**
     * Routes of incidents.
     * 1. Resource of incidents.
     */
    Route::apiResource('institutions.risk_situations.incidents', IncidentController::class);
    Route::get('institutions/{institution}/risk_situations/{riskSituation}/incidents/{incident}/statistics', [IncidentController::class, 'getIncidentStatistics']);

    /**
     * Routes of user reports.
     * 1. Resource of user reports.
     * 2. Resolution of user reports.
     */
    Route::apiResource('institutions.risk_situations.incidents.user_reports', UserReportController::class);
    Route::post('institutions/{institution}/risk_situations/{riskSituation}/incidents/{incident}/user_reports/{userReport}/resolution', [UserReportController::class, 'resolution']);

    /**
     * Routes of brigadiers / administrators.
     * 1. Assign users as brigadier.
     * 2. Remove brigadier role to users.
     * 3. Assign a brigadier to meet point.
     * 4. Assig users as administrators.
     * 5. Get all brigadiers of an institution that are assigned to a meet point in an incident.
     */
    Route::post('institutions/{institution}/brigadiers', [InstitutionController::class, 'assignBrigadiers']);
    Route::delete('institutions/{institution}/brigadiers', [InstitutionController::class, 'removeBrigadiers']);
    Route::post('institutions/{institution}/brigadiers/{user}', [InstitutionController::class, 'assignBrigadier']);
    Route::post('institutions/{institution}/administrators', [InstitutionController::class, 'assignAdministrators']);
    Route::get('institutions/{institution}/risk_situations/{riskSituation}/incidents/{incident}/brigadiers', [IncidentController::class, 'indexActiveBrigadiers']);

    /**
     * Routes of authentication.
     * 1. Login.
     * 2. Logout.
     */
    Route::post('auth/login', [AuthenticationController::class, 'login']);
    Route::post('auth/logout', [AuthenticationController::class, 'logout']);
});
