<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messages Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during the messages of the application.
    |
    */

    /** 200 HTTP status code messages */
    'found' => ':Model found',
    'created' => ':Model created',
    'added' => ':Model added successfully',
    'updated' => ':Model updated successfully',
    'deleted' => ':Model deleted successfully',
    'added_to' => ':Model added to :other',
    'no_error' => 'No errors found',
    'assigned' => ':Model assigned to :other',
    'removed' => ':Model removed successfully',
    'resolved' => ':Model resolved successfully',

    /** 400 HTTP status code messages */
    'bad_request' => 'Bad Request',
    'errors_found' => 'Errors found',
    'unknown_error' => 'Error while :operation the :Model',
    'cannot_delete_role' => 'Cannot delete the role because some users have it',
    'cannot_delete_user' => 'The user cannot be deleted because he or she has alerted about a risk.',
    'cannot_delete_level' => 'The level cannot be deleted because it has rooms related.',
    'cannot_delete' => 'The :Model cannot be deleted because it has related resources: :resources',
    'user_already_associated' => 'The user with that id is already associated with the institution',
    'email_already_associated' => 'The email :email is already associated to another user',
    'email_set_as_primary' => 'The email :email is set as primary email',
    'email_set_as_secondary' => 'The email :email is set as secondary email',
    'active_incident' => 'An active incident already exists in the institution',
    'already_reported' => 'The user has already reported the incident',
    'user_not_brigadier' => 'The user is not a brigadier',
    'not_active_incident' => 'There is no active incident in the institution',
    'already_assigned_incident' => 'The user is already assigned to another meet point in the same incident',
    'not_logged' => 'There is no session logged',
    'user_not_active' => 'The user is not active',
    'not_deleted' => 'The :Model cannot be deleted',
    'already_assigned' => 'The brigadier is already assigned to a fixed meet point',
    'incident_closed' => 'The incident is finished',
    'already_resolved' => 'The user report is already resolved',

    /** 401 HTTP status code messages */
    'unauthorized' => 'Unauthorized',

    /** 403 HTTP status code messages */


    /** 404 HTTP status code messages */
    'not_found' => ':Model not found',
    'not_found_in_institution' => ':Model not found in the institution',
    'not_found_in_zone' => ':Model not found in the zone',
    'not_found_in_risk_situation' => ':Model not found in the risk situation',
    'not_found_in_incident' => ':Model not found in the incident',

    /** 422 HTTP status code messages */
    'already_exist' => ':Model already exist in the institution',
    'required' => ':Attribute are required',
    'not_valid' => 'The :model is not valid',
    'attribute_properties' => 'The :attribute must have the following properties: :values',
    'attribute_at_least_one' => 'The :attribute must have at least one component',
    'attribute_value' => 'The :attribute must have :value',
    'email_domain' => 'The email domain must be autonoma.edu.co',
    'user_is_administrator' => 'The user with the e-mail :email is and administrator',
    'brigadier_assigned' => 'The user with the e-mail :email have meet points assigned',
    'photo_mimetypes' => 'The photo must be of one of the following image types: jpeg, png, webp, svg, heic, heif',
    'cannot_change_state' => 'The state cannot be changed',
    'cannot_resolve' => 'The user report cannot be resolved because it is not at risk',
];
