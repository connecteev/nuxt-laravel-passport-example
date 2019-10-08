<?php

namespace App\Http\Requests;

use App\PodcastEpisodeSnippet;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeSnippetRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_snippet_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'snippet'            => [
                'required',
            ],
            'timestamp'          => [
                'max:255',
                'required',
                'unique:podcast_episode_snippets',
            ],
            'podcast_episode_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
