<?php

namespace App\Http\Requests;

use App\PodcastEpisodeSpeaker;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeSpeakerRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_speaker_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'            => [
                'max:255',
                'required',
                'unique:podcast_episode_speakers',
            ],
            'bio'             => [
                'required',
            ],
            'website'         => [
                'max:2083',
            ],
            'linkedin_handle' => [
                'max:255',
            ],
            'twitter_handle'  => [
                'max:255',
            ],
            'facebook_handle' => [
                'max:255',
            ],
        ];
    }
}
