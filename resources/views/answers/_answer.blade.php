<answer v-bind:answer="{{ $answer }}" inline-template>
    <div class="media post">
        <vote :model="{{ $answer }}" name="answer"></vote>
        <div class="media-body">
            <form v-if="editing" @submit.prevent="update">
                @csrf
                <div class="form-group">
                    <textarea class="form-control" rows="10" v-model="body" required></textarea>
                </div>
                <button class="btn btn-primary" :disabled="isInvalid">Update</button>
                <button class="btn btn-outline-primary" @click="cancel">Cancel</button>
            </form>
            <div v-else>
                <div v-html="bodyHtml"></div>
                <div class="row">
                    <div class="col-4">
                        <div class="ml-auto">
                            {{--@if(Auth::user()->can('update',$question))--}}
                            @can('update',$answer)
                                <a @click.prevent="edit" class="btn btn-outline-info btn-sm">Edit</a>
                            @endcan
                            {{--@endif--}}
                            @can('delete',$answer)
                                <button @click="destroy" class="btn btn-outline-danger btn-sm" >Delete</button>
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
    </div>
</answer>
