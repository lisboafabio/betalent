<?php

namespace App\Http\Requests;

use App\Domain\User\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        if (is_string($targetUser)) {
            $targetUser = User::findOrFail($targetUser);
        }

        return $this->user()->can('update', $targetUser);
    }

    public function rules(): array
    {
        $targetUser = $this->route('user');
        if (is_string($targetUser)) {
            $targetUser = User::findOrFail($targetUser);
        }

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($targetUser->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::enum(UserRoleEnum::class)],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $currentUser = $this->user();
            $requestedRole = $this->input('role');
            $targetUser = $this->route('user');

            if ($requestedRole) {
                if ($currentUser->role === UserRoleEnum::USER->value && $requestedRole !== $currentUser->role) {
                    $validator->errors()->add('role', 'Users cannot change their own role.');
                }

                if ($currentUser->role === UserRoleEnum::MANAGER->value && $requestedRole === UserRoleEnum::ADMIN->value) {
                    $validator->errors()->add('role', 'Managers cannot assign Admin role.');
                }
            }
        });
    }
}
