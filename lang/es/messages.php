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
    'found' => ':Model encontrado/a',
    'created' => ':Model creado/a',
    'added' => ':Model agregado/a exitosamente',
    'updated' => ':Model actualizado/a exitosamente',
    'deleted' => ':Model eliminado/a exitosamente',
    'added_to' => ':Model agregado/a a :other',
    'no_error' => 'No se encontraron errores',
    'retrieved' => ':Model recuperado/a exitosamente',
    'assigned' => ':Model asignado/a a :other',
    'removed' => ':Model removido/a exitosamente',
    'resolved' => ':Model resuelto/a exitosamente',

    /** 400 HTTP status code messages */
    'bad_request' => 'Solicitud incorrecta',
    'errors_found' => 'Errores encontrados',
    'unknown_error' => 'Error al :operation el :Model',
    'cannot_delete_role' => 'No se puede eliminar el rol porque algunos usuarios tienen este rol asignado.',
    'cannot_delete_user' => 'El usuario no puede ser eliminado porque ha alertado sobre un riesgo.',
    'cannot_delete_level' => 'El nivel no puede ser eliminado porque tiene salones relacionados.',
    'cannot_delete' => 'El/La :Model no puede ser eliminado/a porque tiene recursos relacionados: :resources',
    'user_already_associated' => 'El usuario con ese id ya está asociado con la institución',
    'email_already_associated' => 'El correo electrónico :email ya está asociado a otro usuario',
    'email_set_as_primary' => 'El correo electrónico :email se ha establecido como correo principal',
    'email_set_as_secondary' => 'El correo electrónico :email se ha establecido como correo secundario',
    'active_incident' => 'Ya existe un incidente activo en la institución',
    'already_reported' => 'Ya existe un reporte de usuario activo en el incidente',
    'user_not_brigadier' => 'El usuario no es un brigadista',
    'not_active_incident' => 'No hay un incidente activo en la institución',
    'already_assigned_incident' => 'El usuario ya está asignado a un punto de encuentro en el mismo incidente',
    'already_assigned' => 'El usuario ya está asignado a un punto de encuentro fijo',
    'brigadier_has_meet_points' => 'Hay brigadistas que tienen puntos de encuentro asociados',
    'not_logged' => 'No hay sesión iniciada',
    'user_not_active' => 'El usuario no está activo',
    'not_deleted' => 'No se pudo eliminar el/La :Model',
    'incident_closed' => 'El incidente ha finalizado',
    'already_resolved' => 'El reporte de usuario ya está resuelto',

    /** 401 HTTP status code messages */
    'unauthorized' => 'No autorizado',

    /** 403 HTTP status code messages */


    /** 404 HTTP status code messages */
    'not_found' => ':Model no encontrado',
    'not_found_in_institution' => ':Model no encontrado en la institución',
    'not_found_in_zone' => ':Model no encontrado en la zona',
    'not_found_in_risk_situation' => ':Model no encontrado en la situación de riesgo',
    'not_found_in_incident' => ':Model no encontrado en el incidente',

    /** 422 HTTP status code messages */
    'already_exist' => ':Model ya existe en la institución',
    'required' => ':Attribute e requerido',
    'not_valid' => 'El :model no es válido',
    'attribute_properties' => 'El :attribute debe tener las siguientes propiedades: :values',
    'attribute_at_least_one' => 'El :attribute debe tener al menos un componente',
    'attribute_value' => 'El :attribute debe tener :value',
    'email_domain' => 'El dominio del correo electrónico debe ser autonoma.edu.co',
    'user_is_administrator' => 'El usuario con el correo electrónico :email es un administrador',
    'brigadier_assigned' => 'El usuario con el correo electrónico :email tiene punto(s) de encuentro asignados',
    'photo_mimetypes' => 'El campo foto debe ser de alguno de los siguientes tipos de imagen: jpeg, png, webp, svg, heic, heif',
    'cannot_change_state' => 'No se puede cambiar el estado del reporte de usuario',
    'cannot_resolve' => 'El reporte de usuario no puede ser resuelto porque no está en riesgo',
];
