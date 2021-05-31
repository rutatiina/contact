<template>

    <!-- cards go here -->
    <div id="chart">
        <apexchart type=line height=450 :options="chartOptions" :series="series" />
    </div>

</template>

<script>

    import VueApexCharts from 'vue-apexcharts'

    export default {
        name: 'ApexChartsArea',
        components: {
            apexchart: VueApexCharts,
        },
        data() {
            return {
                series: [],
                chartOptions: {}
            }
        },
        methods: {
            async fetchMonthlyExpense() {
                try {
                    return await axios.get('/financial-accounts/dashboard/incomes-and-expense', { params: { contact: this.$route.params.id } })
                        .then(response => {
                            //*
                            //console.log(response.data.chartOptions)
                            let currentObj = this;
                            let co = response.data.chartOptions;
                            co.xaxis.labels.formatter = function (value) {};
                            co.yaxis.labels.formatter = function (value) {
                                return currentObj.$root.tenant.base_currency + ' ' + currentObj.rgNumberFormat(value, 2);
                            }
                            //console.log(co)
                            //*/
                            this.series = response.data.series
                            this.chartOptions = co //response.data.chartOptions
                        })

                } catch (e) {
                    //console.log(e);
                }
            }
        },
        mounted() {
            this.fetchMonthlyExpense()
        },
    }
</script>
