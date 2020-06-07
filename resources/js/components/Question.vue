<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <form class="card-body" v-if="editing" @submit.prevent="update">
                    <div class="card-title">
                        <input type="text" class="form-control form-control-lg" v-model="title">
                    </div>
                    <hr>
                    <div class="media">
                        <div class="media-body">
                            <div class="form-group">
                                <textarea class="form-control" rows="10" v-model="body" required></textarea>
                            </div>
                            <button class="btn btn-primary" :disabled="isInvalid">Update</button>
                            <button class="btn btn-outline-primary" @click="cancel">Cancel</button>
                        </div>
                    </div>
                </form>
                <div class="card-body" v-else>
                    <div class="card-title">
                        <div class="d-flex align-items-center">
                            <h1>{{ title }}</h1>
                            <div class="ml-auto">
                                <a href="/questions" class="btn btn-outline-primary">Back to All Questions</a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="media">
                        <vote :model="question" name="question"></vote>
                        <div class="media-body">
                            <div v-html="bodyHtml"></div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="ml-auto">
                                        <a v-if="authorize('modify', question)" @click.prevent="edit" class="btn btn-outline-info btn-sm">Edit</a>
                                        <button v-if="authorize('deleteQuestion', question)" @click="destroy" class="btn btn-outline-danger btn-sm" >Delete</button>
                                    </div>
                                </div>
                                <div class="col-4"></div>
                                <div class="col-4">
                                    <user-info v-bind:model="question" label="Asked"></user-info>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Vote from "./Vote";
    import UserInfo from "./UserInfo";
    import modification from "../mixins/modification";
    export default {
        props:['question'],

        mixins:[modification],

        components:{Vote, UserInfo},

        data(){
            return {
                title : this.question.title,
                body : this.question.body,
                bodyHtml : this.question.body_html,
                id: this.question.id,
                beforeEditCache:{}
            }
        },

        computed:{
            isInvalid(){
                return this.body.length < 10 || this.title.length < 10;
            },

            endpoint(){
                return `/questions/${this.id}`;
            }
        },
        methods:{
            setEditCache(){
                this.beforeEditCache = {
                    body:this.body,
                        title : this.title
                };
            },

            restoreFromCache(){
                this.title = this.beforeEditCache.title;
                this.body = this.beforeEditCache.body;
            },

            payload(){
                return {
                    title: this.title,
                    body: this.body
                }
            },

            delete(){
                axios.delete(this.endpoint)
                    .then(({data}) => {
                        this.$toast.success(data.message,"Success", { timeout : 2000, position:'bottomLeft' });
                    });

                setTimeout(() => {
                    window.location.href = "/questions";
                }, 3000);
            }
        }

    }
</script>
