<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h2>{{ $answersCount ." ". \Illuminate\Support\Str::plural('Answer', $answersCount) }}</h2>
                </div>
                <hr>
                @foreach($answers as $answer)
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
                            <div class="row">
                                <div class="col-4">
                                    <div class="ml-auto">
                                        {{--@if(Auth::user()->can('update',$question))--}}
                                        @can('update',$answer)
                                            <a href="{{ route('questions.answers.edit',[$question->id, $answer->id]) }}" class="btn btn-outline-info btn-sm">Edit</a>
                                        @endcan
                                        {{--@endif--}}
                                        @can('delete',$answer)
                                            <form method="post" class="form-delete" action="{{ route('questions.answers.destroy', [$question->id, $answer->id]) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure to delete?')">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-4">
                                </div>
                                <div class="col-4 float-right">
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
                    </div>
                    <hr>
                @endforeach
            </div>
        </div>
    </div>
</div>
