<?php

namespace App\Http\Requests\Api\Client\Account;

use App\Exceptions\Http\Base\InvalidPasswordProvidedException;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;

class UpdateEmailRequest extends ClientApiRequest
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
        throw_unless($hasher->check($this->input('password'), $this->user()->password), new InvalidPasswordProvidedException(trans('validation.internal.invalid_password')));

        // Velrix owns this account's identity: it creates the panel user and
        // pushes username/email changes down from the dashboard. Upstream returns
        // a bare false here, which renders as "This action is unauthorized" and
        // tells the user nothing about where the field actually lives.
        throw_if($this->user()->is_managed_externally, new AuthorizationException(
            trans('profile.managed_externally_error', ['field' => trans('profile.email')])
        ));

        return true;
    }

    public function rules(): array
    {
        $rules = User::getRulesForUpdate($this->user());

        return ['email' => $rules['email']];
    }
}
