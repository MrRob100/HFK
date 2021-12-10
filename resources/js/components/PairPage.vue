<template>
    <div>
        <div class="row m-2">
            <div class="m-3">
<!--                <div class="form-check">-->
<!--                    <input class="form-check-input" type="radio" name="hour" id="hour" value="1h" v-model="candleType">-->
<!--                    <label class="form-check-label" for="hour">-->
<!--                        Hour-->
<!--                    </label>-->
<!--                </div>-->
<!--                <div class="form-check">-->
<!--                    <input class="form-check-input" type="radio" name="day" id="day" value="1d" v-model="candleType" checked>-->
<!--                    <label class="form-check-label" for="day">-->
<!--                        Day-->
<!--                    </label>-->
<!--                </div>-->
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
                    <div>
                        <div class="pr-0">
                            <input type="text" :value="v1.toUpperCase()" @input="v1 = $event.target.value.toUpperCase()" class="form-control mb-1">
                        </div>
                    </div>
                    <div>
                        <div class="mr-0 pr-0">
                            <input type="text" :value="v2.toUpperCase()" @input="v2 = $event.target.value.toUpperCase()" class="form-control">
                        </div>
                    </div>
                    <br>
                    <button @click="go" class="btn btn-sml btn-success">Go</button>

                    <button @click="add('active')" class="btn btn-success"><i class="fa fa-bolt"></i></button>
                    <button @click="add('archived')" class="btn btn-secondary"><i class="fa fa-book"></i></button>
                    <button @click="add('next')" class="btn btn-success"><i class="fa fa-lightbulb"></i></button>
                    <button @click="sync" class="btn btn-info"><i class="fa fa-sync"></i></button>

                </div>
            </div>
            <div class="col-md-9">
                <list
                    @populate="populate"
                    :added="added"
                ></list>
            </div>
        </div>

        <pair
            @lasts="sendLasts"
            :symbols="value"
            :type="marketType"
            :candleType="candleType"
        ></pair>
        <br>
        <br>
        <div class="container">
            <controls
                :symbol1="v1.toUpperCase()"
                :symbol2="v2.toUpperCase()"
            ></controls>
            <!--            <limits></limits>-->
        </div>
        <div>
<!--            <pair-record-->
<!--                :latest-data-route="latestDataRoute"-->
<!--                :balance-route="balanceRoute"-->
<!--                :value="value"-->
<!--                :push-lasts="pushLasts">-->
<!--            </pair-record>-->
            <br>
            <results
                :candleType="candleType"
                :marketType="marketType"
                @show="show"
            >
            </results>
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
            candleType: "1d",
            value: '',
            v1: "",
            v2: "",
            symbols: {
                binance: [],
                kucoin: [],
            },
            marketType: "kucoin", //could be kucoin
            added: [],
            pushLasts: [],
            open: false,
        }
    },

    methods: {
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

        sync: function() {
            axios.get("/sync");
        },

        add: function(state) {
            let _this = this;
            axios
                .post("/pairs", {
                    params: {
                        s1: this.v1.toUpperCase(),
                        s2: this.v2.toUpperCase(),
                        state: state,
                    },
                })
                .then(function() {
                    _this.added = [
                        {"s1": _this.v1},
                        {"s2": _this.v2}
                    ]
                });
        },
        show: function(symbol1, symbol2) {
            this.v1 = symbol1;
            this.v2 = symbol2;

            this.value = [
                {"name": (symbol1).toUpperCase()},
                {"name": (symbol2).toUpperCase()},
            ]
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
