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
                            <a title="This question is useful" class="vote-up {{ \Auth::guest() ? 'off' : ''}}"
                            onclick="event.preventDefault(); document.getElementById('up-vote-answer-{{ $answer->id }}').submit();"
                            >
                                <i class="fas fa-caret-up fa-2x"></i>
                            </a>
                            <form name="" id="up-vote-answer-{{ $answer->id }}" method="post" action="/answers/{{ $answer->id }}/vote">
                                @csrf
                                <input type="hidden" name="vote" value="1">
                            </form>
                                <span class="vote-count">{{ $answer->votes_count }}</span>
                            <a title="This question is not useful" class="vote-down {{ \Auth::guest() ? 'off' : '' }}"
                                onclick="event.preventDefault(); document.getElementById('down-vote-answer-{{ $answer->id }}').submit();"
                            >
                                <i class="fas fa-caret-down fa-2x"></i>
                            </a>
                            <form name="" id="down-vote-answer-{{ $answer->id }}" method="post" action="/answers/{{ $answer->id }}/vote">
                                @csrf
                                <input type="hidden" name="vote" value="-1">
                            </form>
                            @can('accept',$answer)
                                <a title="Mark this answer as best answer"
                                   class="{{ $answer->status }} favorited"
                                    onclick="event.preventDefault(); document.getElementById('accept-answer-{{ $answer->id }}').submit();"
                                >
                                    <i class="fa fa-check fa-1x"></i>
                                </a>
                                <form id="accept-answer-{{ $answer->id }}" action="{{ route('answers.accept', $answer->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            @elseif($answer->isBest)
                                <a  class="{{ $answer->status }} favorited">
                                    <i class="fa fa-check fa-1x"></i>
                                </a>
                            @endcan
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
