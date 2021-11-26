<template>
    <div class="container">
        <table class="table table-responsive table-striped table-sm">
            <thead>
            <th>Pair</th>
            <th>Candle Type</th>
            <th>USN</th>
            <th>Mids</th>
            <th>Uns</th>
            <th>Dns</th>
            <th>1<i class="fas fa-arrow-up"></i></th>
            <th>2<i class="fas fa-arrow-up"></i></th>
            <th><button :class="band === 'threeup' ? 'bg-info' : 'bg-light'" @click="getResults('threeup')">3<i class="fas fa-arrow-up"></i></button></th>
            <th><button :class="band === 'fourup' ? 'bg-info' : 'bg-light'" @click="getResults('fourup')">4<i class="fas fa-arrow-up"></i></button></th>
            <th><button :class="band === 'fiveeup' ? 'bg-info' : 'bg-light'" @click="getResults('fiveup')">5<i class="fas fa-arrow-up"></i></button></th>
            <th><button :class="band === 'sixup' ? 'bg-info' : 'bg-light'" @click="getResults('sixup')">6<i class="fas fa-arrow-up"></i></button></th>
            <th>10<i class="fas fa-arrow-up"></i></th>
            <th>1<i class="fas fa-arrow-down"></i></th>
            <th>2<i class="fas fa-arrow-down"></i></th>
            <th :class="band === 'threeup' ? 'bg-info' : ''">3<i class="fas fa-arrow-down"></i></th>
            <th :class="band === 'fourup' ? 'bg-info' : ''">4<i class="fas fa-arrow-down"></i></th>
            <th :class="band === 'fiveup' ? 'bg-info' : ''">5<i class="fas fa-arrow-down"></i></th>
            <th :class="band === 'sixup' ? 'bg-info' : ''">6<i class="fas fa-arrow-down"></i></th>
            <th>10<i class="fas fa-arrow-down"></i></th>
            <th></th>
            </thead>
            <tbody>
            <tr v-for="(result, index) in results.data" :id="'row_' + index">
                <td>
                    {{ result.symbol1 }}
                    {{ result.symbol2 }}
                </td>
                <td>
                    {{ result.candle_type }}
                </td>
                <td>
                    {{ result.usn }}
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
                <td :class="band === 'threeup' ? 'bg-info' : ''">
                    {{ result.threeup }}
                </td>
                <td :class="band === 'fourup' ? 'bg-info' : ''">
                    {{ result.fourup }}
                </td>
                <td :class="band === 'fiveup' ? 'bg-info' : ''">
                    {{ result.fiveup }}
                </td>
                <td :class="band === 'sixup' ? 'bg-info' : ''">
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
                <td :class="band === 'threeup' ? 'bg-info' : ''">
                    {{ result.threedown }}
                </td>
                <td :class="band === 'fourup' ? 'bg-info' : ''">
                    {{ result.fourdown }}
                </td>
                <td :class="band === 'fiveup' ? 'bg-info' : ''">
                    {{ result.fivedown }}
                </td>
                <td :class="band === 'sixup' ? 'bg-info' : ''">
                    {{ result.sixdown }}
                </td>
                <td>
                    {{ result.tendown }}
                </td>
                <td>
                    <button class="btn btn-success btn-sm" @click="showResult(result.symbol1, result.symbol2, index)">Show</button>
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
            index: null,
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
        showResult: function(symbol1, symbol2, index) {

            this.index = index;

            let row = document.getElementById('row_' + index);

            row.style.backgroundColor = "#777";

            this.$emit('show', symbol1, symbol2);
        }
    },

    watch: {
        candleType: function() {
            this.getResults();
        },
        index: function(newVal, oldVal) {
            if (oldVal || oldVal == 0) {
                let row = document.getElementById('row_' + oldVal);
                row.style.backgroundColor = "initial";
            }
        },
    }
}

</script>
