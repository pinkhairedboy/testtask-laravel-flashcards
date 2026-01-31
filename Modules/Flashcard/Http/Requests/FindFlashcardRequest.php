<?php

namespace Modules\Flashcard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FindFlashcardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), ['id' => $this->route('id')]);
    }
}
