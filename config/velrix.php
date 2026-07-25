<?php

return [
    // Shared secret with the Velrix app, used to verify signed single-sign-on
    // tokens (see App\Http\Controllers\Auth\VelrixSsoController). Must match
    // VELRIX_SSO_SECRET on the Velrix backend. SSO is disabled while empty.
    'sso_secret' => env('VELRIX_SSO_SECRET', ''),
];
