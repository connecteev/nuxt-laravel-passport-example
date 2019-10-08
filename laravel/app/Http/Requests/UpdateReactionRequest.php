<?php

namespace App\Http\Requests;

use App\Reaction;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateReactionRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('reaction_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'user_id'       => [
                'required',
                'integer',
            ],
            'reaction_type' => [
                'required',
            ],
        ];
    }
}
