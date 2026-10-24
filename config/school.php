<?php

return [
    /*
    | Row-level school isolation (App\Models\Scopes\SchoolScope).
    | Emergency switch only — set SCHOOL_SCOPE_ENABLED=false in .env to turn the
    | global scope and SchoolRule off if a screen misbehaves while you investigate.
    | Leave it true in production.
    */
    'scope_enabled' => env('SCHOOL_SCOPE_ENABLED', true),
];
