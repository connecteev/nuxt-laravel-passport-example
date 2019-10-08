<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\Admin\ProfileResource;
use App\Profile;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfilesApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('profile_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ProfileResource(Profile::with(['user'])->get());
    }

    public function store(StoreProfileRequest $request)
    {
        $profile = Profile::create($request->all());

        if ($request->input('profile_picture', false)) {
            $profile->addMedia(storage_path('tmp/uploads/' . $request->input('profile_picture')))->toMediaCollection('profile_picture');
        }

        return (new ProfileResource($profile))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Profile $profile)
    {
        abort_if(Gate::denies('profile_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ProfileResource($profile->load(['user']));
    }

    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        $profile->update($request->all());

        if ($request->input('profile_picture', false)) {
            if (!$profile->profile_picture || $request->input('profile_picture') !== $profile->profile_picture->file_name) {
                $profile->addMedia(storage_path('tmp/uploads/' . $request->input('profile_picture')))->toMediaCollection('profile_picture');
            }
        } elseif ($profile->profile_picture) {
            $profile->profile_picture->delete();
        }

        return (new ProfileResource($profile))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Profile $profile)
    {
        abort_if(Gate::denies('profile_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $profile->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
