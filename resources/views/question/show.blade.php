@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <div class="d-flex align-items-center">
                                <h1>{{ $question->title }}</h1>
                                <div class="ml-auto">
                                    <a href="{{ route('questions.index') }}" class="btn btn-outline-primary">Back to All Questions</a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="media">
                            <div class="d-flex flex-column vote-controls">
                                <a title="This question is useful" class="vote-up">
                                    <i class="fas fa-caret-up fa-2x"></i>
                                    <span class="vote-count">1230</span></a>
                                <a title="This question is not useful" class="vote-down off">
                                    <i class="fas fa-caret-down fa-2x"></i>
                                    <span class="vote-count">12</span>
                                </a>
                                <a title="Click to mark as favorite question (Click again to undo)" class="favorite favorited">
                                    <i class="fa fa-star fa-1x"></i>
                                    <span class="favorite-count">123</span>
                                </a>
                            </div>
                            <div class="media-body">
                                {!! $question->body_html !!}
                                <div class="float-right">
                                    <span class="text-muted">Asked {{ $question->created_date }}</span>
                                    <div class="media mt-2">
                                        <a href="{{ $question->user->url }}" class="pr-2">
                                            <img src="{{ $question->user->avatar }} ">
                                        </a>
                                        <div class="media-body mt-1">
                                            <a href="{{ $question->user->url }}">{{ $question->user->name }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <h2>{{ $question->answers_count ." ". \Illuminate\Support\Str::plural('Answer', $question->answers_count) }}</h2>
                        </div>
                        <hr>
                        @foreach($question->answers as $answer)
                            <div class="media">
                                <div class="d-flex flex-column vote-controls">
                                    <a title="This question is useful" class="vote-up">
                                        <i class="fas fa-caret-up fa-2x"></i>
                                        <span class="vote-count">1230</span></a>
                                    <a title="This question is not useful" class="vote-down off">
                                        <i class="fas fa-caret-down fa-2x"></i>
                                        <span class="vote-count">12</span>
                                    </a>
                                    <a title="Mark this answer as best answer" class="vote-accepted favorited">
                                        <i class="fa fa-check fa-1x"></i>
                                        <span class="favorite-count">123</span>
                                    </a>
                                </div>
                                <div class="media-body">
                                    {{ $answer->body }}
                                    <div class="float-right">
                                        <span class="text-muted">Answered {{ $answer->created_date }}</span>
                                        <div class="media mt-2">
                                            <a href="{{ $answer->user->url }}" class="pr-2">
                                                <img src="{{ $answer->user->avatar }} ">
                                            </a>
                                            <div class="media-body mt-1">
                                                <a href="{{ $answer->user->url }}">{{ $answer->user->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
