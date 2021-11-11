<template>
    <div>
        <button @click="findPairs()" class="btn btn-info">Find Pairs</button>
        <br>
        <br>
        {{ message }}
    </div>
</template>

<script>
export default {
    name: "RunControls",
    props: ['candleType'],
    data() {
        return {
            'message': null,
            'results': null,
        };
    },

    mounted() {
        let _this = this;
        setInterval(function() {
            _this.pollMessage();
        }, 4000)
    },

    methods: {
        findPairs: function() {
            let _this = this;
            axios.post('/find_pairs', {
                candleType: this.candleType,
            }).then(response => {
                _this.results = response.data;
            });
        },
        pollMessage: function() {
            let _this = this;
            axios.get('/latest_message', {
                params: {
                    type: 'pair_check',
                }
            }).then(response => {
                _this.message = response.data;
            });
        }
    }
}
</script>

<style scoped>

</style>
