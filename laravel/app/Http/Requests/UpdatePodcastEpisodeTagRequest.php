<?php

namespace App\Http\Requests;

use App\PodcastEpisodeTag;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdatePodcastEpisodeTagRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_tag_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'max:255',
                'required',
                'unique:podcast_episode_tags,name,' . request()->route('podcast_episode_tag')->id,
            ],
            'slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_tags,slug,' . request()->route('podcast_episode_tag')->id,
            ],
        ];
    }
}
