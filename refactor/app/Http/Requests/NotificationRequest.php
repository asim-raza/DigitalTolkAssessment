<?php

namespace DTApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
        'jobid' => 'required|integer|exists:jobs,id'
        ];
    }

}