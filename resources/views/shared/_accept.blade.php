@can('accept',$model)
    <a title="Mark this answer as best answer"
       class="{{ $model->status }} favorited"
       onclick="event.preventDefault(); document.getElementById('accept-answer-{{ $model->id }}').submit();"
    >
        <i class="fa fa-check fa-1x"></i>
    </a>
    <form id="accept-answer-{{ $model->id }}" action="{{ route('answers.accept', $model->id) }}" method="POST" style="display: none;">
        @csrf
    </form>
@elseif($model->isBest)
    <a  class="{{ $model->status }} favorited">
        <i class="fa fa-check fa-1x"></i>
    </a>
@endcan
