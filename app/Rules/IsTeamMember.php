<?php

namespace App\Rules;

use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class IsTeamMember implements ValidationRule
{
    /**
     * The team instance.
     *
     * @var \App\Models\Team
     */
    protected $team;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isMember = DB::table('team_user')
            ->where('team_id', $this->team->id)
            ->where('user_id', $value)
            ->exists();

        if (!$isMember) {
            $fail('The selected user is not a member of this team.');
        }
    }
}
