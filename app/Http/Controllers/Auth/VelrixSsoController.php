<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Single sign-on from the Velrix app.
 *
 * Velrix-provisioned users never set a panel password, so a signed magic link is
 * the only way they reach the panel UI. Velrix signs a short-lived token
 * (HMAC-SHA256 over a base64url JSON payload using the shared VELRIX_SSO_SECRET);
 * we verify it, log the user into the web session, and land them on their server.
 *
 * Mirrors OAuthController::loginUser — the panel web guard is the standard
 * session guard, so auth()->guard()->login() is all that's needed.
 */
class VelrixSsoController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $secret = (string) config('velrix.sso_secret');
        abort_if($secret === '', 503, 'Single sign-on is not configured.');

        $token = (string) $request->query('token', '');
        [$body, $signature] = array_pad(explode('.', $token, 2), 2, '');
        abort_if($body === '' || $signature === '', 403, 'Invalid sign-on token.');

        // Constant-time signature check over the exact payload string Velrix signed.
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $body, $secret, true));
        abort_unless(hash_equals($expected, $signature), 403, 'Invalid sign-on signature.');

        $payload = json_decode($this->base64UrlDecode($body), true);
        abort_unless(is_array($payload), 403, 'Malformed sign-on token.');

        // Expiry is milliseconds since epoch (matches the Velrix signer).
        $exp = (int) ($payload['exp'] ?? 0);
        abort_if($exp < (int) round(microtime(true) * 1000), 403, 'This sign-on link has expired.');

        $user = User::find($payload['uid'] ?? null);
        abort_unless($user instanceof User, 404, 'Account not found.');

        auth()->guard()->login($user, true);

        // Land on the requested server if one was passed (short identifier), else
        // the panel home. A stale/removed server id just 404s inside the panel with
        // the user already signed in.
        $server = (string) ($payload['srv'] ?? '');

        return redirect($server !== '' ? "/server/{$server}" : '/');
    }

    private function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $b64 = strtr($value, '-_', '+/');
        $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);

        return (string) base64_decode($b64, true);
    }
}
