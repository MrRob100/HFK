<template>
    <div class="row">
        <div class="col-4">
            <trading-vue
                :indexBased="true"
                style="z-index: -1"
                colorText="#7DA0B1"
                :data="tradingVue1"
                :height="280"
                :width="460"
            ></trading-vue>
        </div>
        <div class="col-4">
            <trading-vue
                :indexBased="true"
                style="z-index: -1"
                colorText="#7DA0B1"
                :data="tradingVueData"
                :overlays="overlays"
                :height="280"
                :width="460"
            ></trading-vue>
        </div>
        <div class="col-4">
            <trading-vue
                :indexBased="true"
                style="z-index: -1"
                colorText="#7DA0B1"
                :data="tradingVue2"
                :height="280"
                :width="460"
            ></trading-vue>
        </div>
        <br>
        <div class="ml-3 mr-3 mt-3">{{ error }}</div>
        <button class="btn btn-primary btn-sm" v-if="error" @click="getData(symbols[0].name, symbols[1].name, type, candleType)">Try again</button>
    </div>
</template>
<script>

import { TradingVue, DataCube } from 'trading-vue-js';
import Overlays from 'tvjs-overlays';
// import TradingVuea from '../../js/trading-vue-a-2';

export default {

    components: {
        TradingVue,
        // TradingVuea,
    },

    props: {
        symbols: "",
        type: "",
        candleType: "",
    },

    data: function() {
        return {
            error: null,
            tradingVue1: new DataCube({
                chart: {
                    data: []
                },
            }),
            tradingVueData: new DataCube({
                chart: {
                    data: []
                },
                onchart: [
                    // {
                    //     name: 'EMA',
                    //     type: 'EMA',
                    //     data: [],
                    // },
                    {
                        name: 'EMA, 50',
                        type: 'EMA',
                        data: [],
                        settings: {
                            length: 50
                        }
                    },
                    {
                        name: 'EMA, 25',
                        type: 'EMA',
                        data: [],
                        settings: {
                            length: 25
                        }
                    }
                ]
            }),
            tradingVue2: new DataCube({
                chart: {
                    data: []
                },
            }),
            overlays: [Overlays['EMA']],
            lineDataPair: [],
        }
    },

    methods: {
        getData: function(s1, s2, type, candleType) {

            this.error = null;
            this.loading = true;

            let _this = this;

            axios.get('/chart_data', {
                params: {
                    s1,
                    s2,
                    type,
                    candleType,
                }
            }).then(response => {
                this.tradingVue1.data.chart.data = response.data['first'];
                this.tradingVueData.data.chart.data = response.data['pair'];
                this.tradingVueData.data.onchart.data = response.data['pair'];
                this.tradingVue2.data.chart.data = response.data['second'];

                this.lineDataPair = response.data['events'];

                let lasts = [
                    {"s1": response.data['first'][response.data['first'].length - 1][4]},
                    {"s2": response.data['second'][response.data['second'].length - 1][4]},
                ];

                this.$emit('lasts', lasts)
                this.loading = false;
            }).catch(err => {

                console.log(err);

                this.error = 'error getting data';
                this.loading = false;
            });
        },
        setChartHeading: function(val) {
            let elems = document.getElementsByClassName('trading-vue-ohlcv');

            elems[0].style.display = "block";
            elems[0].innerText = val[0].name + "USD";

            elems[1].style.display = "block";
            elems[1].innerText = val[0].name + val[1].name;

            elems[2].style.display = "block";
            elems[2].innerText = val[1].name + "USD";
        }
    },

    watch: {
        candleType: function() {
            this.getData(this.symbols[0].name, this.symbols[1].name, this.type, this.candleType);
        },
        symbols: function(val) {
            if (val.length == 2) {
                this.getData(val[0].name, val[1].name, this.type, this.candleType);
                this.setChartHeading(val);
            }
        },
        type: function() {
            this.getData(this.symbols[0].name, this.symbols[1].name, this.type, this.candleType);
        }
    }
}
</script>
<style lang="scss">
.trading-vue-legend {
    z-index: 1;
    font-size: 1.5rem;
}
.trading-vue-ohlcv {
    display: none;
}
</style>
