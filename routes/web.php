<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/','QuestionController@index');

Auth::routes();

Route::get('/home', 'QuestionController@index')->name('home');

Route::resource('/questions', 'QuestionController')->except('show');
/*Route::post('/question/{$question}/answers', 'AnswersController@store')->name('answers.store');*/
Route::resource('questions.answers', 'AnswersController')->except(['create','show']);
Route::get('/questions/{slug}', 'QuestionController@show')->name('questions.show');

Route::post('/answers/{answer}/accept', 'AcceptAnswerController')->name('answers.accept');
Route::post('/questions/{question}/favorites','FavoritesController@store')->name('questions.favorite');
Route::delete('/questions/{question}/favorites','FavoritesController@destroy')->name('questions.unfavorite');

Route::post('/questions/{question}/vote', 'VoteQuestionController')->name('questions.vote');
Route::post('/answers/{answer}/vote', 'VoteAnswerController')->name('answers.vote');
