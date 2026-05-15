<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMultipleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'files'       => 'required|array|min:1',
            'files.*'     => 'required|file|max:20480',
            'upload_type' => 'required|in:image,file',
            'type'        => 'required|in:answer,profile-picture',
        ];

        if ($this->get('type') === 'answer') {
            $rules['answer_id'] = 'required|exists:answers,id';
        }

        return $rules;
    }
}
