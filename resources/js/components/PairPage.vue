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
        <pair
            @lasts="sendLasts"
            :symbols="value"
            :marketType="marketType"
        ></pair>
        <br>
        <div class="container">
<!--            <controls-->
<!--                :symbol1="v1.toUpperCase()"-->
<!--                :symbol2="v2.toUpperCase()"-->
<!--                :cr="cr"-->
<!--                :position-route="positionRoute"-->
<!--                :pr="pr"-->
<!--                :record-route="recordRoute"-->
<!--                :transfer-route="transferRoute"-->
<!--            >-->
<!--            </controls>-->
<!--            <pair-record-->
<!--                :latest-data-route="latestDataRoute"-->
<!--                :balance-route="balanceRoute"-->
<!--                :value="value"-->
<!--                :push-lasts="pushLasts">-->
<!--            </pair-record>-->
        </div>
    </div>
</template>

<script>

export default {
    props: [],

    data: function () {
        return {
            value: '',
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

    mounted() {
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
