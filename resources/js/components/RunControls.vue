<template>
    <div>
        <button @click="findPairs()" class="btn btn-info">Find Pairs</button>
    </div>
</template>

<script>
export default {
    name: "RunControls",
    props: ['candleType'],
    methods: {
        findPair: function() {
            let _this = this;
            axios.post('/find_pair', {
                params: {
                    candleType: this.candleType,
                },
            }).then(response => {
                _this.results = response.data;
            });
        }
    }
}
</script>

<style scoped>

</style>
