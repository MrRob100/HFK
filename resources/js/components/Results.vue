<template>
    <div class="container">
        <table class="table-borderless">
            <thead>
            <th>Pair</th>
            <th>Candle Type</th>
            <th>Count Above</th>
            <th>Count Middle</th>
            <th>SD Above</th>
            <th>Ave</th>
            <th></th>
            </thead>
            <tbody>
            <tr v-for="result in results">
                <td>
                    {{ result.symbol1 }}
                    {{ result.symbol2 }}
                </td>
                <td>
                    {{ result.candle_type }}
                </td>
                <td>
                    {{ result.count_above }}
                </td>
                <td>
                    {{ result.count_middle }}
                </td>
                <td>
                    {{ result.sd_above }}
                </td>
                <td>
                    {{ result.ave }}
                </td>
                <td></td>
                <td>
                    <button class="btn btn-success btn-sm" @click="showResult(result.symbol1, result.symbol2)">Show Pair</button>
<!--                    <button onclick="showResult({{ $key }})" class="btn btn-success btn-sm">show pair</button>-->
<!--                    <input id="symbol1_{{ $key }}" type="hidden" value="{{ $result->symbol1 }}">-->
<!--                    <input id="symbol2_{{ $key }}" type="hidden" value="{{ $result->symbol2 }}">-->
<!--                    EMIT SYMBOLS-->
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: [
        "candle-type"
    ],
    data: function() {
        return {
            results: [],
        };
    },

    mounted() {
        this.getResults();
    },

    methods: {
        getResults: function() {
            axios.get('/results', {
                params: {
                    candleType: this.candleType,
                }
            }).then(response => {
                this.results = response.data;
            });
        },
        showResult: function(symbol1, symbol2) {
            this.$emit('show', symbol1, symbol2);
        }
    },

    watch: {
        candleType: function() {
            this.getResults();
        }
    }
}

</script>
