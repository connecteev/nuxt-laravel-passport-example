<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\Comment;
use App\Post;
use App\Tag;
use App\Reaction;
use App\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\Admin\CommentResource;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Http\Controllers\Traits\PostCommentsTrait;

class CommentsApiController extends Controller
{
    use PostCommentsTrait;

    /*
        -- SQL command to get recursive comments:
        -- Recursive hierarchy / tree of children of a parent with ID
        SET @PARENTID=1;
        SELECT  id, body, commentable_id, commentable_type
        FROM (select * from comments where commentable_type LIKE "%App%Comment%" AND is_removed=0 AND deleted_at IS NULL
        ORDER BY commentable_id ASC, id ASC) comments,
        (select @pv := @PARENTID) initialisation
        WHERE find_in_set(commentable_id, @pv) > 0
        AND @pv := concat(@pv, ',', id)
        ;
        -- Direct children of a parent with ID
        SET @PARENTID=1;
        SELECT id, body, commentable_id, commentable_type
        FROM comments
        WHERE commentable_id=@PARENTID
        AND commentable_type LIKE "%App%Comment%" AND is_removed=0 AND deleted_at IS NULL
        ORDER BY commentable_id ASC, id ASC;
    */
    // Test URL: http://localhost:8000/api/v1/open/comments/authorUsername/parentCommentHash            // to retrieve data for a specific comment hash (and it's reactions, author/user and the parent comment/post)
    // Test URL: http://localhost:8000/api/v1/open/comments/authorUsername/parentCommentHash?nested     // to retrieve data for a specific comment hash AND it's nested sub-tree of comments (and it's reactions, author/user and the parent comment/post)
    // Note: We disabled route-model binding: public function show(Post $tag)
    // so that we can load the model by slug
    // Note: If we use getRouteKeyName() for implicit binding in the model, to load by slug instead of id, it will make the api/v1/ for the backend fail.
    // See https://laravel.com/docs/master/routing#implicit-binding
    public function tree(Request $request, $authorUsername, $parentCommentHash)
    {
        $checkValidCommentExists = $this->checkForValidComment($authorUsername, $parentCommentHash);
        if (!$checkValidCommentExists["success"]) {
            return ["error" => $checkValidCommentExists["error"]];
        }

        // Only return comments that have NOT been deleted and with is_removed=false
        $returnedCommentTree =
            Comment::where([
                ['deleted_at', '=', NULL],
                ['is_removed', '=', '0'],
                ['comment_hash', '=', $parentCommentHash],
            ])
            //->orWhere('id', $parentCommentHash) // to load by hash OR id

            // Note: if you dont also return user_id here, the user object returned below will be null
            ->select('id', 'comment_hash', 'commentable_id', 'commentable_type', 'user_id', 'body', 'num_comments', 'num_likes', 'created_at', 'updated_at')
            ->whereHas('user', function ($query) use ($authorUsername) {
                $query->where([
                    ['deleted_at', '=', NULL],
                    ['username', '=', $authorUsername],
                ]);
            })
            ->with([
                'user' => function ($query) use ($authorUsername) {
                    // Note: To get this related data, you must also return the user_id field above
                    $query
                        ->where([
                            ['deleted_at', '=', NULL],
                            ['username', '=', $authorUsername],
                        ])
                        ->select('id', 'name', 'username');
                },
            ]);

        $getDeepComments = $request->has('nested') ? true : false;
        if ($getDeepComments) {
            // Retrieve the entire nested chain of comments
            $max_top_level_comments = 9999999;
            $returnedCommentTree = $this->withNestedCommentsAndReactions($returnedCommentTree, $max_top_level_comments);
        }

        $returnedCommentTree = $returnedCommentTree->firstOrFail();
        if (!$returnedCommentTree) {
            return ["error" => "Comment not found"];
        }

        $parent = $returnedCommentTree->commentable;
        $parent->type = get_class($returnedCommentTree->commentable);
        $parent->user = $returnedCommentTree->commentable->user;
        unset($returnedCommentTree->commentable);

        return new CommentResource(['parent' => $parent, 'comment' => $returnedCommentTree]);
    }
}
