<template>
    <div class="media post">
        <vote :model="answer" name="answer"></vote>
        <div class="media-body">
            <form v-show="authorize('modify', answer) && editing" @submit.prevent="update">
                <div class="form-group">
                    <r-editor :body="body" :name="uniqueName">
                        <textarea class="form-control" rows="10" v-model="body" required></textarea>
                    </r-editor>
                </div>
                <button class="btn btn-primary" :disabled="isInvalid">Update</button>
                <button class="btn btn-outline-primary" @click.prevent="cancel">Cancel</button>
            </form>
            <div v-show="!editing">
                <div v-html="bodyHtml" ref="bodyHtml"></div>
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
    import modification from "../mixins/modification";

    export default {
        props:['answer'],

        mixins:[modification],

        data(){
            return {
                body: this.answer.body,
                bodyHtml: this.answer.body_html,
                id: this.answer.id,
                questionId: this.answer.question_id,
                beforeEditCache: null
            }
        },
        methods: {
            setEditCache() {
                this.beforeEditCache = this.body;
            },
            restoreFromCache(){
                this.body = this.beforeEditCache;
            },
            payload(){
                return {
                    body: this.body
                }
            },
            delete(){
                axios.delete(this.endpoint)
                    .then(res => {
                        this.$toast.success(res.data.message,"Success", { timeout : 2000, position:'bottomLeft' });
                        this.$emit('deleted')
                    })
            }

        },
        computed:{
            isInvalid(){
                return this.body.trim().length < 10;
            },
            endpoint(){
                return `/questions/${this.questionId}/answers/${this.id}`;
            },
            uniqueName(){
                return `answer-${this.id}`;
            }
        }


    }
</script>
