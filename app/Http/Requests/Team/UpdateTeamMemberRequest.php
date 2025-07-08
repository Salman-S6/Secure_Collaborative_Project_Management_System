<?php

namespace App\Http\Requests\Team;

use App\Enums\Roles\TeamRole;
use App\Rules\IsTeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('updateMember', $this->route('team'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::enum(TeamRole::class),
                Rule::notIn([TeamRole::OWNER->value]),
            ],
            'member_id' => [
                'required',
                'integer',
                new IsTeamMember($this->route('team')),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     * We are adding the route parameter 'member' to the validation data.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'member_id' => (int) $this->route('member')?->id,
        ]);
    }
}
