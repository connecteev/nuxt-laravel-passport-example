<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\Admin\TagResource;
use App\Tag;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class TagsApiController extends Controller
{
    use MediaUploadingTrait;

    // public function index()
    // {
    //     $returnedTags =
    //         Tag::where([
    //             ['active', '=', '1'],
    //         ])
    //         //->select('id', 'name', 'slug', 'is_featured', 'featured_order', 'is_popular', 'popular_order', 'created_by_user_id')
    //         ->get();
    //     return new TagResource($returnedTags);
    // }

    public function index()
    {
        // $user = Auth::user();
        // return $user;

        abort_if(Gate::denies('tag_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // return new TagResource(Tag::with(['users', 'posts'])->get());
        return new TagResource(Tag::with(['users'])->get());
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->all());
        $tag->users()->sync($request->input('users', []));
        $tag->posts()->sync($request->input('posts', []));

        // if ($request->input('tag_logo_image', false)) {
        //     $tag->addMedia(storage_path('tmp/uploads/' . $request->input('tag_logo_image')))->toMediaCollection('tag_logo_image');
        // }

        // if ($request->input('tag_background_image', false)) {
        //     $tag->addMedia(storage_path('tmp/uploads/' . $request->input('tag_background_image')))->toMediaCollection('tag_background_image');
        // }

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tag $tag)
    {
        abort_if(Gate::denies('tag_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new TagResource($tag->load(['users', 'posts']));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->all());
        $tag->users()->sync($request->input('users', []));
        $tag->posts()->sync($request->input('posts', []));

        // if ($request->input('tag_logo_image', false)) {
        //     if (!$tag->tag_logo_image || $request->input('tag_logo_image') !== $tag->tag_logo_image->file_name) {
        //         $tag->addMedia(storage_path('tmp/uploads/' . $request->input('tag_logo_image')))->toMediaCollection('tag_logo_image');
        //     }
        // } elseif ($tag->tag_logo_image) {
        //     $tag->tag_logo_image->delete();
        // }

        // if ($request->input('tag_background_image', false)) {
        //     if (!$tag->tag_background_image || $request->input('tag_background_image') !== $tag->tag_background_image->file_name) {
        //         $tag->addMedia(storage_path('tmp/uploads/' . $request->input('tag_background_image')))->toMediaCollection('tag_background_image');
        //     }
        // } elseif ($tag->tag_background_image) {
        //     $tag->tag_background_image->delete();
        // }

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Tag $tag)
    {
        abort_if(Gate::denies('tag_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tag->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
