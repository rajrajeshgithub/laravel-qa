<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/token', 'Auth\LoginController@getToken');
Route::get('/questions','Api\QuestionsController@index');
Route::get('/questions/{question}/answers','Api\AnswersController@index');
Route::get('/question/{slug}','Api\QuestionDetailsController');
Route::middleware(['auth:api'])->group(function() {
    Route::apiResource('/questions', 'Api\QuestionsController');
    Route::apiResource('/questions.answers', 'Api\AnswersController');
    Route::post('/answers/{answer}/accept', 'Api\AcceptAnswerController');
    Route::post('/questions/{question}/vote', 'Api\VoteQuestionController');
    Route::post('/answers/{answer}/vote', 'Api\VoteAnswerController');
    Route::post('/questions/{question}/favorites','Api\FavoritesController@store');
    Route::delete('/questions/{question}/favorites','Api\FavoritesController@destroy');
    Route::get('my-posts','Api\MyPostsController');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


