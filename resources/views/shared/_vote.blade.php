@if($model instanceof App\Question)
    @php
        $name = 'question';
        $firstURISegment = 'questions';
    @endphp
@elseif($model instanceof App\Answer)
    @php
        $name = 'answer';
        $firstURISegment = 'answers';
    @endphp
@endif

<div class="d-flex flex-column vote-controls">
    <a title="This {{ $name }} is useful" class="vote-up {{ Auth::guest() ? 'off' : '' }}"
       onclick="event.preventDefault(); document.getElementById('up-vote-{{ $name }}-{{ $model->id }}').submit();"
    >
        <i class="fas fa-caret-up fa-2x"></i>
    </a>
    <form id="up-vote-{{ $name }}-{{ $model->id }}" method="post" action="/{{ $firstURISegment }}/{{ $model->id }}/vote">
        @csrf
        <input type="hidden" name="vote" value="1">
    </form>
    <span class="vote-count">{{ $model->votes_count }}</span>
    <a title="This {{ $name }} is not useful"
       class="vote-down {{ Auth::guest() ? 'off' : '' }}"
       onclick="event.preventDefault(); document.getElementById('down-vote-{{ $name }}-{{ $model->id }}').submit();"
    >
        <i class="fas fa-caret-down fa-2x"></i>
    </a>
    <form id="down-vote-{{ $name }}-{{ $model->id }}" method="post" action="/{{ $firstURISegment }}/{{ $model->id }}/vote">
        @csrf
        <input type="hidden" name="vote" value="-1">
    </form>
    @if($model instanceof App\Question)
        <favorite :question="{{ $model }}"></favorite>
    @elseif($model instanceof App\Answer)
        <accept :answer="{{ $model }}"></accept>
    @endif
</div>
