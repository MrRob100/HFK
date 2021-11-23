<template>
    <div>
        <table class="table table-responsive table-striped">
            <thead>
            <th>Pair</th>
            <th>Candle Type</th>
            <th>Mids</th>
            <th>Uns</th>
            <th>Dns</th>
            <th>1 up</th>
            <th>2 up</th>
            <th><button :class="band === 'threeup' ? 'bg-info' : 'bg-light'" @click="getResults('threeup')">3 up</button></th>
            <th><button :class="band === 'fourup' ? 'bg-info' : 'bg-light'" @click="getResults('fourup')">4 up</button></th>
            <th><button :class="band === 'fiveeup' ? 'bg-info' : 'bg-light'" @click="getResults('fiveup')">5 up</button></th>
            <th><button :class="band === 'sixup' ? 'bg-info' : 'bg-light'" @click="getResults('sixup')">6 up</button></th>
            <th>10 up</th>
            <th>1 down</th>
            <th>2 down</th>
            <th>3 down</th>
            <th>4 down</th>
            <th>5 down</th>
            <th>6 down</th>
            <th>10 down</th>
            <th></th>
            </thead>
            <tbody>
            <tr v-for="result in results.data">
                <td>
                    {{ result.symbol1 }}
                    {{ result.symbol2 }}
                </td>
                <td>
                    {{ result.candle_type }}
                </td>
                <td>
                    {{ result.middles }}
                </td>
                <td>
                    {{ result.upneighbours }}
                </td>
                <td>
                    {{ result.downneighbours }}
                </td>
                <td>
                    {{ result.oneup }}
                </td>
                <td>
                    {{ result.twoup }}
                </td>
                <td>
                    {{ result.threeup }}
                </td>
                <td>
                    {{ result.fourup }}
                </td>
                <td>
                    {{ result.fiveup }}
                </td>
                <td>
                    {{ result.sixup }}
                </td>
                <td>
                    {{ result.tenup }}
                </td>
                <td>
                    {{ result.onedown }}
                </td>
                <td>
                    {{ result.twodown }}
                </td>
                <td>
                    {{ result.threedown }}
                </td>
                <td>
                    {{ result.fourdown }}
                </td>
                <td>
                    {{ result.fivedown }}
                </td>
                <td>
                    {{ result.sixdown }}
                </td>
                <td>
                    {{ result.tendown }}
                </td>
                <td>
                    <button class="btn btn-success btn-sm" @click="showResult(result.symbol1, result.symbol2)">Show</button>
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
            band: null,
        };
    },

    mounted() {
        this.getResults();
    },

    methods: {
        getResults: function(band = null) {
            this.band = band;
            axios.get('/results', {
                params: {
                    candleType: this.candleType,
                    band: band,
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
