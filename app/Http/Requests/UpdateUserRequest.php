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
        $targetUserId = $this->route('id');
        $targetUser = User::where('id', $targetUserId)->first();

        if (!$targetUser) {
            return true; // Let the controller handle 404
        }

        return $this->user()->can('update', $targetUser);
    }

    public function rules(): array
    {
        $targetUserId = $this->route('id');
        $targetUser = User::where('id', $targetUserId)->first();

        $id = $targetUser ? $targetUser->id : null;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::enum(UserRoleEnum::class)],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $currentUser = $this->user();
            $requestedRole = $this->input('role');
            $targetUserId = $this->route('id');
            $targetUser = User::where('id', $targetUserId)->first();

            if ($targetUser && $requestedRole) {
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
