<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->commentable);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'commentable_id' => ['required', 'integer'],
            'commentable_type' => ['required', 'string', Rule::in(['project', 'task'])],
            'commentable' => ['required'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $modelClass = $this->input('commentable_type') === 'project'
            ? \App\Models\Project::class
            : \App\Models\Task::class;

        $commentable = $modelClass::find($this->input('commentable_id'));

        $this->merge([
            'commentable' => $commentable,
        ]);
    }
}
