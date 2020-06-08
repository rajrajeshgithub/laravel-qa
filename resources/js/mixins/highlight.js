import Prism from "prismjs";

export default {
    methods:{
        highlight(){
            const el = this.$refs.bodyHtml;
            Prism.highlightAllUnder(el);
        }
    }
}
