<template>
    <div>
        <div class="row">
            <div class="col-10 m-auto">
                <div class="row">
                    <div class="col-6">
                        <p>Transfer from {{ symbol1 }} to {{ symbol2 }}</p>
                        <input v-model=amount1to2 type="range" min="0" max="100" value="50" class="slider">
                        <p>{{ amount1to2 }}%</p>
                        <button @click="transfer(symbol1, symbol2, (1/amount1to2 * 100))" class="btn btn-success">Trade</button>
                        <br>
                        <br>
                        <button @click="getPosition(symbol1, 'one')" class="btn btn-info mb-2">Balance {{ symbol1 }}: {{ bal.one }} (${{ Math.floor(bal.oneUSD) }})</button>
                    </div>
                    <div class="col-6">
                        <p>Transfer from {{ symbol2 }} to {{ symbol1 }}</p>
                        <input v-model=amount2to1 type="range" min="0" max="100" value="50" class="slider">
                        <p>{{ amount2to1 }}%</p>
                        <button @click="transfer(symbol2, symbol1, (1/amount2to1 * 100))" class="btn btn-success">Trade</button>
                        <br>
                        <br>
                        <button @click="getPosition(symbol2, 'two')" class="btn btn-info mb-2">Balance {{ symbol2 }}: {{ bal.two }} (${{ Math.floor(bal.twoUSD) }})</button>
                        <br>
                    </div>
                    <div v-if="symbol1 === 'RIF' && symbol2 === 'BTC'" class="col-12">
                        <button @click="getRIFBTC()" class="btn btn-info mb-2">Balance Both in BTC: {{ (bal.oneUSD / (bal.twoUSD / bal.two)) + bal.two }}</button>
                    </div>
                    <div class="col-12">
                        <button @click="getPosition('USDT', 'usdt')" class="btn btn-info mb-2">Balance USDT: ${{ Math.floor(bal.usdt) }}</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <button @click="showInputForm()" class="btn btn-info mb-2">Add Input</button>
                        <div v-if="showForm" class="mt-3">
                            <form v-on:submit.prevent>
                                <div class="form-group pl-0">
                                    <input class="form-control mb-2" v-model="input.one" type="number" step="any" :placeholder="'amount ' + symbol1">
                                    <input class="form-control mb-2" v-model="input.oneUSD" type="number" step="any" :placeholder="'amount ' + symbol1 + ' usd'">
                                    <input class="form-control mb-2" v-model="input.two" type="number" step="any" :placeholder="'amount ' + symbol2">
                                    <input class="form-control mb-2" v-model="input.twoUSD" type="number" step="any" :placeholder="'amount ' + symbol2 + ' usd'">
                                    <button @click="createInputRecord()" class="btn btn-success">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>
</template>

<script>
export default {
    props: [
        "symbol1",
        "symbol2",
    ],
    data: function() {
        return {
            amount1to2: 0,
            amount2to1: 0,
            splitLow: 25,
            split: 44.5,
            bal: {
                one: null,
                oneUSD: null,
                two: null,
                twoUSD: null,
                usdt: null
            },
            disabled: false,
            showForm: false,
            input: {
                symbol1: null,
                one: null,
                oneUSD: null,
                symbol2: null,
                two: null,
                twoUSD: null,
            }
        };
    },

    methods: {
        createInputRecord: function() {
            this.input.symbol1 = this.symbol1;
            this.input.symbol2 = this.symbol2;

            axios.post("/input", this.input).then(() => {
                this.showForm = false;
            }).catch(error => {
                console.error(error);
            })
        },
        showInputForm: function() {
            this.showForm = !this.showForm;
        },
        getRIFBTC: function() {
            this.getPosition('RIF', 'one');
            this.getPosition('BTC', 'two');
        },
        getPosition: function(symbol, which) {
            let _this = this;
            axios.get("/position", {
                params: {
                    of: symbol,
                }
            }).then(function (response) {

                let suffix = which + 'USD';

                if (response.data === "") {
                    _this.bal[which] = 0;
                    _this.bal[suffix] = 0;
                } else {
                    _this.bal[which] = response.data.qty;
                    _this.bal[suffix] = response.data.market_value;
                }
            });
        },
        // inUSD: function(symbol, amount, which) {
        //     let _this = this;
        //     axios.get(this.pr, {
        //         params: {
        //             symbol: symbol,
        //         }
        //     }).then(function (response) {
        //         _this.bal[which + "USD"] = response.data * amount;
        //     });
        // }
        transfer: function(from, to, portion) {
            if (confirm("Are you sure you want to transfer " + from + " to " + to +"?")) {
                this.disabled = true;
                let _this = this;
                axios.get("/transfer", {
                    params: {
                        from: from.toUpperCase(),
                        to: to.toUpperCase(),
                        portion: portion,
                    }
                }).then(function (response) {
                    // _this.getBalance(from);
                    // _this.getBalance(to);
                    _this.disabled = !response.data;
                });
            }
        },
    },

    watch: {
        symbol1: function() {
            this.bal.one = "";
            this.bal.oneUSD = "";
        },
        symbol2: function() {
            this.bal.two = "";
            this.bal.twoUSD = "";
        },
    }

}

</script>
<style>
.slider {
    -webkit-appearance: none;
    width: 100%;
    height: 25px;
    background: #d3d3d3;
    outline: none;
    opacity: 0.7;
    -webkit-transition: .2s;
    transition: opacity .2s;
}

.slider:hover {
    opacity: 1;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 25px;
    height: 25px;
    background: #38c172;
    cursor: pointer;
}

.slider::-moz-range-thumb {
    width: 25px;
    height: 25px;
    background: #38c172;
    cursor: pointer;
}
</style>
