<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request)
    {
        $commentable = $request->validated('commentable');

        $comment = $commentable->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        return new CommentResource($comment->load('user'));
    }

    public function projectComments(Project $project)
    {
        $this->authorize('view', $project);

        return CommentResource::collection($project->comments()->with('user')->latest()->get());
    }

    public function taskComments(Task $task)
    {
        $this->authorize('view', $task);

        return CommentResource::collection($task->comments()->with('user')->latest()->get());
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return new CommentResource($comment->load('user'));
    }

    public function destroy(Comment $comment): Response
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->noContent();
    }
}
