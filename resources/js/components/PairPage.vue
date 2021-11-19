<template>
    <div>
        <div class="row m-2">
            <div class="col-md-6">
<!--                <list-->
<!--                    @populate="populate"-->
<!--                    :spr="spr"-->
<!--                    :added="added"-->
<!--                    :dlr="dlr"-->
<!--                ></list>-->
            </div>
        </div>
        <div class="m-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="hour" id="hour" value="1h" v-model="candleType">
                <label class="form-check-label" for="hour">
                    Hour
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="day" id="day" value="1d" v-model="candleType" checked>
                <label class="form-check-label" for="day">
                    Day
                </label>
            </div>
            <br>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="kucoin" id="kucoin" value="kucoin" v-model="marketType">
                <label class="form-check-label" for="kucoin">
                    Kucoin
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="binance" id="binance" value="binance" v-model="marketType" checked>
                <label class="form-check-label" for="binance">
                    Binance
                </label>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-3 pr-0">
                        <input type="text" :value="v1.toUpperCase()" @input="v1 = $event.target.value.toUpperCase()" class="form-control mb-1">
                    </div>
                </div>
                <div class="row">
                    <div class="col-3 mr-0 pr-0">
                        <input type="text" :value="v2.toUpperCase()" @input="v2 = $event.target.value.toUpperCase()" class="form-control">
                    </div>

                </div>
                <br>
                <button @click="go" class="btn btn-sml btn-success">go</button>
            </div>
        </div>

        <pair
            @lasts="sendLasts"
            :symbols="value"
            :type="marketType"
        ></pair>
        <br>
        <div class="container">
            <run-controls :candleType="candleType">
            </run-controls>
<!--            <pair-record-->
<!--                :latest-data-route="latestDataRoute"-->
<!--                :balance-route="balanceRoute"-->
<!--                :value="value"-->
<!--                :push-lasts="pushLasts">-->
<!--            </pair-record>-->
            <results :candleType="candleType"></results>
        </div>
    </div>
</template>

<script>

import Results from "./Results";
export default {
    components: {Results},
    props: [],

    data: function () {
        return {
            candleType: '1d',
            value: '',
            v1: "",
            v2: "",
            symbols: {
                binance: [],
                kucoin: [],
            },
            marketType: "binance", //could be kucoin
            added: [],
            pushLasts: [],
            open: false,
        }
    },

    methods: {
        getOptions: function() {
            if (this.marketType === "oil") {
                return this.symbols.oil;
            }

            if (this.marketType === "metals") {
                return this.symbols.metals;
            }
        },
        go: function() {
            this.value = [
                {"name": (this.v1).toUpperCase()},
                {"name": (this.v2).toUpperCase()},
            ]
        },
        populate: function(s1, s2) {
            this.v1 = s1;
            this.v2 = s2;

            this.go();
        },

        add: function() {
            let _this = this;
            axios
                .post(this.cpr, {
                    params: {
                        s1: this.v1,
                        s2: this.v2,
                    },
                })
                .then(function() {
                    _this.added = [
                        {"s1": _this.v1},
                        {"s2": _this.v2}
                    ]
                });
        },
        randomize: function() {
            let _this = this;
            axios.get(this.rand).then(response => {
                _this.v2 = response.data.v2;
                _this.v1 = response.data.v1;
                _this.value = [
                    {"name": (response.data.v1).toUpperCase()},
                    {"name": (response.data.v2).toUpperCase()},
                ];
            });
        },
        trash: function() {
            let _this = this;
            axios.post(this.dp, {
                params: {
                    s1: this.v1,
                    s2: this.v2,
                }
            }).then(response => {
                if (!this.v1frozen) {
                    _this.v1 = '';
                }
                _this.v2 = '';
            })
        },
        sendLasts: function(data) {
            this.pushLasts = data;
        },
    },

    watch: {
        marketType: function(val) {
            this.options = this.symbols[val];
        }
    }
}
</script>
