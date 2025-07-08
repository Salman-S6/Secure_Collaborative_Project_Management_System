<?php

namespace App\Http\Requests\Team;

use App\Enums\Roles\TeamRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('addMember', $this->route('team'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('team_user')->where('team_id', $this->route('team')->id),
            ],
            'role' => [
                'required',
                'string',
                Rule::enum(TeamRole::class),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === TeamRole::OWNER->value) {
                        $fail('The owner role cannot be assigned. To change the team owner, please contact the admin.');
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('role')) {
            $this->merge([
                'role' => TeamRole::MEMBER->value,
            ]);
        }
    }
}
