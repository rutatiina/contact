<template>

    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4>
                        <i class="icon-file-plus"></i>
                        {{pageTitle}}
                    </h4>

                    <!--<div class="btn-group btn-xs btn-group-animated p-0 mr-20 float-right ">
                        <button type="button" class="btn btn-danger btn-labeled pr-20 import_btn" data-import="contacts" data-url="">
                            <b><i class="icon-download4"></i></b> Import contacts
                        </button>
                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown"><span
                            class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="">
                                    <i class="icon-file-download"></i> Download template</a>
                            </li>
                        </ul>
                    </div>-->

                </div>

            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="index.html" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Contacts</a>
                        <span class="breadcrumb-item active">Create</span>
                    </div>

                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

                <div class="header-elements">
                    <div class="breadcrumb justify-content-center">
                        <router-link to="/contacts" class=" btn btn-danger btn-sm rounded-round font-weight-bold">
                            <i class="icon-users2 mr-1"></i>
                            Contacts
                        </router-link>
                    </div>
                </div>

            </div>

        </div>
        <!-- /page header -->

        <!-- Content area -->
        <div class="content border-0 padding-0">

            <!-- Form horizontal -->
            <div class="card shadow-none rounded-0 border-0">

                <div class="card-body p-0">

                    <LoadingComponent />

                    <form id="counterparty_update_form"
                          v-if="!this.$root.loading"
                          @submit="formSubmit"
                          action=""
                          method="post"
                          class="max-width-820"
                          style="margin-bottom: 100px;"
                          autocomplete="off">


                        <fieldset class="pt-20">

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label"> </label>
                                <div class="col-lg-10">

                                        <div v-for="(type, index) in types" class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" v-model="attributes.types" :value="type" class="custom-control-input" :id="'contact-type-'+index" checked>
                                            <label class="custom-control-label text-capitalize" :for="'contact-type-'+index">{{type.replace('-', ' ')}}</label>
                                        </div>

                                </div>
                            </div>

                        </fieldset>


                        <fieldset class="">

                            <div class="form-group row">
                                <label class="col-lg-2 control-label">
                                    Contact name:
                                </label>
                                <div class="col-lg-2">
                                    <model-select
                                        :options="globalsSalutations"
                                        v-model="attributes.salutation"
                                        placeholder="Salutation ...">
                                    </model-select>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" v-model="attributes.name" value=""
                                           class="form-control "
                                           placeholder="Full name">
                                </div>

                            </div>

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label">
                                    Display name:
                                </label>
                                <div class="col-lg-10">
                                        <input type="text" v-model="attributes.display_name" value="" class="form-control " placeholder="Display name">
                                </div>
                            </div>

                        </fieldset>

                        <div class="card shadow-none rounded-0 border-0 mb-0">
                            <div class="card-body pb-0 pt-2">
                                <ul class="nav nav-tabs nav-tabs-bottom border-bottom-0 font-weight-semibold">
                                    <li class="nav-item">
                                        <a href="#bottom-divided-tab1" data-toggle="tab" class="nav-link active">Other details</a></li>
                                    <li class="nav-item">
                                        <a href="#bottom-divided-tab2" data-toggle="tab" class="nav-link">Address</a></li>
                                    <li class="nav-item">
                                        <a href="#bottom-divided-tab3" data-toggle="tab" class="nav-link">Contact persons</a></li>
                                    <li class="nav-item">
                                        <a href="#bottom-divided-tab4" data-toggle="tab" class="nav-link">Remarks</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#bottom-divided-tab5" data-toggle="tab" class="nav-link">Is Tax body?</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card p-0 shadow-none border-0">
                            <div class="card-body p-0">

                                <div class="tab-content">

                                    <div class="tab-pane active" id="bottom-divided-tab1">

                                        <fieldset class="">

                                            <div class="form-group row">

                                                <label class="col-lg-2 col-form-label">
                                                    Country
                                                </label>
                                                <div class="col-lg-10">
                                                    <model-list-select :list="globalsCountries"
                                                                       v-model="attributes.country"
                                                                       option-value="value"
                                                                       option-text="text"
                                                                       placeholder="Select item">
                                                    </model-list-select>
                                                </div>

                                            </div>

                                            <div class="form-group row">

                                                <label class="col-lg-2 col-form-label">
                                                    Default currency
                                                </label>
                                                <div class="col-lg-10">
                                                    <model-list-select :list="globalsCurrencies"
                                                                       v-model="attributes.currency"
                                                                       option-value="value"
                                                                       option-text="text"
                                                                       placeholder="Select currency">
                                                    </model-list-select>
                                                </div>

                                            </div>

                                            <div class="form-group row">

                                                <label class="col-lg-2 col-form-label">
                                                    Currencies
                                                </label>
                                                <div class="col-lg-10">
                                                    <multi-list-select
                                                        :list="globalsCurrencies"
                                                        option-value="value"
                                                        option-text="text"
                                                        :selected-items="globalsCurrenciesSelected"
                                                        placeholder="Currencies"
                                                        @select="setCurrencies">
                                                    </multi-list-select>
                                                </div>

                                            </div>

                                            <div class="form-group row">
                                                <label class="col-lg-2 col-form-label">
                                                    Payment terms
                                                </label>
                                                <div class="col-lg-10">
                                                    <model-select
                                                        :options="globalsPaymentTerms"
                                                        v-model="attributes.payment_terms"
                                                        placeholder="Payment terms ...">
                                                    </model-select>
                                                </div>

                                            </div>

                                            <div class="form-group row">

                                                <label class="col-lg-2 col-form-label">
                                                    Facebook
                                                </label>
                                                <div class="col-lg-10">
                                                    <input type="text" v-model="attributes.facebook_link" value=""
                                                           class="form-control "
                                                           placeholder="Facebook link">
                                                </div>

                                            </div>


                                            <div class="form-group row">
                                                <label class="col-lg-2 col-form-label">
                                                    Twitter
                                                </label>
                                                <div class="col-lg-10">
                                                    <input type="text" v-model="attributes.twitter_link" value=""
                                                           class="form-control "
                                                           placeholder="Twitter link">
                                                </div>
                                            </div>

                                        </fieldset>

                                    </div>

                                    <div class="tab-pane " id="bottom-divided-tab2">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <fieldset class="col-md-11">

                                                    <span class="badge badge-primary font-weight-bold ">BILLING ADDRESS</span>

                                                    <div class="form-group row mt-2">

                                                        <div class="col-lg-12" title="Attention" >
                                                            <input type="text" v-model="attributes.billing_address_attention"
                                                                   value="" class="form-control "
                                                                   placeholder="Attention">
                                                        </div>

                                                    </div>

                                                    <div class="form-group row" title="Address" >
                                                        <div class="col-lg-12">
                                                            <input type="text" v-model="attributes.billing_address_street1"
                                                                   value=""
                                                                   class="form-control  mb-2"
                                                                   placeholder="Street 1">
                                                            <div class="clearfix"></div>
                                                            <input type="text" v-model="attributes.billing_address_street2"
                                                                   value="" class="form-control "
                                                                   placeholder="Street 2">
                                                        </div>

                                                    </div>

                                                    <div class="form-group row" title="City">
                                                        <div class="col-lg-12">
                                                            <input type="text" v-model="attributes.billing_address_city" value=""
                                                                   class="form-control "
                                                                   placeholder="City">
                                                        </div>
                                                    </div>


                                                    <div class="form-group row" title="State">
                                                        <div class="col-lg-12">
                                                            <input type="text" v-model="attributes.billing_address_state" value=""
                                                                   class="form-control "
                                                                   placeholder="State">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row" title="Zip code">
                                                        <div class="col-lg-12">
                                                            <input type="text" v-model="attributes.billing_address_zip_code"
                                                                   value="" class="form-control "
                                                                   placeholder="Name">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row" title="Country">
                                                        <div class="col-lg-12">
                                                            <model-list-select :list="globalsCountries"
                                                                               v-model="attributes.billing_address_country"
                                                                               option-value="value"
                                                                               option-text="text"
                                                                               placeholder="Select country">
                                                            </model-list-select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row hidden">
                                                        <div class="col-lg-12" title="Fax" >
                                                            <input type="text" v-model="attributes.billing_address_fax" value=""
                                                                   class="form-control "
                                                                   placeholder="Name">
                                                        </div>
                                                    </div>

                                                </fieldset>
                                            </div>

                                            <div class="col-md-6">

                                                <fieldset class="col-md-12 pr-0">

                                                    <span class="badge badge-primary font-weight-bold ">SHIPPING ADDRESS</span>

                                                    <div class="form-group row mt-2">
                                                        <div class="col-lg-12" title="Attention">
                                                            <input type="text" v-model="attributes.shipping_address_attention"
                                                                   value="" class="form-control "
                                                                   placeholder="Attention">
                                                        </div>

                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-lg-12" title="Address">
                                                            <input type="text" v-model="attributes.shipping_address_street1"
                                                                   value=""
                                                                   class="form-control  mb-2"
                                                                   placeholder="Street 1">
                                                            <div class="clearfix"></div>
                                                            <input type="text" v-model="attributes.shipping_address_street2"
                                                                   value="" class="form-control "
                                                                   placeholder="Street 2">
                                                        </div>

                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-lg-12" title="City">
                                                            <input type="text" v-model="attributes.shipping_address_city" value=""
                                                                   class="form-control "
                                                                   placeholder="City">
                                                        </div>

                                                    </div>


                                                    <div class="form-group row">
                                                        <div class="col-lg-12" title="State" >
                                                            <input type="text" v-model="attributes.shipping_address_state"
                                                                   value="" class="form-control "
                                                                   placeholder="State">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-lg-12" title="Zip code">
                                                            <input type="text" v-model="attributes.shipping_address_zip_code"
                                                                   value="" class="form-control "
                                                                   placeholder="Zip code">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-lg-12" title="Country">
                                                            <model-list-select :list="globalsCountries"
                                                                               v-model="attributes.shipping_address_country"
                                                                               option-value="value"
                                                                               option-text="text"
                                                                               placeholder="Select country">
                                                            </model-list-select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row hidden">
                                                        <div class="col-lg-12" title="Fax" >
                                                            <input type="text" v-model="attributes.shipping_address_fax" value=""
                                                                   class="form-control "
                                                                   placeholder="Fax">
                                                        </div>
                                                    </div>

                                                </fieldset>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="tab-pane " id="bottom-divided-tab3">

                                        <table id="primary-contact" class="table table-bordered no-border-left no-border-right no-border-bottom">
                                            <thead class="thead-default">
                                            <tr>
                                                <th class="pl-10">Salutation</th>
                                                <th class="pl-15">First Name</th>
                                                <th class="pl-15">Last Name</th>
                                                <th class="pl-15" style="width:30%">Email Address</th>
                                                <th class="pl-15">Work Phone</th>
                                                <th class="pl-15">Mobile</th>
                                            </tr>
                                            </thead>
                                            <tbody id="">

                                                <tr class="">
                                                    <td class="p-0">
                                                        <model-select
                                                            :options="globalsSalutations"
                                                            v-model="attributes.contact_salutation"
                                                            class="border-0"
                                                            placeholder="Salutation ...">
                                                        </model-select>
                                                    </td>
                                                    <td class="p-0">
                                                        <input type="text"
                                                               v-model="attributes.contact_first_name"
                                                               class="item_row_quantity form-control border-0"
                                                               value="" placeholder="First Name">
                                                    </td>
                                                    <td class="p-0">
                                                        <input type="text"
                                                               v-model="attributes.contact_last_name"
                                                               class="item_row_rate form-control m-input border-0"
                                                               value="" placeholder="Last Name"></td>
                                                    <td class="p-0">
                                                        <input type="text"
                                                               v-model="attributes.contact_email"
                                                               class="item_row_rate form-control m-input border-0"
                                                               value="" placeholder="Email Address">
                                                    </td>
                                                    <td class="p-0">
                                                        <input type="text"
                                                               v-model="attributes.contact_work_phone"
                                                               class="item_row_rate form-control m-input border-0"
                                                               value="" placeholder="Work Phone">
                                                    </td>
                                                    <td class="p-0">
                                                        <input type="text"
                                                               v-model="attributes.contact_mobile"
                                                               class="item_row_rate form-control m-input border-0"
                                                               value="" placeholder="Mobile"></td>
                                                </tr>

                                                <tr class="contact_person_row" v-for="(contact_person, index)  in attributes.contact_persons">
                                                    <td class="p-0">
                                                        <model-select
                                                            :options="globalsSalutations"
                                                            v-model="contact_person.salutation"
                                                            class="border-0"
                                                            placeholder="Salutation ...">
                                                        </model-select>
                                                    </td>
                                                    <td class="p-0"><input type="text"
                                                                           v-model="contact_person.first_name"
                                                                           class="form-control border-0"
                                                                           value="" placeholder="First Name">
                                                    </td>
                                                    <td class="p-0"><input type="text"
                                                                           v-model="contact_person.last_name"
                                                                           class="form-control m-input border-0"
                                                                           value="" placeholder="Last Name"></td>
                                                    <td class="p-0"><input type="text"
                                                                           v-model="contact_person.email"
                                                                           class="form-control m-input border-0"
                                                                           value="" placeholder="Email Address">
                                                    </td>
                                                    <td class="p-0"><input type="text"
                                                                           v-model="contact_person.work_phone"
                                                                           class="form-control m-input border-0"
                                                                           value="" placeholder="Work Phone">
                                                    </td>
                                                    <td class="p-0"><input type="text"
                                                                           v-model="contact_person.mobile"
                                                                           class="form-control m-input border-0"
                                                                           value="" placeholder="Mobile"></td>
                                                </tr>

                                            </tbody>
                                        </table>

                                        <button type="button" @click="addContactPerson" class="btn btn-link btn-xs font-weight-bold">
                                            <i class="icon-plus22 position-left"></i> Add Contact Person
                                        </button>


                                    </div>

                                    <div class="tab-pane " id="bottom-divided-tab4">

                                        <fieldset class="">

                                            <div class="form-group row">

                                                <label class="col-lg-12 col-form-label">
                                                    <span class="text-semibold">Remarks</span> (
                                                    <small>For internal use</small>
                                                    )
                                                </label>
                                                <div class="col-lg-12">
                                                    <textarea v-model="attributes.remarks" class="form-control "
                                                              placeholder="Remarks"></textarea>
                                                </div>

                                            </div>

                                        </fieldset>

                                    </div>

                                    <div class="tab-pane " id="bottom-divided-tab5">

                                        <div class="form-group">
                                            <label>Taxes collected by tax body / contact being created;</label>
                                            <multi-list-select
                                                :list="txnTaxes"
                                                option-value="id"
                                                option-text="display_name"
                                                :selected-items="selectedTaxes"
                                                placeholder="Select taxes"
                                                @select="setTaxes">
                                            </multi-list-select>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label"> </label>
                            <div class="col-lg-10">
                                <button type="submit" class="btn btn-danger font-weight-bold">
                                    <i class="icon-user-plus"></i> {{pageTitle}}
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
            <!-- /form horizontal -->


        </div>
        <!-- /content area -->

    </div>
    <!-- /main content -->

</template>

<script>

    import LoadingComponent from '../../../../../../../resources/js/components/LoadingComponent.vue'

    export default {
        name: 'ContactsCreate',
        components: {
            LoadingComponent,
        },
        data() {
            return {
                pageTitle: 'Contact',
                urlPost: '/contacts',
                types: [],
                selectedTaxes: [],
                attributes: [],
            }
        },
        mounted() {
            this.$root.appMenu('accounting')

            this.fetchAttributes()
            this.appFetchGlobalsCurrencies()
            this.appFetchGlobalsCountries()
            this.txnFetchTaxes()
            this.appFetchGlobalsPaymentTerms()
            this.appFetchGlobalsSalutations()

        },
        watch: {
            $route: function () {
                this.fetchAttributes()
            }
        },
        methods: {
            async fetchAttributes() {
                //console.log('fetchAttributes')

                try {

                    this.$root.loadingTxn = true

                    return await axios.get(this.$route.fullPath)
                        .then(response => {

                            this.pageTitle = response.data.pageTitle
                            this.urlPost = response.data.urlPost
                            this.types = response.data.types
                            this.attributes = response.data.attributes
                            this.currencies = response.data.currencies
                            this.globalsCurrenciesSelected = response.data.selectedCurrencies
                            this.selectedTaxes = response.data.selectedTaxes

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
            setCurrencies(options, option, row) {
                this.globalsCurrenciesSelected = options
                //console.log(options)
                this.attributes.currencies = options.map(function (currency) {
                    return currency.value
                })
            },
            setTaxes(options, option, row) {
                this.selectedTaxes = options
                //console.log(options)
                this.attributes.taxes = options.map(function (tax) {
                    return tax.id
                })
            },
            addContactPerson() {
                this.attributes.contact_persons.push({
                    salutation: null,
                    first_name: null,
                    last_name: null,
                    email: null,
                    work_phone: null,
                    mobile: null
                });
            },
            formSubmit(e) {

                e.preventDefault();

                let currentObj = this;

                PNotify.removeAll();

                let PNotifySettings = {
                    title: false, //'Processing',
                    text: 'Please wait as we do our thing',
                    addclass: 'bg-warning-400 border-warning-400',
                    hide: false,
                    buttons: {
                        closer: false,
                        sticker: false
                    }
                };

                let notice = new PNotify(PNotifySettings);

                console.log(this.attributes);

                axios.post(currentObj.urlPost, this.attributes)
                    .then(function (response) {

                        //PNotify.removeAll();

                        PNotifySettings.text = response.data.messages.join("\n");

                        if(response.data.status === true) {
                            PNotifySettings.title = 'Success';
                            PNotifySettings.type = 'success';
                            PNotifySettings.addclass = 'bg-success-400 border-success-400';
                        } else {
                            PNotifySettings.title = '! Error';
                            PNotifySettings.type = 'error';
                            PNotifySettings.addclass = 'bg-warning-400 border-warning-400';
                        }

                        //let notice = new PNotify(PNotifySettings);
                        notice.update(PNotifySettings);

                        notice.get().click(function() {
                            notice.remove();
                        });

                        //currentObj.response = response.data;
                    })
                    .catch(function (error) {
                        currentObj.response = error;
                    });
            },
        }
    }
</script>
