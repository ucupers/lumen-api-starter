<?php

namespace App;

use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Accounts
{
    /**
     * Get list of paginated users.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function getUsers(Request $request): array
    {
        $users = User::filter($request)->paginate($request->get('per_page', 20));

        return fractal($users, new UserTransformer())->toArray();
    }

    /**
     * Get a user by ID.
     *
     * @param  int  $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return array
     */
    public function getUserById(int $id): array
    {
        $user = User::findOrFail($id);

        return fractal($user, new UserTransformer())->toArray();
    }

    /**
     * Store a new user.
     *
     * @param  array  $attrs
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return array
     */
    public function storeUser(array $attrs): array
    {
        $user = new User($attrs);

        if (!$user->isValidFor('CREATE')) {
            throw new ValidationException($user->validator());
        }

        $user->save();

        event(new UserCreated($user));

        return fractal($user, new UserTransformer())->toArray();
    }

    /**
     * Update a user by ID.
     *
     * @param  int  $id
     * @param  array  $attrs
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return array
     */
    public function updateUserById(int $id, array $attrs): array
    {
        $user = User::findOrFail($id);

        $user->fill($attrs);

        $changes = $user->getDirty();

        if (!$user->isValidFor('UPDATE')) {
            throw new ValidationException($user->validator());
        }

        $user->save();

        event(new UserUpdated($user, $changes));

        return fractal($user, new UserTransformer())->toArray();
    }

    /**
     * Delete a user by ID.
     *
     * @param  int  $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return bool
     */
    public function deleteUserById(int $id): bool
    {
        $user = User::findOrFail($id);

        if (!$user->delete()) {
            return false;
        }

        return true;
    }
}
