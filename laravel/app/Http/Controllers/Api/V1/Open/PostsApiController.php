<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\Admin\PostResource;
use App\User;
use App\Post;
use App\Tag;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use App\Http\Resources\Admin\TagResource; // for TagStreams

use App\Http\Controllers\Traits\PostCommentsTrait;
use App\Http\Controllers\Traits\UserProfilesTrait;

class PostsApiController extends Controller
{
    use MediaUploadingTrait;
    use PostCommentsTrait;
    use UserProfilesTrait;

    // Test URL: http://localhost:8000/api/v1/open/posts?page=1&viewBy=week // possible values: 'feed', 'week', 'month', 'year', 'alltime', 'latest'
    // Test URL (posts with one tag): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help
    // Test URL: http://localhost:8000/api/v1/open/posts?page=1&username=admin // For all posts by a user (on the user's profile page)
    // Test URL (multiple tags, find posts matching ALL tags): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help:leadership:tutorial
    // Test URL (multiple tags, find posts matching ANY tag): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help|leadership|tutorial
    public function index(Request $request)
    {
        // abort_if(Gate::denies('post_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $resultsPerPage = 15;
        $maxPages = 20000000;

        // Simpler than using limit()->get() or take(10)->get(), which dont work with paginate()
        // See the hairier alternative here: https://laracasts.com/discuss/channels/eloquent/how-to-limit-pagination-to-x-itemspages?page=1#reply=354780
        if ($request->page > $maxPages) {
            return [];
        }

        // Only return posts with: status="published" AND is_removed_by_admin=false
        $returnedPosts =
            Post::where([
                ['status', '=', 'published'],
                ['is_removed_by_admin', '=', '0'],
                ['deleted_at', '=', NULL],
            ])
            ->select('id', 'title', 'slug', 'user_id', 'featured_image_caption', 'reading_time_minutes', 'num_comments', 'num_likes', 'created_at', 'updated_at')
            ->with([
                // Note: must also include the user_id field above, otherwise the user object returned will be null
                'user' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('id', 'username');
                },
                'user.profiles' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        // if you select specific fields, be sure to include the 'user_id' field, otherwise this wont work
                        ->select('id', 'full_name', 'user_id', 'bio_headline', 'bio_description');
                },

                // TODO: filter out tags with restricted=1?
                'tags' => function ($query) {
                    $query
                        ->where([
                            ['active', '=', '1'],
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('name', 'slug'); // 'tag_bg_color', 'tag_fg_color'
                },
            ]);

        // Test URL: http://localhost:8000/api/v1/open/posts?page=1&viewBy=week
        $viewBy = $request->viewBy; // possible values: 'feed', 'week', 'month', 'year', 'alltime', 'latest'

        // Test URL (posts with one tag): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help
        // Test URL (multiple tags, find posts matching ALL tags): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help:leadership:tutorial
        // Test URL (multiple tags, find posts matching ANY tag): http://localhost:8000/api/v1/open/posts?page=1&tagSlugs=help|leadership|tutorial
        $tagSlugs = $request->tagSlugs; // examples: help or help:leadership:tutorial or help|leadership|tutorial

        if ($tagSlugs) {
            $tagSeparatorAll = ':';
            $tagSeparatorAny = '|';
            $andSeparatorFoundInTags = false;
            $orSeparatorFoundInTags = false;
            $checkForAllTags = false;
            if (strpos($tagSlugs, $tagSeparatorAll) !== false) {
                $andSeparatorFoundInTags = true;
                $tagSlugsArray = $tagSlugs ? explode($tagSeparatorAll, $tagSlugs) : null;
                $checkForAllTags = true;
            }
            if (strpos($tagSlugs, $tagSeparatorAny) !== false) {
                $orSeparatorFoundInTags = true;
                $tagSlugsArray = $tagSlugs ? explode($tagSeparatorAny, $tagSlugs) : null;
                $checkForAllTags = false;
            }
            if ($andSeparatorFoundInTags && $orSeparatorFoundInTags) {
                return ["error" => "Error: The tagSlugs parameter can't have both AND and OR separators"];
            }
            if (!$andSeparatorFoundInTags && !$orSeparatorFoundInTags) {
                // A single tag was passed in as a parameter
                $tagSlugsArray = [$tagSlugs];
                $checkForAllTags = false;
            }

            // For posts on the tag page
            if ($tagSlugsArray && (sizeof($tagSlugsArray) > 0)) {
                if ($checkForAllTags) {
                    // Only return posts that are tagged with ALL of the tagSlugs in the tagSlugsArray
                    /*
                    // MySQL query for getting published posts that are tagged with ALL of the tags in the specified array (of tag slugs)
                    // Note: In the results, only look at the rows with count == length of the tags array (which is 2, in this case)
                        select pt.post_id, count(*) as count, p.title
                        from post_tag pt
                        join tags t
                        on pt.tag_id=t.id
                        join posts p
                        on pt.post_id=p.id
                        where pt.tag_id in (select id from tags where slug in ("leadership", "tutorial"))
                        and p.status="published"
                        and p.is_removed_by_admin=0
                        group by pt.post_id order by count desc;
                    */
                    $returnedPosts = $returnedPosts->whereHas('tags', function ($query) use ($tagSlugsArray) {
                        // Query the slug field in tags table
                        $query->whereIn('slug', $tagSlugsArray);
                    }, '=', count($tagSlugsArray));
                } else {
                    // Return posts that are tagged with ANY of the (one or more) tagSlugs in the tagSlugsArray
                    /*
                    // MySQL query for getting published posts that are tagged with ANY of the tags in the specified array (of tag slugs)
                        select distinct pt.post_id, p.title
                        from post_tag pt
                        join tags t
                        on pt.tag_id=t.id
                        join posts p
                        on pt.post_id=p.id
                        where pt.tag_id in (select id from tags where slug in ("leadership", "tutorial"))
                        and p.status="published"
                        and p.is_removed_by_admin=0;
                    */
                    $returnedPosts = $returnedPosts->whereHas('tags', function ($query) use ($tagSlugsArray) {
                        // Query the slug field in tags table
                        $query->whereIn('slug', $tagSlugsArray);
                    });
                }
            }
        }

        // For all posts by a user (on the user's profile page)
        // Test URL: http://localhost:8000/api/v1/open/posts?page=1&username=admin
        $userName = $request->username; // example: admin
        if ($userName) {
            $returnedPosts = $returnedPosts->whereHas('user', function ($query) use ($userName) {
                $query
                    ->where([
                        ['deleted_at', '=', NULL],
                        ['username', '=', $userName],
                    ]);
            });
        }

        $returnedPosts = $this->filterResults($returnedPosts, $viewBy);
        $returnedPosts = $this->sortResults($returnedPosts, $viewBy);

        // Dont use ->take(3) or ->get()
        //$returnedPosts = $returnedPosts->take(100)->get();
        $returnedPosts = $returnedPosts->paginate($resultsPerPage);

        foreach ($returnedPosts as $returnedPost) {
            // This flattens the profile array and brings it one level below the user object
            // It also removes the email address from the returned data, if it is not public
            $returnedPost->user = $this->cleanupUserProfileData($returnedPost->user);
        }

        return new PostResource($returnedPosts);
    }

    // possible values: 'feed', 'week', 'month', 'year', 'alltime', 'latest'
    private function filterResults($results, $viewBy)
    {
        $dateTimeLimit = Carbon::now();
        $dateTimeLimit->setTimezone('America/Los_Angeles');
        switch ($viewBy) {
            case 'week':
                $dateTimeLimit->subtract(1, 'weeks');
                break;
            case 'month':
                $dateTimeLimit->subtract(1, 'months');
                break;
            case 'year':
                $dateTimeLimit->subtract(1, 'years');
                break;
            case 'alltime':
            case 'latest':
            case 'feed':
            default:
                $dateTimeLimit = Carbon::create(1999, 12, 31, 24);
                break;
        }
        $results = $results->where([
            ['created_at', '>=', $dateTimeLimit],
        ]);
        return $results;
    }

    // possible values: 'feed', 'week', 'month', 'year', 'alltime', 'latest'
    private function sortResults($results, $viewBy)
    {
        switch ($viewBy) {
            case 'latest':
                return $results->orderBy('created_at', 'DESC');
                break;
            case 'week':
            case 'month':
            case 'year':
            case 'alltime':
                return $results->orderByRaw("SUM( (IFNULL(num_comments, 0)*0.5) + (IFNULL(num_likes, 0)*0.4) + (CHAR_LENGTH(body)*0.1) ) DESC")->groupBy('id');
                break;
            case 'feed':
            default:
                // TODO: sort results by a relevance algorithm
                /*
                    Parameters for algorithm:
                    Num Comments
                    Num Likes
                    Date Created (Recency of Post)
                    Date Updated (Recency of Post)
                    Date last comment (Recency of Comments)
                    Date last like / reaction (Recency of Likes / Reactions)
                    Author (user) Karma score (1-100)
                    Length of Post?
                    Fast Rising: Number of likes / comments in past X <hours / days>

                    SELECT `id`, `title`, `slug`, `user_id`, `featured_image_caption`, `reading_time_minutes`, `num_comments`, `num_likes`, `created_at`, `updated_at`
                    FROM `posts`
                    WHERE (`status` = 'published' and `is_removed_by_admin` = '0')
                    AND (`created_at` >= "2000-01-01T00:00:00.000000Z")
                    AND `posts`.`deleted_at` is null
                    GROUP BY `id`
                    ORDER BY SUM( (IFNULL(num_comments, 0)*0.5) + (IFNULL(num_likes, 0)*0.4) + (CHAR_LENGTH(body)*0.1) ) / (UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(created_at)) DESC
                    LIMIT 15 OFFSET 0;

                    Note: This doesn't do a join with the comments / reactions tables, and so this algorithm doesn't consider
                    * Date of last comment (Recency of Comments, from comments table, which contains post_id)
                    * Date of last like / reaction (Recency of Likes / Reactions, from reactions table, which contains post_id))

                    Note: In future, consider a cron if this query becomes slow.
                    See https://laracasts.com/discuss/channels/eloquent/need-to-order-posts-based-on-a-composite-score?page=1#reply=540775
                    Note: Can also use bindings like shown here https://laracasts.com/discuss/channels/laravel/sql-native-to-query-builder?page=1#reply=421632
                */

                // return $results->orderBy('num_comments', 'DESC');
                return $results->orderByRaw("SUM( (IFNULL(num_comments, 0)*0.5) + (IFNULL(num_likes, 0)*0.4) + (CHAR_LENGTH(body)*0.1) ) / (UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(created_at)) DESC")->groupBy('id');
                break;
        }
        return $results;
    }

    // Test URL: http://localhost:8000/api/v1/open/tagStreams
    public function tagStreams(Request $request)
    {
        $popularTags =
            Tag::where([
                ['active', '=', '1'],
                ['is_popular', '=', '1'],
                ['deleted_at', '=', NULL],
            ])
            ->select('id', 'name', 'slug', 'is_popular', 'popular_order', 'cta_title', 'cta_subtitle', 'tag_bg_color', 'tag_fg_color')
            ->with([])
            ->orderBy('popular_order', 'asc')
            ->get();

        if (sizeof($popularTags) <= 0) {
            return [
                "error" => "Couldn't retrieve popular tags"
            ];
        }

        $featuredTags =
            Tag::where([
                ['active', '=', '1'],
                ['is_featured', '=', '1'],
                ['deleted_at', '=', NULL],
            ])
            ->select('id', 'name', 'slug', 'is_featured', 'featured_order', 'cta_title', 'cta_subtitle', 'tag_bg_color', 'tag_fg_color')
            ->with([])
            ->orderBy('featured_order', 'ASC')
            ->get();

        if (sizeof($featuredTags) <= 0) {
            return [
                "error" => "Couldn't retrieve featured tags"
            ];
        }

        foreach ($featuredTags as $featuredTag) {
            $tagSlug = $featuredTag['slug'];

            $returnedPosts = [];
            // Only return posts with: status="published" AND is_removed_by_admin=false
            $returnedPosts = Post::where([
                ['status', '=', 'published'],
                ['is_removed_by_admin', '=', '0'],
                ['deleted_at', '=', NULL],
            ])
                ->select('id', 'title', 'slug', 'user_id', 'num_comments', 'num_likes', 'created_at', 'updated_at')
                ->whereHas('tags', function ($query) use ($tagSlug) {
                    // Query the slug field in tags table
                    $query->where('slug', $tagSlug);
                })
                ->with([
                    // Note: MUST also return the user_id field in 'select', otherwise the user object returned below will be null
                    'user' => function ($query) {
                        $query
                            ->where([
                                ['deleted_at', '=', NULL],
                            ])
                            ->select('id', 'username');
                    },
                    /*
                    // TODO: filter out tags with restricted=1?
                    'tags' => function ($query) {
                        $query
                        ->where([
                            ['active', '=', '1'],
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('name', 'slug'); // 'tag_bg_color', 'tag_fg_color'
                    },
                    */
                ])
                /*
                // realtime count of number of direct child comments (does not count the nested comments)
                ->withCount([
                    'comments' => function ($query) {
                        $query
                            ->where([
                                ['is_removed', '=', '0'],
                                ['deleted_at', '=', NULL],
                            ]);
                    },
                ])
                */
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $featuredTag['posts'] = $returnedPosts;
        }

        $returnedData = [];
        $returnedData['popular_tags'] = $popularTags;
        $returnedData['featured_tags'] = $featuredTags;
        return new TagResource($returnedData);
    }

    // Test URL: http://localhost:8000/api/v1/open/posts/authorUsername/postSlug
    // Test URL: http://localhost:8000/api/v1/open/posts/authorUsername/postSlug?comments               // to retrieve post with comments, 'default' number of parent-level comments
    // Test URL: http://localhost:8000/api/v1/open/posts/authorUsername/postSlug?comments=<anything>    // to retrieve post with comments, 'default' number of parent-level comments
    // Test URL: http://localhost:8000/api/v1/open/posts/authorUsername/postSlug?comments=all           // to retrieve post with comments, 'all' number of parent-level comments
    // Note: We disabled route-model binding: public function show(Post $tag)
    // so that we can load the model by slug
    // Note: If we use getRouteKeyName() for implicit binding in the model, to load by slug instead of id, it will make the api/v1/ for the backend fail.
    // See https://laravel.com/docs/master/routing#implicit-binding
    public function show(Request $request, $authorUsername, $postSlug)
    {
        $checkValidPostExists = $this->checkForValidPost($authorUsername, $postSlug);
        if (!$checkValidPostExists["success"]) {
            return ["error" => $checkValidPostExists["error"]];
        }

        $post =
            Post::select('id', 'title', 'slug', 'description', 'body', 'user_id', 'featured_image_caption', 'is_pinned', 'reading_time_minutes', 'canonical_url', 'status', 'num_comments', 'num_likes', 'created_at', 'updated_at')
            ->where([
                ['status', '=', 'published'],
                ['is_removed_by_admin', '=', '0'],
                ['deleted_at', '=', NULL],
                ['slug', '=', $postSlug],
            ])
            //->orWhere('id', $postSlug) // to load by slug OR id
            ->with([
                'user' => function ($query) {
                    // Note: To get this related data, you must also return the user_id field above
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('id', 'username');
                },
                'user.profiles' => function ($query) {
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                        ])
                        // if you select specific fields, be sure to include the 'user_id' field, otherwise this wont work
                        ->select('id', 'full_name', 'user_id', 'bio_headline', 'bio_description');
                },
                'tags' => function ($query) {
                    $query
                        ->where([
                            ['active', '=', '1'],
                            ['deleted_at', '=', NULL],
                        ])
                        ->select('name', 'slug', 'tag_bg_color', 'tag_fg_color');
                },
            ])
            /*
            // NOT WORKING CONSISTENTLY. ENABLE LATER
            ->withCount([
                //number of top-level (parent) comments on the (main) post
                'comments as post_top_level_comments_count' => function ($query) {
                    $query
                        ->where([
                            ['is_removed', '=', '0'],
                            ['deleted_at', '=', NULL],
                        ]);
                },
                //number of reactions on the (main) post, not including counts of reactions on any comments
                'reactions as post_reactions_count' => function ($query) {
                    $query
                        ->where([
                            ['is_removed', '=', '0'],
                            ['deleted_at', '=', NULL],
                            ['reaction_type', '=', 'like'],
                        ]);
                },
            ])
            */;

        $getComments = $request->has('comments') ? true : false;
        if ($getComments) {
            // Retrieve comments along with the post data
            switch ($request->comments) {
                case 'all':
                    $max_top_level_comments = 999999;
                    break;
                default:
                    $max_top_level_comments = 10;
                    break;
            }

            $post = $this->withNestedCommentsAndReactions($post, $max_top_level_comments);
        }

        //$post = $post->take(1)->get();
        $post = $post->firstOrFail();

        // This flattens the profile array and brings it one level below the user object
        // It also removes the email address from the returned data, if it is not public
        $post->user = $this->cleanupUserProfileData($post->user);

        return new PostResource($post);
    }
}
