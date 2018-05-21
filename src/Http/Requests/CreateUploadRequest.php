<?php

namespace OneOffTech\TusUpload\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        
        return true;
        // dd($this->user());
        // return !is_null($this->user()) && $this->user()->can('upload-via-tus', $this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filename' => 'required|string|min:1',
            'filesize' => 'present|numeric|min:0',
            'filetype' => 'present|string',
        ];
    }
}
