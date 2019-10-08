<?php

namespace App\Http\Requests;

use App\PodcastEpisode;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'season_num'                       => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'episode_num'                      => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'status'                           => [
                'required',
            ],
            'episode_title'                    => [
                'min:10',
                'max:255',
                'required',
                'unique:podcast_episodes',
            ],
            'episode_slug'                     => [
                'min:10',
                'max:255',
                'required',
                'unique:podcast_episodes',
            ],
            'podcast_episode_speaker_id'       => [
                'required',
                'integer',
            ],
            'podcast_episode_topic_id'         => [
                'required',
                'integer',
            ],
            'episode_recorded_on'              => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'episode_published_on'             => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'episode_download_url'             => [
                'max:2083',
            ],
            'episode_worksheet_url'            => [
                'max:2083',
            ],
            'episode_teaser_blurb'             => [
                'required',
            ],
            'episode_description_blurb'        => [
                'required',
            ],
            'podcast_episode_companies.*'      => [
                'integer',
            ],
            'podcast_episode_companies'        => [
                'array',
            ],
            'podcast_episode_audiences.*'      => [
                'integer',
            ],
            'podcast_episode_audiences'        => [
                'array',
            ],
            'podcast_episode_industry_types.*' => [
                'integer',
            ],
            'podcast_episode_industry_types'   => [
                'array',
            ],
            'podcast_episode_tags.*'           => [
                'integer',
            ],
            'podcast_episode_tags'             => [
                'array',
            ],
        ];
    }
}
