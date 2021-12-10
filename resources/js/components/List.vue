<template>
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a @click="tab('active')" class="nav-link" :class="state === 'active' ? 'active' : ''" aria-current="page"><i class="fa fa-bolt"></i></a>
            </li>
            <li class="nav-item">
                <a @click="tab('archived')" class="nav-link" :class="state === 'archived' ? 'active' : ''"><i class="fa fa-book"></i></a>
            </li>
            <li class="nav-item">
                <a @click="tab('next')" class="nav-link" :class="state === 'next' ? 'active' : ''"><i class="fa fa-lightbulb"></i></a>
            </li>
        </ul>
        <ul class="col-list">
            <li v-for="pair in pairs">
                <p @click="populate(pair.s1, pair.s2)" class="btn-link mr-2">{{ (pair.s1 + pair.s2).toUpperCase() }}</p>
                <i @click="deletePair(pair.id)" class="fa fa-times grey-cross"></i>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: [
        "added",
        "spr",
        "cpr",
    ],
    data: function() {
        return {
            pairs: [],
            totalPairs: [],
            state: 'active',
        };
    },

    mounted() {
        this.getSavedPairs(this.state);
    },

    methods: {
        getSavedPairs(state) {
            axios
                .get('/pairs', {
                    params: {
                        state: state,
                    }
                })
                .then((response) => (this.pairs = response.data))
                .catch((error) => console.log(error));
        },

        populate(s1, s2) {
            this.$emit('populate', s1, s2);
        },

        tab(state) {
            this.state = state;
            this.getSavedPairs(state);
        },

        deletePair(id) {
            let _this = this;
            axios
                .post("/pairs/delete", {
                    params: {
                        id: id,
                    },
                })
                .then(function() {
                    _this.getSavedPairs(this.state);
                });
        }
    },

    watch: {
        added: function() {
            this.getSavedPairs(this.state);
        }
    }
}

</script>
<style lang="scss">
.nav-link {
    cursor: pointer;
}
.col-list {
    cursor: pointer;
    columns: 3;
    -webkit-columns: 3;
    -moz-columns: 3;

    p {
        display: contents;
        font-size: 20px;
        font-family: monospace;
    }

    li {
        list-style-type: none;
    }

    i {
        cursor: pointer;
    }

    .grey-cross {
        color: #777;
    }
}
</style>
