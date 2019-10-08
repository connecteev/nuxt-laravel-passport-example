<?php

namespace App\Http\Requests;

use App\Comment;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateCommentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('comment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'comment_hash' => [
                'required',
            ],
            'user_id'      => [
                'required',
                'integer',
            ],
            'body'         => [
                'required',
            ],
            'num_comments' => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'num_likes'    => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
        ];
    }
}
