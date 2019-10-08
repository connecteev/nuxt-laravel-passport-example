<?php

namespace App\Http\Requests;

use App\PodcastEpisodeTopic;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeTopicRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_topic_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'topic_name' => [
                'max:255',
                'required',
                'unique:podcast_episode_topics',
            ],
            'topic_slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_topics',
            ],
        ];
    }
}
