<template>
    <div class="media post">
        <vote :model="answer" name="answer"></vote>
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
                            <a v-if="authorize('modify', answer)" @click.prevent="edit" class="btn btn-outline-info btn-sm">Edit</a>
                            <button v-if="authorize('modify', answer)" @click="destroy" class="btn btn-outline-danger btn-sm" >Delete</button>
                        </div>
                    </div>
                    <div class="col-4">
                    </div>
                    <div class="col-4 float-right">
                        <user-info v-bind:model="answer" label="Answered"></user-info>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    export default {
        props:['answer'],
        data(){
            return {
                editing: false,
                body: this.answer.body,
                bodyHtml: this.answer.body_html,
                id: this.answer.id,
                questionId: this.answer.question_id,
                beforeEditCache: null
            }
        },
        methods: {
            edit() {
                this.beforeEditCache = this.body;
                this.editing = true;
            },
            cancel(){
                this.body = this.beforeEditCache;
                this.editing = false;
            },
            update(){
              axios.patch(this.endpoint,{
                  body: this.body
              })
                  .then(res => {
                  this.editing = false;
                  this.bodyHtml = res.data.body_html;
                      this.$toast.success(res.data.message,"Success",{ timeout:3000 });
              })
                .catch(err => {
                    this.$toast.error(err.response.data.message,"Error", { timeout:3000 });
                    //console.log(err)
              });
          },
            destroy(){
                this.$toast.question("Are you sure about that?", "Success",{
                    timeout: 20000,
                    close: false,
                    overlay: true,
                    displayMode: 'once',
                    id: 'question',
                    zindex: 999,
                    title: 'Hey',
                    position: 'center',
                    buttons: [
                        ['<button><b>YES</b></button>', (instance, toast) => {

                            axios.delete(this.endpoint)
                                .then(res => {
                                    this.$emit('deleted')
                                })
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');

                        }, true],
                        ['<button>NO</button>', function (instance, toast) {

                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');

                        }],
                    ]
                });

            }

        },
        computed:{
            isInvalid(){
                return this.body.trim().length < 10;
            },
            endpoint(){
                return `/questions/${this.questionId}/answers/${this.id}`;
            }
        }


    }
</script>
