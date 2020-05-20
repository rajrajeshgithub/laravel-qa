@extends('layouts.app')

@section('content')
        <div class="row justify-content-center">
            @include('inc.messages')
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h2>All Questions</h2>
                            <div class="ml-auto">
                                <a href="{{ route('questions.create') }}" class="btn btn-outline-primary">Ask Question</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($questions as $question)
                            <div class="media">
                                <div class="d-flex flex-column counters">
                                    <div class="vote">
                                        <strong>{{ $question->votes }}</strong> {{ \Illuminate\Support\Str::plural('vote',$question->votes) }}
                                    </div>
                                    <div class="status {{ $question->status }}">
                                        <strong>{{ $question->answers_count }}</strong> {{ \Illuminate\Support\Str::plural('answer',$question->answers_count) }}
                                    </div>
                                    <div class="view">
                                        {{ $question->views }} {{ \Illuminate\Support\Str::plural('view',$question->answers_count) }}
                                    </div>
                                </div>
                                <div class="media-body">
                                    <div class="d-flex align-items-center">
                                        <h3 class="mt-0"><a href="{{ $question->url }}">{{ $question->title }}</a></h3>
                                        <div class="ml-auto">
                                            {{--@if(Auth::user()->can('update',$question))--}}
                                            @can('update',$question)
                                            <a href="{{ route('questions.edit',$question->id) }}" class="btn btn-outline-info btn-sm">Edit</a>
                                            @endcan
                                            {{--@endif--}}
                                            @can('delete',$question)
                                            <form method="post" class="form-delete" action="{{ route('questions.destroy', $question->id) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure to delete?')">Delete</button>
                                            </form>
                                            @endcan
                                        </div>
                                    </div>

                                <p class="lead">
                                    Asked by
                                    <a href="{{ $question->user->url }}">{{ $question->user->name }}</a>
                                    <small class="text-muted">{{ $question->created_date }}</small>
                                </p>
                                {{ \Illuminate\Support\Str::limit($question->body,250) }}
                                </div>
                            </div>
                            <hr>
                            @endforeach

                        {{ $questions->links() }}

                    </div>
                </div>
            </div>
        </div>
@endsection
