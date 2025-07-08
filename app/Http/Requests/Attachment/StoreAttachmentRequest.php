<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    public ?Model $attachable = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->input('attachable_type') && $this->input('attachable_id')) {
            $modelClass = match ($this->input('attachable_type')) {
                'project' => \App\Models\Project::class,
                'task' => \App\Models\Task::class,
                'comment' => \App\Models\Comment::class,
                default => null,
            };

            if (!$modelClass)
                return false;

            $this->attachable = $modelClass::find($this->input('attachable_id'));

            return $this->attachable && $this->user()->can('view', $this->attachable);
        }
        return false;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,png,pdf,doc,docx,zip'], // Max 10MB
            'attachable_id' => ['required', 'integer'],
            'attachable_type' => ['required', 'string', Rule::in(['project', 'task', 'comment'])],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'attachable_model' => $this->attachable,
        ]);
    }
}
