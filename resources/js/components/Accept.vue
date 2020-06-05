<template>
    <div>

        <a v-if="canAccept" title="Mark this answer as best answer"
           :class="classess"
            @click.prevent="create"
        >
            <i class="fa fa-check fa-1x"></i>
        </a>
        <a v-if="accepted" :class="classess">
            <i class="fa fa-check fa-1x"></i>
        </a>

    </div>
</template>
<script>
    import eventBus from '../event-bus';
    export  default {
        props:['answer'],

        data(){
            return {
                isBest:this.answer.is_best,
                id: this.answer.id
            }
        },
        created() {
            eventBus.$on('accepted', id => {
                this.isBest = (id === this.id);
            });
        },
        methods:{
            create(){
                axios.post(`/answers/${this.id}/accept`)
                .then(res => {
                    this.$toast.success(res.data.message,'Success',{
                        timeout:3000,
                        position:'bottomLeft'
                    });
                    this.isBest = true;

                    eventBus.$emit('accepted',this.id);
                });
            }
        },

        computed:{
            canAccept(){
                return this.authorize('accept',this.answer);
            },

            accepted(){
                return !this.canAccept && this.isBest;
            },

            classess(){
                return ['mt-2',
                this.isBest ? 'vote-accepted':'']
            }
        },

    }
</script>
