<?php

namespace App\Http\Requests;

use App\PodcastEpisodeCompany;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdatePodcastEpisodeCompanyRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_company_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'                             => [
                'max:255',
                'required',
                'unique:podcast_episode_companies,name,' . request()->route('podcast_episode_company')->id,
            ],
            'slug'                             => [
                'max:255',
                'required',
                'unique:podcast_episode_companies,slug,' . request()->route('podcast_episode_company')->id,
            ],
            'url'                              => [
                'max:255',
                'required',
                'unique:podcast_episode_companies,url,' . request()->route('podcast_episode_company')->id,
            ],
            'company_size'                     => [
                'required',
            ],
            'podcast_episode_business_types.*' => [
                'integer',
            ],
            'podcast_episode_business_types'   => [
                'array',
            ],
        ];
    }
}
