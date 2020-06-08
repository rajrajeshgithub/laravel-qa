<template>
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <h3>Your Answer</h3>
                    </div>
                    <hr>
                    <form @submit.prevent="create">
                        <div class="form-group">
                            <r-editor :body="body" name="new-answer">
                                <textarea class="form-control" required v-model="body" rows="7" name="body" ></textarea>
                            </r-editor>
                        </div>
                        <div class="form-group">
                            <button type="submit" :disabled="isInvalid" class="btn btn-lg btn-outline-primary">Post your answer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import REditor from "./REditor";
    export default {
        props:['questionId'],

        components:{ REditor },

        data(){
            return {
                body: '',
                id:this.questionId
            }
        },
        methods:{
            endpoint(){
                return `/questions/${this.id}/answers`;
            },
            create(){
                axios.post(this.endpoint(),{
                    body : this.body
                })
                .catch(response => {
                    this.$toast.error(response.data.message,"Error");
                })
                .then(({data}) => {
                    this.$toast.success(data.message,"Success");
                    this.body = '';
                    this.$emit('created', data.answer);
                });
            },
        },
        computed : {
            isInvalid(){
                return !this.signedIn || this.body.length < 10;
            }
        }
    }
</script>
