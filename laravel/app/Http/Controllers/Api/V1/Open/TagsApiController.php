<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\Admin\TagResource;
use App\Tag;
use App\Post;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Carbon\Carbon;

class TagsApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        $returnedTags =
            Tag::where([
                ['active', '=', '1'],
            ])
            //->select('id', 'name', 'slug', 'is_featured', 'featured_order', 'is_popular', 'popular_order', 'created_by_user_id')
            ->with([
                'created_by_user' => function ($query) {
                    $query->where([
                        ['deleted_at', '=', NULL],
                    ]);
                },
            ])
            ->get();
        return new TagResource($returnedTags);
    }

    // Test URL: http://localhost:8000/api/v1/open/userTags
    public function userTags()
    {
        /*
        // MySQL query for getting user tags
            select t.id, t.name from tag_user tu
            join users u
            on tu.user_id = u.id
            join tags t
            on tu.tag_id = t.id
            where u.username="admin";
        */
        $userName = "admin"; // hardcode for now
        $returnedTags =
            Tag::where([
                ['active', '=', '1'],
            ])
            //->select('id', 'name', 'slug', 'is_featured', 'featured_order', 'is_popular', 'popular_order', 'created_by_user_id')
            ->whereHas('users', function ($query) use ($userName) {
                // Query the 'id' field in users table (for now), change to 'username' later
                $query
                    ->where([
                        ['username', '=', $userName],
                    ]);
            })
            ->get();
        return new TagResource($returnedTags);
    }

    // Test URL: http://localhost:8000/api/v1/open/tags/help
    // Note: We disabled route-model binding: public function show(Tag $tag)
    // so that we can load the model by slug
    // Note: If we use getRouteKeyName() for implicit binding in the model, to load by slug instead of id, it will make the api/v1/ for the backend fail.
    // See https://laravel.com/docs/master/routing#implicit-binding
    public function show($param)
    {
        $returnedTag =
            Tag::where([
                ['active', '=', '1'],
                ['slug', '=', $param],
            ])
            //->orWhere('id', $param) // to load by slug OR id
            //->select('id', 'name', 'slug', 'is_featured', 'featured_order', 'is_popular', 'popular_order', 'created_by_user_id')
            ->firstOrFail();

        $tagId = $returnedTag['id'];
        $numPosts = \DB::select('select count(*) as count from post_tag pt join tags t on pt.tag_id=t.id where pt.tag_id = ? and t.active=1', [$tagId]);
        $numPosts = $numPosts[0]->count;

        /*
            // MySQL query for getting published posts that are tagged with ALL of the tags in the specified array (of tag slugs)
            // Note: In the results, only look at the rows with count == length of the tags array (which is 2, in this case)
            select pt.post_id, count(*) as count, p.title
            from post_tag pt
            join tags t
            on pt.tag_id=t.id
            join posts p
            on pt.post_id=p.id
            where pt.tag_id in (select id from tags where slug in ("discuss", "help"))
            and p.status="published"
            and p.is_removed_by_admin=0
            group by pt.post_id order by count desc;
        */
        // Posts that are tagged with BOTH the tag slug and '#discuss'

        $tagSlugsArray = ["discuss"];
        if ($param != "discuss") {
            // if we dont check for uniqueness, the count will get thrown off and we'll get no results back
            $tagSlugsArray[] = $param;
        }
        // Only return posts with: status="published" AND is_removed_by_admin=false
        $discussionPostsTaggedWithTagSlug =
            Post::where([
                ['status', '=', 'published'],
                ['is_removed_by_admin', '=', '0'],
            ])
            ->select('id', 'title', 'slug', 'user_id', 'featured_image_caption', 'reading_time_minutes', 'created_at', 'updated_at')
            ->with([
                // Note: must also return the user_id field above, otherwise the user object returned will be null
                'user' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('id', 'username');
                },
                // // To include tags in the results
                // // TODO: filter out tags with restricted=1?
                // 'tags' => function ($query) {
                //     $query
                //         // filter out tags with active=0 or deleted_at=not NULL
                //         ->where([
                //             ['active', '=', '1'],
                //             ['deleted_at', '=', NULL],
                //         ])
                //         ->select('name', 'slug');
                // },
            ])
            ->whereHas('tags', function ($query) use ($tagSlugsArray) {
                // Query the slug field in tags table
                $query->whereIn('slug', $tagSlugsArray);
            }, '=', count($tagSlugsArray))
            ->orderBy('created_at', 'DESC')
            ->take(5)
            ->get();

        /*
            // TODO: Add logic for #likes and #comments

            // MySQL query for getting trending posts that are tagged this tag Slug
            select pt.post_id, p.title, p.created_at
            from post_tag pt
            join tags t
            on pt.tag_id=t.id
            join posts p
            on pt.post_id=p.id
            where pt.tag_id in (select id from tags where slug in ("help"))
            and p.status="published"
            and p.is_removed_by_admin=0
            and p.created_at >= DATE_SUB(NOW(), INTERVAL 10 DAY) AND NOW() -- cutoff time
            order by p.created_at desc
            ;
        */
        $trendingPostsCutoffTimedays = 9999; // setting this to a large value means no cutoff time
        $tagSlugsArray = [$param];

        $trendingPostsInTagSlug =
            Post::where([
                // Only return posts with: status="published" AND is_removed_by_admin=false
                ['status', '=', 'published'],
                ['is_removed_by_admin', '=', '0'],
                ['created_at', '>=', Carbon::now()->subDays($trendingPostsCutoffTimedays)],
            ])
            // Note: if you dont also return user_id here, the user object returned below will be null
            ->select('id', 'title', 'slug', 'user_id', 'featured_image_caption', 'reading_time_minutes', 'created_at', 'updated_at')
            ->with([
                // Note: MUST also return the user_id field above
                'user' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('id', 'username');
                },
                // // To include tags in the results
                // // TODO: filter out tags with restricted=1?
                // 'tags' => function ($query) {
                //     $query
                //         // filter out tags with active=0 or deleted_at=not NULL
                //         ->where([
                //             ['active', '=', '1'],
                //             ['deleted_at', '=', NULL],
                //         ])
                //         ->select('name', 'slug');
                // },
            ])
            ->whereHas('tags', function ($query) use ($tagSlugsArray) {
                // Query the slug field in tags table
                $query->whereIn('slug', $tagSlugsArray);
            }, '=', count($tagSlugsArray))
            ->orderBy('created_at', 'DESC')
            ->take(25)
            ->get();

        $returnedData = [];
        $returnedData['meta'] = $returnedTag;
        $returnedData['numPosts'] = $numPosts;
        $returnedData['discussionPostsTaggedWithTagSlug'] = $discussionPostsTaggedWithTagSlug;
        $returnedData['trendingPostsInTagSlug'] = $trendingPostsInTagSlug;
        return new TagResource($returnedData);
    }
}
