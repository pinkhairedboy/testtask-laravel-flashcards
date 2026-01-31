<?php

namespace Modules\Flashcard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevertFlashcardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'audit_id' => 'required|integer',
        ];
    }
}
