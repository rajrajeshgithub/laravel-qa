<a title="Click to mark as favorite {{ $name }} (Click again to undo)"
   class="favorite {{ Auth::guest() ? 'off' : ($model->is_favorited ? 'favorited' : '') }}"
   onclick="event.preventDefault(); document.getElementById('favorites-{{ $name }}-{{ $model->id }}').submit();"
>
    <i class="fa fa-star fa-1x"></i>
    <span class="favorite-count">{{ $model->favorites_count }}</span>
</a>
<form id="favorites-{{ $name }}-{{ $model->id }}" action="/{{ $firstURISegment }}/{{ $model->id }}/favorites" method="POST" style="display: none;">
    @csrf
    @if($model->is_favorited)
        @method('DELETE')
    @endif
</form>
