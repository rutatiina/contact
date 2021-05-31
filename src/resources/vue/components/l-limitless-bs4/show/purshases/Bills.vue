<template>

    <div>

        <loading-animation></loading-animation>

        <!-- Basic table -->
        <div class="card shadow-none rounded-0 border-0">

            <div class="card-body" v-if="!this.$root.loading && tableData.settingsDisplay">


                <div class="form-group row mb-0">
                    <label class="col-lg-1 col-form-label text-right bg-light border rounded-left border-right-0"
                           style="white-space: nowrap;">
                        Search by column:
                    </label>
                    <div class="col-lg-2 pl-0">
                        <model-select
                            :options="tableData.searchColumnOptions"
                            v-model="tableData.searchColumn"
                            class="rounded-left-0"
                            placeholder="Choose column">
                        </model-select>
                    </div>
                    <div class="col-lg-6">
                        <input type="text"
                               v-model="tableData.searchValue"
                               class="form-control h-100 input-roundless"
                               placeholder="Search by column">
                    </div>

                    <label class="col-lg-1 col-form-label text-right bg-light border rounded-left border-right-0"
                           style="white-space: nowrap;">
                        Records per page:
                    </label>
                    <div class="col-lg-1 pl-0">
                        <model-select
                            :options="tableData.recordsPerPageOptions"
                            v-model="tableData.recordsPerPage"
                            class="rounded-left-0"
                            placeholder="...">
                        </model-select>
                    </div>
                    <div class="col-lg-1">
                        <button type="button"
                                @click="tableDataUpdate"
                                class="btn btn-danger rounded border-2 border-danger-400 w-100 h-100 pl-2 pr-2">
                            <i class="icon-cog"></i> Search
                        </button>
                    </div>
                </div>

            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-active">
                            <th scope="col" class="font-weight-bold" style="width: 20px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           v-model="rgTableSelectAll"
                                           class="custom-control-input"
                                           id="row-checkbox-all">
                                    <label class="custom-control-label" for="row-checkbox-all"> </label>
                                </div>
                            </th>
                            <th scope="col" class="font-weight-bold">Date</th>
                            <th scope="col" class="font-weight-bold" nowrap="">Document No</th>
                            <th scope="col" class="font-weight-bold" nowrap="">Reference</th>
                            <th scope="col" class="font-weight-bold">Status</th>
                            <th scope="col" class="font-weight-bold" nowrap>Due date</th>
                            <th scope="col" class="font-weight-bold text-right" nowrap>Total</th>
                            <th scope="col" class="font-weight-bold text-right" nowrap>Balance</th>
                        </tr>
                    </thead>

                    <rg-tables-state></rg-tables-state>

                    <tbody>
                        <tr v-for="row in tableData.payload.data"
                            @click="onRowClick(row)">
                            <td v-on:click.stop="" class="pr-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           v-model="tableData.selected"
                                           :value="row.id"
                                           number
                                           class="custom-control-input"
                                           :id="'row-checkbox-'+row.id"
                                           isabled>
                                    <label class="custom-control-label" :for="'row-checkbox-'+row.id"> </label>
                                </div>
                            </td>
                            <td class="cursor-pointer" nowrap >{{row.date}}</td>
                            <td class="cursor-pointer">{{row.number}}</td>
                            <td class="cursor-pointer">{{row.reference}}</td>
                            <td class="cursor-pointer">{{row.status}}</td>
                            <td class="cursor-pointer">{{row.due_date}}</td>
                            <td class="cursor-pointer font-weight-bold text-right">
                                <span class="text-slate-800">{{rgNumberFormat(row.total, 2)}}</span>
                                <small>{{row.base_currency}}</small>
                            </td>
                            <td class="cursor-pointer font-weight-bold text-right">
                                <span class="text-danger-800">{{rgNumberFormat(row.balance, 2)}}</span>
                                <small>{{row.base_currency}}</small>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <rg-tables-pagination></rg-tables-pagination>

            </div>

        </div>
        <!-- /basic table -->

    </div>

</template>

<script>

    export default {
        data() {
            return {
                url: '/bills'
            }
        },
        mounted() {

            this.tableData.searchColumnOptions = [
                { value: 'date', text: 'Date' },
                { value: 'number', text: 'Document No' },
                { value: 'reference', text: 'Reference' },
                { value: 'contact_name', text: 'Contact name' },
                { value: 'status', text: 'Status' },
                { value: 'expiry_date', text: 'Expiry date' },
                { value: 'total', text: 'Total' }
            ]
            this.tableData.parameters = {
                contact: this.$route.params.id
            }
            this.tableData.url = this.url //initiates this.tableRecordsPerPage
            this.tableData.initiate = true

            //page height - 230(page header and breadcrump) - 80 (lower space) / 45 (height of each row)
            this.tableRecordsPerPage(230, 80, 45)

        },
        methods: {
            onRowClick(txn) {
                //console.log(txn)
                this.$router.push({ path: this.url + '/'+txn.id })
            }
        },
        ready:function(){},
        beforeUpdate: function () {},
        updated: function () {
            InputsCheckboxesRadios.initComponents();
        }
    }
</script>
