<?php

namespace App\Http\Requests;

use App\Tag;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreTagRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('tag_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'           => [
                'max:255',
                'required',
                'unique:tags',
            ],
            'slug'           => [
                'max:255',
                'required',
                'unique:tags',
            ],
            'featured_order' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'popular_order'  => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'tag_bg_color'   => [
                'max:10',
                'required',
            ],
            'tag_fg_color'   => [
                'max:10',
                'required',
            ],
            'cta_title'      => [
                'max:255',
            ],
            'cta_subtitle'   => [
                'max:255',
            ],
            'users.*'        => [
                'integer',
            ],
            'users'          => [
                'array',
            ],
            'posts.*'        => [
                'integer',
            ],
            'posts'          => [
                'array',
            ],
        ];
    }
}
