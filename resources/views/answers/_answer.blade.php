<div class="media post">
    @include('shared._vote',['model' => $answer])
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
                {{--@include('shared._author',[
                'model'=> $answer,
                'label'=> 'Answered'
                ])--}}
                <user-info v-bind:model="{{ $answer }}" label="Answered"></user-info>
            </div>
        </div>
    </div>
</div>
