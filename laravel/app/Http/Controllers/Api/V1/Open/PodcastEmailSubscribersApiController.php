<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePodcastEmailSubscriberRequest;
use App\Http\Requests\UpdatePodcastEmailSubscriberRequest;
use App\Http\Resources\Admin\PodcastEmailSubscriberResource;
use App\PodcastEmailSubscriber;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PodcastEmailSubscribersApiController extends Controller
{
    public function store(StorePodcastEmailSubscriberRequest $request)
    {
        $podcastEmailSubscriber = PodcastEmailSubscriber::create($request->all());

        return (new PodcastEmailSubscriberResource($podcastEmailSubscriber))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
