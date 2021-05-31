<template>

    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Content area -->
        <div class="content border-0 p-0">

            <!-- Inner container -->
            <div class="d-md-flex align-items-md-start">

                <!-- Left sidebar component -->
                <div class="sidebar sidebar-light bg-transparent sidebar-component sidebar-component-left wmin-300 shadow-0 sidebar-expand-md mr-0 border-right-1 border-left-0 border-top-0 border-bottom-0">

                    <!-- Sidebar content -->
                    <div class="sidebar-content">
                        <div id="rg-sidebar-secondary-fixed-content-scroll"
                             class="position-fixed h-100 border-right-1"
                             style="width: 299px; overflow: hidden; padding-bottom:80px !important; border-right-color: rgba(0,0,0,.125);">

                            <!-- Navigation -->
                            <div class="card border-0 shadow-0 rounded-0">
                                <div class="card-body text-center card-img-top">
                                    <div class="card-img-actions d-inline-block mb-3">
                                        <img class="img-fluid rounded-circle" src="/template/l/global_assets/images/placeholders/placeholder.jpg" width="170" height="170" alt="">
                                        <div class="card-img-actions-overlay rounded-circle">
                                            <a href="#" class="btn btn-outline bg-white text-white border-white border-2 btn-icon rounded-round">
                                                <i class="icon-plus3"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline bg-white text-white border-white border-2 btn-icon rounded-round ml-2">
                                                <i class="icon-link"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <h6 class="font-weight-semibold mb-0">{{contact.name}}</h6>
                                    <div class="d-block opacity-75">
                                        <span v-for="type in contact.types" class="badge badge-light badge-pill font-weight-bold text-capitalize"> {{type}} </span>
                                    </div>

                                    <div class="list-icons list-icons-extended mt-3">
                                        <a v-bind:class="'list-icons-item ' + [contact.facebook_link ? '' : 'btn-link disabled']"
                                           :href="contact.facebook_link" data-popup="tooltip" title="" data-container="body" data-original-title="Facebook"><i class="icon-facebook2"></i></a>
                                        <a v-bind:class="'list-icons-item ' + [contact.twitter_link ? '' : 'btn-link disabled']"
                                           :href="contact.twitter_link" class="list-icons-item" data-popup="tooltip" title="" data-container="body" data-original-title="Twitter"><i class="icon-twitter"></i></a>
                                        <a v-bind:class="'list-icons-item ' + [contact.instagram_link ? '' : 'btn-link disabled']"
                                           :href="contact.instagram_link" class="list-icons-item" data-popup="tooltip" title="" data-container="body" data-original-title="Instagram"><i class="icon-instagram"></i></a>
                                        <a v-bind:class="'list-icons-item ' + [contact.linkedin_link ? '' : 'btn-link disabled']"
                                           :href="contact.linkedin_link" class="list-icons-item" data-popup="tooltip" title="" data-container="body" data-original-title="Linkedin"><i class="icon-linkedin"></i></a>
                                        <a v-bind:class="'list-icons-item ' + [contact.github_link ? '' : 'btn-link disabled']"
                                           :href="contact.github_link" class="list-icons-item" data-popup="tooltip" title="" data-container="body" data-original-title="Github"><i class="icon-github"></i></a>
                                    </div>
                                </div>

                                <div class="card-body p-0">
                                    <ul class="nav nav-sidebar mb-2">
                                        <li class="nav-item-header">Basic</li>
                                        <li class="nav-item">
                                            <a href="#profile" class="nav-link active" data-toggle="tab">
                                                <i class="icon-user"></i>
                                                My profile
                                            </a>
                                        </li>
                                        <!--<li class="nav-item">
                                            <a href="#statement" class="nav-link" data-toggle="tab">
                                                <i class="icon-calendar3"></i>
                                                Statement
                                            </a>
                                        </li>-->
                                        <li class="nav-item-header">Transactions</li>
                                        <li class="nav-item">
                                            <a href="#sales"  class="nav-link" data-toggle="tab" @click="tabSales = 'sales-estimates'">
                                                <i class="icon-cart"></i>
                                                Sales
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#purchases" class="nav-link" data-toggle="tab" @click="tabPurchases = 'purchases-expenses'">
                                                <i class="icon-price-tag"></i>
                                                Purchases
                                            </a>
                                        </li>
                                        <li class="nav-item-header">Remarks & Comments</li>
                                        <li class="nav-item">
                                            <a href="#remarks" class="nav-link" data-toggle="tab">
                                                <i class="icon-menu6"></i>
                                                Remarks
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#comments" class="nav-link" data-toggle="tab">
                                                <i class="icon-comments"></i>
                                                Comments
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <!-- /navigation -->

                        </div>
                    </div>
                    <!-- /sidebar content -->

                </div>
                <!-- /left sidebar component -->

                <div class="tab-content w-100 overflow-hidden">

                    <!-- Page header -->
                    <div class="page-header page-header-light d-print-none">
                        <div class="page-header-content header-elements-md-inline">
                            <div class="page-title d-flex">
                                <h4>
                                    <i class="icon-files-empty2 mr-2"></i>
                                    <span class="font-weight-semibold">{{contact.display_name}}</span>
                                </h4>
                                <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                            </div>

                        </div>

                        <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                            <div class="d-flex">
                                <div class="breadcrumb">
                                    <a href="/" class="breadcrumb-item">
                                        <i class="icon-home2 mr-2"></i>
                                        <span class="badge badge-primary badge-pill font-weight-bold rg-breadcrumb-item-tenant-name"> {{this.$root.tenant.name | truncate(30) }} </span>
                                    </a>
                                    <span class="breadcrumb-item">Contacts</span>
                                    <span class="breadcrumb-item active">{{contact.name}}</span>
                                </div>

                                <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                            </div>

                            <div class="header-elements">
                                <div class="breadcrumb justify-content-center">
                                    <router-link :to="'/contacts/'+contact.id+'/edit'"
                                                 class="btn btn-outline btn-danger border-danger text-danger-800 border-2 btn-sm rounded font-weight-bold mr-2">
                                        <i class="icon-pencil5 mr-1"></i>
                                        Edit Contact
                                    </router-link>

                                    <div class="btn-group" v-if="!this.$root.loadingTxn && txnData">
                                        <button type="button"
                                                class="btn btn-sm dropdown-toggle btn-warning border-warning border-2 font-weight-bold"
                                                data-toggle="dropdown">
                                            <i class="icon-cog mr-1"></i> Process to </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <router-link :to="'/financial-accounts/sales/estimates/'+txnData.id+'/process/invoice'" class="dropdown-item"><i class="icon-files-empty2"></i> Invoice</router-link>
                                            <div class="dropdown-divider"></div>
                                            <router-link :to="'/financial-accounts/sales/estimates/'+txnData.id+'/process/retainer-invoices'" class="dropdown-item"><i class="icon-files-empty2"></i> Retainer Invoice</router-link>
                                            <router-link :to="'/financial-accounts/sales/estimates/'+txnData.id+'/process/sales-orders'" class="dropdown-item"><i class="icon-files-empty2"></i> Sales Order</router-link>
                                            <router-link :to="'/financial-accounts/sales/estimates/'+txnData.id+'/process/recurring-invoices'" class="dropdown-item"><i class="icon-files-empty2"></i> Recurring Invoice</router-link>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /page header -->

                    <div class="tab-pane fade active show" id="profile">

                        <div class="card card-body shadow-none border-0">
                            <div class="row">
                                <div class="col-12">
                                    <apex-chart-incomes-and-expenses></apex-chart-incomes-and-expenses>
                                </div>
                            </div>
                        </div>

                        <div class="card card-body rounded-0 border-0 shadow-0">
                            <div class="row text-center">
                                <div class="col-2">
                                    <p><i class="icon-earth icon-2x d-inline-block text-info"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.country}}</h5>
                                    <span class="text-muted font-size-sm">Country</span>
                                </div>

                                <div class="col-2">
                                    <p><i class="icon-cash4 icon-2x d-inline-block text-warning"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.currency}}</h5>
                                    <span class="text-muted font-size-sm">Currency</span>
                                </div>

                                <div class="col-2">
                                    <p><i class="icon-wallet icon-2x d-inline-block text-success"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.payment_terms || '-'}}</h5>
                                    <span class="text-muted font-size-sm">Payment terms</span>
                                </div>

                                <div class="col-2">
                                    <p><i class="icon-newspaper2 icon-2x d-inline-block text-success"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.statistics.orders || '-'}}</h5>
                                    <span class="text-muted font-size-sm">Orders</span>
                                </div>

                                <div class="col-2">
                                    <p><i class="icon-stack-text icon-2x d-inline-block text-info"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.statistics.invoices || '-'}}</h5>
                                    <span class="text-muted font-size-sm">Invoices</span>
                                </div>

                                <div class="col-2">
                                    <p><i class="icon-windows2 icon-2x d-inline-block text-warning"></i></p>
                                    <h5 class="font-weight-semibold mb-0">{{contact.statistics.bills || '-'}}</h5>
                                    <span class="text-muted font-size-sm">Bills</span>
                                </div>
                            </div>
                        </div>

                        <div class="row pl-3 pr-3">

                            <div class="col-lg-6">
                                <div class="card border-left-3 border-left-danger rounded-left-0">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
                                            <div>
                                                <h6 class="font-weight-semibold">Billing Address</h6>
                                                <ul class="list list-unstyled mb-0">
                                                    <li>Attention: {{contact.billing_address_attention}}</li>
                                                    <li>Street #1: {{contact.billing_address_street1}}</li>
                                                    <li>Street #2: {{contact.billing_address_street2}}</li>
                                                    <li>City: {{contact.billing_address_city}}</li>

                                                </ul>
                                            </div>

                                            <div class="text-sm-right mb-0 mt-3 mt-sm-0 ml-auto">
                                                <h6 class="font-weight-semibold">&nbsp;</h6>
                                                <ul class="list list-unstyled mb-0">
                                                    <li>State: {{contact.billing_address_state}}</li>
                                                    <li>
                                                        Fax: <span class="font-weight-semibold">{{contact.billing_address_fax}}</span>
                                                    </li>
                                                    <li class="dropdown">
                                                        Zip code:
                                                        <span
                                                              class="badge bg-danger-400 align-top">{{contact.billing_address_zip_code}}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer d-sm-flex justify-content-sm-between align-items-sm-center">
                                                <span>
                                                    Country:
                                                    <span class="font-weight-semibold">{{contact.billing_address_country}}</span>
                                                </span>

                                        <ul class="list-inline list-inline-condensed mb-0 mt-2 mt-sm-0">
                                            <li class="list-inline-item dropdown">
                                                <a href="#" class="text-default dropdown-toggle" data-toggle="dropdown"><i class="icon-menu7"></i></a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="#" class="dropdown-item"><i class="icon-printer"></i> Print invoice</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-file-download"></i> Download invoice</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" class="dropdown-item"><i class="icon-file-plus"></i> Edit invoice</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-cross2"></i> Remove invoice</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card border-left-3 border-left-success rounded-left-0">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
                                            <div>
                                                <h6 class="font-weight-semibold">Shipping Address</h6>
                                                <ul class="list list-unstyled mb-0">
                                                    <li>Attention: {{contact.shipping_address_attention}}</li>
                                                    <li>Street #1: {{contact.shipping_address_street1}}</li>
                                                    <li>Street #2: {{contact.shipping_address_street2}}</li>
                                                    <li>City: {{contact.shipping_address_city}}</li>

                                                </ul>
                                            </div>

                                            <div class="text-sm-right mb-0 mt-3 mt-sm-0 ml-auto">
                                                <h6 class="font-weight-semibold">&nbsp;</h6>
                                                <ul class="list list-unstyled mb-0">
                                                    <li>State: {{contact.shipping_address_state}}</li>
                                                    <li>
                                                        Fax: <span class="font-weight-semibold">{{contact.shipping_address_fax}}</span>
                                                    </li>
                                                    <li class="dropdown">
                                                        Zip code:
                                                        <span
                                                              class="badge bg-danger-400 align-top">{{contact.shipping_address_zip_code}}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer d-sm-flex justify-content-sm-between align-items-sm-center">
                                                <span>
                                                    Country:
                                                    <span class="font-weight-semibold">{{contact.shipping_address_country}}</span>
                                                </span>

                                        <ul class="list-inline list-inline-condensed mb-0 mt-2 mt-sm-0">
                                            <li class="list-inline-item dropdown">
                                                <a href="#" class="text-default dropdown-toggle" data-toggle="dropdown"><i class="icon-menu7"></i></a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="#" class="dropdown-item"><i class="icon-printer"></i> Print invoice</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-file-download"></i> Download invoice</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" class="dropdown-item"><i class="icon-file-plus"></i> Edit invoice</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-cross2"></i> Remove invoice</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="tab-pane fade" id="sales">

                        <div class="card border-0 shadow-0 m-0">

                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-tabs-bottom font-weight-bold mb-0">
                                    <li class="nav-item">
                                        <a href="#sales-estimates" @click="tabSales = 'sales-estimates'"
                                           class="nav-link active" data-toggle="tab">Estimates</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-retainer-invoices" @click="tabSales = 'sales-retainer-invoices'"
                                           class="nav-link" data-toggle="tab">Retainer Invoices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-sales-orders" @click="tabSales = 'sales-sales-orders'"
                                           class="nav-link" data-toggle="tab">Sales Orders</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-invoices" @click="tabSales = 'sales-invoices'"
                                           class="nav-link" data-toggle="tab">Invoices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-receipts" @click="tabSales = 'sales-receipts'"
                                           class="nav-link" data-toggle="tab">Receipts</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-recurring-invoices" @click="tabSales = 'sales-recurring-invoices'"
                                           class="nav-link" data-toggle="tab">Recurring Invoices</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sales-credit-notes" @click="tabSales = 'sales-credit-notes'"
                                           class="nav-link" data-toggle="tab">Credit Notes</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="sales-estimates" v-if="tabSales === 'sales-estimates'">
                                        <sales-estimates></sales-estimates>
                                    </div>

                                    <div class="tab-pane fade" id="sales-retainer-invoices" v-if="tabSales === 'sales-retainer-invoices'">
                                        <sales-retainer-invoices></sales-retainer-invoices>
                                    </div>

                                    <div class="tab-pane fade" id="sales-sales-orders" v-if="tabSales === 'sales-sales-orders'">
                                        <sales-sales-orders></sales-sales-orders>
                                    </div>

                                    <div class="tab-pane fade" id="sales-invoices" v-if="tabSales === 'sales-invoices'">
                                        <sales-invoices></sales-invoices>
                                    </div>

                                    <div class="tab-pane fade" id="sales-receipts" v-if="tabSales === 'sales-receipts'">
                                        <sales-receipts></sales-receipts>
                                    </div>

                                    <div class="tab-pane fade" id="sales-recurring-invoices" v-if="tabSales === 'sales-recurring-invoices'">
                                        <sales-recurring-invoices></sales-recurring-invoices>
                                    </div>

                                    <div class="tab-pane fade" id="sales-credit-notes" v-if="tabSales === 'sales-credit-notes'">
                                        <sales-credit-notes></sales-credit-notes>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade" id="purchases">

                        <div class="card border-0 shadow-0 m-0">

                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-tabs-bottom font-weight-bold mb-0">
                                    <li class="nav-item">
                                        <a href="#purchases-expenses" @click="tabPurchases = 'purchases-expenses'"
                                           class="nav-link active" data-toggle="tab">Expenses</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-recurring-expenses" @click="tabPurchases = 'purchases-recurring-expenses'"
                                           class="nav-link" data-toggle="tab">Recurring Expenses</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-purchase-orders" @click="tabPurchases = 'purchases-purchase-orders'"
                                           class="nav-link" data-toggle="tab">Purchase Orders</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-bills" @click="tabPurchases = 'purchases-bills'"
                                           class="nav-link" data-toggle="tab">Bills</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-payments" @click="tabPurchases = 'purchases-payments'"
                                           class="nav-link" data-toggle="tab">Payments</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-recurring-bills" @click="tabPurchases = 'purchases-recurring-bills'"
                                           class="nav-link" data-toggle="tab">Recurring Bills</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#purchases-debit-notes" @click="tabPurchases = 'purchases-debit-notes'"
                                           class="nav-link" data-toggle="tab">Debit Notes</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="purchases-expenses" v-if="tabPurchases === 'purchases-expenses'">
                                        <purchases-expenses></purchases-expenses>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-recurring-expenses" v-if="tabPurchases === 'purchases-recurring-expenses'">
                                        <purchases-recurring-expenses></purchases-recurring-expenses>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-purchase-orders" v-if="tabPurchases === 'purchases-purchase-orders'">
                                        <purchases-purchase-orders></purchases-purchase-orders>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-bills" v-if="tabPurchases === 'purchases-bills'">
                                        <purchases-bills></purchases-bills>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-payments" v-if="tabPurchases === 'purchases-payments'">
                                        <purchases-payments></purchases-payments>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-recurring-bills" v-if="tabPurchases === 'purchases-recurring-bills'">
                                        <purchases-recurring-bills></purchases-recurring-bills>
                                    </div>

                                    <div class="tab-pane fade" id="purchases-debit-notes" v-if="tabPurchases === 'purchases-debit-notes'">
                                        <purchases-debit-notes></purchases-debit-notes>
                                    </div>

                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="tab-pane fade" id="remarks">

                        <div class="card border-0 shadow-0 m-0">
                            <div class="card-body">
                                Remarks coming soon
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade" id="comments">

                        <div class="card border-0 shadow-0 m-0">
                            <div class="card-body">
                                Comments coming soon
                            </div>
                        </div>

                    </div>

                </div>

            </div>
            <!-- /inner container -->

        </div>
        <!-- Footer -->

        <!-- /footer -->

    </div>
    <!-- /main content -->

</template>

<script>

    import ApexChartIncomesAndExpenses from './apex-charts/IncomesAndExpenses.vue'

    import SalesCreditNotes from './sales/CreditNotes'
    import SalesEstimates from './sales/Estimates'
    import SalesInvoices from './sales/Invoices'
    import SalesReceipts from './sales/Receipts'
    import SalesRecurringInvoices from './sales/RecurringInvoices'
    import SalesRetainerInvoices from './sales/RetainerInvoices'
    import SalesSalesOrders from './sales/SalesOrders'

    import PurchasesBills from './purshases/Bills'
    import PurchasesDebitNotes from './purshases/DebitNotes'
    import PurchasesExpenses from './purshases/Expenses'
    import PurchasesPayments from './purshases/Payments'
    import PurchasesPurchaseOrders from './purshases/PurchaseOrders'
    import PurchasesRecurringBills from './purshases/RecurringBills'
    import PurchasesRecurringExpenses from './purshases/RecurringExpenses'

    export default {
        components: {
            ApexChartIncomesAndExpenses,

            SalesCreditNotes,
            SalesEstimates,
            SalesInvoices,
            SalesReceipts,
            SalesRecurringInvoices,
            SalesRetainerInvoices,
            SalesSalesOrders,

            PurchasesBills,
            PurchasesDebitNotes,
            PurchasesExpenses,
            PurchasesPayments,
            PurchasesPurchaseOrders,
            PurchasesRecurringBills,
            PurchasesRecurringExpenses
        },
        data() {
            return {
                PerfectScrollbar: null,
                url: '/financial-accounts/transactions/sort-by-contact?contact_id='+this.$route.params.id,
                tabSales: null,
                tabPurchases: null,
                contact: {
                    id: this.$route.params.id,
                    currencies: [],
                    statistics: {}
                }
            }
        },
        watch: {
            '$route.query.page': function (page) {
                this.tableData.url = this.url+'&page='+page;
            }
        },
        mounted() {
            this.$root.appMenu('accounting')

            this.PerfectScrollbar = new PerfectScrollbar('#rg-sidebar-secondary-fixed-content-scroll');

            this.fetchContact()

            this.tableData.searchColumnOptions = [
                { value: 'date', text: 'Date' },
                { value: 'number', text: 'Document No' },
                { value: 'reference', text: 'Reference' },
                { value: 'contact_name', text: 'Contact name' },
                { value: 'status', text: 'Status' },
                { value: 'expiry_date', text: 'Expiry date' },
                { value: 'total', text: 'Total' }
            ]

            // this.tableData.initiate = true //this has been commented out because the default transactions display is throwing errors

            //page height - 230(page header and breadcrump) - 80 (lower space) / 45 (height of each row)
            this.tableRecordsPerPage(230, 80, 45)

            let currentObj = this;

            if (currentObj.$route.query.page === undefined) {
                currentObj.tableData.url = this.url;
            } else {
                currentObj.tableData.url = this.url+'&page='+currentObj.$route.query.page;
            }


        },
        methods: {
            async fetchContact() {
                try {

                    return await axios.get(this.$route.fullPath)
                        .then(response => {
                            this.contact = response.data
                        })
                        .catch(function (error) {
                            // handle error
                            console.log(error); //test
                        })
                        .finally(function (response) {
                            // always executed this is supposed
                        })

                } catch (e) {
                    console.log(e); //test
                }
            },
            onRowClick(txn) {
                //console.log(txn)
                //this.$router.push({ path: '/financial-accounts/sales/estimates/'+txn.id })
            }
        },
        ready:function(){},
        beforeUpdate: function () {},
        updated: function () {
            InputsCheckboxesRadios.initComponents();

            //update scrollable are on data change
            this.PerfectScrollbar.update();
        }
    }

</script>
