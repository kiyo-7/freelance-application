<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\FreelancerProfileController;
use App\Http\Controllers\ProjectController; 
use App\Http\Controllers\FavoriteProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteServiceController;



Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/validate-token', [AuthController::class, 'validateToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    //client routes
    Route::get('/client/profile', [ClientProfileController::class, 'show']);
    Route::put('/client/profile', [ClientProfileController::class, 'update']);
    //freelancer routes
    Route::get('/freelancer/all', [FreelancerProfileController::class, 'index_all']);
    Route::get('/freelancer/profile', [FreelancerProfileController::class, 'show']);
    Route::put('/freelancer/profile', [FreelancerProfileController::class, 'update']);

      // favorite project routes
    Route::get('/projects/favorites', [FavoriteProjectController::class, 'indexFavorites']);
    Route::post('/projects/{projectId}/favorite', [FavoriteProjectController::class, 'toggle']);
    
   
    //project routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/all', [ProjectController::class, 'index_all']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::get('/client/projects', [ProjectController::class, 'myProjects']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    //chat routes
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::post('/conversations', [ChatController::class, 'createConversation']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::delete('/conversations/{conversation}', [ChatController::class, 'destroy']);
    Route::delete('/conversations/{conversation}/messages/{message}', [ChatController::class, 'deleteMessage']);

    //reviews
    Route::get('/projects/{project}/reviews', [ReviewController::class, 'index']);
    Route::post('/projects/{project}/reviews', [ReviewController::class,'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);

    //payments
    Route::get('/projects/{project}/payments', [PaymentController::class, 'index']);
    Route::post('/projects/{project}/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    //services
    Route::get('/freelancer/services', [ServiceController::class, 'index']);
    Route::get('/freelancer/services/all', [ServiceController::class, 'index_all']);
    Route::get('/freelancers/{id}', [FreelancerProfileController::class, 'showById']);
    Route::get('/freelancers/user/{userId}', [FreelancerProfileController::class, 'showByUserId']);
    Route::post('/freelancer/services', [ServiceController::class, 'store']);
    Route::put('/freelancer/services/{id}/status', [ServiceController::class, 'updateStatus']);
    Route::delete('/freelancer/services/{id}', [ServiceController::class, 'destroy']);
    
    //proposals
    Route::post('/projects/{project}/proposals', [ProposalController::class, 'store']);
    Route::get('/projects/{project}/proposals', [ProposalController::class, 'index']);
    Route::post('/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::post('/proposals/{proposal}/reject', [ProposalController::class, 'reject']);

    //notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);


    //favorite service routes
    Route::get('/services/favorites', [FavoriteServiceController::class, 'indexFavorites']);
    Route::post('/services/{serviceId}/favorite', [FavoriteServiceController::class, 'toggle']);
});
