<?php

namespace App\Http\Requests;

use App\PodcastEpisodeSnippet;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPodcastEpisodeSnippetRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_snippet_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:podcast_episode_snippets,id',
        ];
    }
}
