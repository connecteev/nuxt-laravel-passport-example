<?php

namespace App\Http\Requests;

use App\PodcastEpisodeTag;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeTagRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_tag_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'max:255',
                'required',
                'unique:podcast_episode_tags',
            ],
            'slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_tags',
            ],
        ];
    }
}
