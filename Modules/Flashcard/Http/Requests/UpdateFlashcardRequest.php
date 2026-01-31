<?php

namespace Modules\Flashcard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashcardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
        ];
    }
}
