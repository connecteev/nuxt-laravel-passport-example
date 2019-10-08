<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\User;
use App\Profile;
use App\Post;
use App\Comment;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\Traits\UserProfilesTrait;

class UsersApiController extends Controller
{
    use UserProfilesTrait;

    // Test URL: http://localhost:8000/api/v1/open/users/kdefranco
    // Note: We disabled route-model binding: public function show(User $user)
    // so that we can load the model by slug
    // Note: If we use getRouteKeyName() for implicit binding in the model, to load by slug instead of id, it will make the api/v1/ for the backend fail.
    // See https://laravel.com/docs/master/routing#implicit-binding
    public function show($param)
    {
        $returnedUser =
            User::where([
                //['active', '=', '1'],
                ['deleted_at', '=', NULL],
                ['username', '=', $param],
            ])
            //->orWhere('id', $param) // to load by username OR id
            ->select('id', 'username', 'email', 'created_at')
            ->with([
                'profiles' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        // if you select specific fields, be sure to include the 'user_id' field, otherwise this wont work
                        // ->select('id', 'full_name', 'user_id')
                        ->first();
                },
                'social',
            ])
            ->withCount([
                'tags as num_tags_followed' => function ($query) {
                    $query
                        ->where([
                            ['active', '=', '1'],
                            ['deleted_at', '=', NULL],
                        ]);
                },
            ])
            ->firstOrFail();

        // This flattens the profile object and brings it at the same level as the user object
        // It also removes the email address from the returned data, if it is not public
        $returnedUser = $this->cleanupUserProfileData($returnedUser);

        $numPostsPublished = Post::where([
            ['status', '=', 'published'],
            ['is_removed_by_admin', '=', '0'],
            ['deleted_at', '=', NULL],
            ['user_id', '=', $returnedUser->id],
        ])->count();

        $numCommentsWritten = Comment::where([
            ['is_removed', '=', '0'],
            ['deleted_at', '=', NULL],
            ['user_id', '=', $returnedUser->id],
        ])->count();

        $userStats = [
            'numPostsPublished' => $numPostsPublished,
            'numCommentsWritten' => $numCommentsWritten,
            'numTagsFollowed' => $returnedUser->num_tags_followed,
        ];
        unset($returnedUser->num_tags_followed);

        $returnedUser->stats = $userStats;
        return new UserResource($returnedUser);
    }
}
