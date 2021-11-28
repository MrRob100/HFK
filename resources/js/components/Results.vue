<template>
    <div class="container">
        <input class="form-control col-3" type="text" v-model="frozen" placeholder="frozen coin">
        <br>
        <table class="table table-responsive table-striped table-sm">
            <thead>
            <th>Pair</th>
            <th>Candle Type</th>
            <th>USN 25 50</th>
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
                    {{ result.usn_25_50 }}
                </td>
                <td>
                    <button class="btn btn-success btn-xs" @click="showResult(result.symbol1, result.symbol2, index)">Show</button>
                </td>
            </tr>
            </tbody>
        </table>
        <button v-if="page <= 1" class="btn btn-info" disabled><i class="fa fa-arrow-left"></i></button>
        <button v-if="page > 1" @click="getResults(page - 1)" class="btn btn-info"><i class="fa fa-arrow-left"></i></button>
        {{ page }}
        <button @click="getResults(page + 1)" class="btn btn-info"><i class="fa fa-arrow-right"></i></button>
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
            index: null,
            frozen: null,
            page: 1,
        };
    },

    mounted() {
        this.getResults();
    },

    methods: {
        getResults: function(page = 1) {
            this.page = page;
            axios.get('/results', {
                params: {
                    candleType: this.candleType,
                    frozen: this.frozen,
                    page: page,
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
        frozen: function() {
            if (this.frozen.length > 2 || !this.frozen || this.frozen === "") {
                this.getResults();
            }
        },
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
