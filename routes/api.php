<?php

use App\Http\Controllers\Api\Attachment\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Project\ProjectMembershipController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Api\Team\TeamController;
use App\Http\Controllers\Api\Team\TeamMembershipController;
use Illuminate\Support\Facades\Route;

// Route::post('register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::controller(TeamMembershipController::class)->group(function () {
        Route::post('/teams/{team}/members', 'addMember');

        Route::get('/teams/{team}/members', 'showTeamMembers');

        Route::patch('/teams/{team}/members/{member}', 'updateMemberRole');

        Route::delete('/teams/{team}/members/{member}', 'removeMember');
    });
    Route::apiResource('teams', TeamController::class);

    Route::controller(ProjectMembershipController::class)->group(function () {
        Route::post('/projects/{project}/members', 'addMember');

        Route::get('/projects/{project}/members', 'showProjectMembers');

        Route::delete('/projects/{project}/members/{member}', 'removeMember');
    });
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::apiResource('tasks', TaskController::class)->except('index');

    Route::get('/projects/{project}/comments', [CommentController::class, 'projectComments']);
    Route::get('/tasks/{task}/comments', [CommentController::class, 'taskComments']);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/attachments', [AttachmentController::class, 'store']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});
