@extends('layouts.app')

@section('content')
    <div class="row justify-content-center" xmlns="http://www.w3.org/1999/html">
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
                        @forelse($questions as $question)
                            @include('question._question')
                            @empty
                            <div class="alert alert-warning"><strong>Sorry</strong> There are no questions available</div>
                            @endforelse

                            {{ $questions->links() }}
                    </div>
                </div>
            </div>
        </div>
@endsection
