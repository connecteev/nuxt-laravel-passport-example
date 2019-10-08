<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StorePodcastEpisodeRequest;
use App\Http\Requests\UpdatePodcastEpisodeRequest;
use App\Http\Resources\Admin\PodcastEpisodeResource;
use App\PodcastEpisode;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PodcastEpisodesApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        //return new PodcastEpisodeResource(PodcastEpisode::with(['podcast_episode_speaker', 'podcast_episode_topic', 'podcast_episode_companies', 'podcast_episode_audiences', 'podcast_episode_industry_types', 'podcast_episode_tags'])->get());
        return new PodcastEpisodeResource(PodcastEpisode::where('status', 'published')->with(['podcast_episode_speaker', 'podcast_episode_topic', 'podcast_episode_companies', 'podcast_episode_audiences', 'podcast_episode_industry_types', 'podcast_episode_tags'])->get());
    }

    /*
    public function show(PodcastEpisode $podcastEpisode)
    {
        return new PodcastEpisodeResource($podcastEpisode->load(['podcast_episode_speaker', 'podcast_episode_topic', 'podcast_episode_companies', 'podcast_episode_audiences', 'podcast_episode_industry_types', 'podcast_episode_tags']));
    }
    */

    // Note: We disabled route-model binding: public function show(PodcastEpisode $podcastEpisode)
    // so that we can load the model by episode_slug
    // Note: If we use getRouteKeyName() for implicit binding in the model, to load by slug instead of id, it will make the api/v1/ for the backend fail.
    // See https://laravel.com/docs/master/routing#implicit-binding
    public function show($param)
    {
        $podcastEpisode = PodcastEpisode::where('episode_slug', $param)
                //->orWhere('id', $param) // to load by episode_slug OR id
                ->firstOrFail();

        if ($podcastEpisode['status'] != 'published') {
            // return an empty json response
            return response()->json(new \stdClass());
        }

        return new PodcastEpisodeResource($podcastEpisode->load(['podcast_episode_speaker', 'podcast_episode_topic', 'podcast_episode_audiences', 'podcast_episode_industry_types', 'podcast_episode_tags', 'podcastEpisodeSnippets', 'podcastEpisodeResources', 'podcastEpisodeTakeaways',
            'podcast_episode_companies' => function ($query) {
                // Note: podcast_episode_companies hasmany podcast_episode_business_types
                $query->with('podcast_episode_business_types');
            }
        ]));
    }
}
