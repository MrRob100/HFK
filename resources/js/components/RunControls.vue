<template>
    <div>
        <button @click="findPairs()" class="btn btn-info">Find Pairs</button>
        <br>
    </div>
</template>

<script>
export default {
    name: "RunControls",
    props: ['candleType'],
    data() {
        return {
            'results': null,
        };
    },

    methods: {
        findPairs: function() {
            let _this = this;
            axios.post('/find_pairs', {
                candleType: this.candleType,
            }).then(response => {
                _this.results = response.data;
            });
        }
    }
}
</script>

<style scoped>

</style>
