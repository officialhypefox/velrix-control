<?php

namespace App\Http\Requests\Api\Client\Account;

use App\Exceptions\Http\Base\InvalidPasswordProvidedException;
use App\Http\Requests\Api\Client\ClientApiRequest;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;

// current_password is checked in authorize() rather than validated, so it has no rule to comment.
#[BodyParameter('current_password', description: 'The account\'s current password, which must be given to change it.', required: true, type: 'string')]
#[BodyParameter('password_confirmation', description: 'Repeat of the new password.', required: true)]
class UpdatePasswordRequest extends ClientApiRequest
{
    /**
     * @throws InvalidPasswordProvidedException
     */
    public function authorize(): bool
    {
        if (!parent::authorize()) {
            return false;
        }

        $hasher = Container::getInstance()->make(Hasher::class);

        // Verify password matches when changing password or email.
        throw_unless($hasher->check($this->input('current_password'), $this->user()->password), new InvalidPasswordProvidedException(trans('validation.internal.invalid_password')));

        // Velrix owns this account's identity: it creates the panel user and
        // pushes username/email changes down from the dashboard. Upstream returns
        // a bare false here, which renders as "This action is unauthorized" and
        // tells the user nothing about where the field actually lives.
        throw_if($this->user()->is_managed_externally, new AuthorizationException(
            trans('profile.managed_externally_error', ['field' => trans('profile.password')])
        ));

        return true;
    }

    public function rules(): array
    {
        return [
            /** New password. */
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ];
    }
}
