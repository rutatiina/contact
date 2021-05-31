/**
 * Created by t on 9/9/2017.
 */
rg_contacts = function () {

    var datatable_sidebar = function () {

        var dtable = $('.rg_datatable_sidebar').DataTable({
            serverSide: true,
            pagingType: "simple",
            language: {
                paginate: {'next': 'Next &rarr;', 'previous': '&larr; Prev'}
            },
            columnDefs: [
                {
                    'targets': [0],
                    "orderable": true
                }
            ],
            aaSorting: [[0, 'asc']],
            //ordering: false,
            info: false,
            bLengthChange: false,
            bFilter: false,
            iDisplayLength: 20,
            aoColumns: [
                {"mDataProp": "display_name", "sClass": "pl-5 pr-5"}
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //var display_name = (rg_empty(aData['display_name'])? '': ' - <span class="text-muted pl-5">'+aData['display_name']+'</span>');
                $('td:eq(0)', nRow).html('<a href="' + APP_URL + '/contacts/' + aData['id'] + '">' + aData['display_name'] + '</a>');
            }
        });

        return dtable;

    };

    var datatable = function () {

        var dtable = $('.rg-datatable').DataTable({
            //destroy: true,
            //processing: false,
            //serverSide: true,
            buttons: {
                dom: {
                    button: {
                        className: 'btn btn-default'
                    }
                },
                buttons: [
                    {
                        extend: 'copyHtml5',
                        className: 'btn btn-default btn-icon',
                        text: '<i class="icon-copy3"></i>'
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-default btn-icon',
                        text: '<i class="icon-file-excel"></i>'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-default btn-icon',
                        text: '<i class="icon-file-pdf"></i>'
                    }
                ]
            },
            ajax: APP_URL + '/contacts/datatables/',
            pagingType: "simple",
            language: {
                paginate: {'next': 'Next &rarr;', 'previous': '&larr; Prev'}
            },
            iDisplayLength: 20,
            aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
            columnDefs: [
                {
                    'targets': [2],
                    "orderable": true
                },
                {
                    'targets': 0,
                    "orderable": false,
                    'checkboxes': {
                        'selectRow': true,
                        'selectCallback': function (nodes, selected, indeterminate) {
                            //nodes: [Array] List of cell nodes td containing checkboxes.
                            //selected: [Boolean]  Flag indicating whether checkbox has been checked.
                            //indeterminate: [Boolean] Flag indicating whether “Select all” checkbox has indeterminate state.
                            //console.log(nodes);
                            //console.log(selected);

                            var rows_selected = nodes.column(0).checkboxes.selected().length;
                            if (rows_selected > 0) {
                                $('.rg_datatable_onselect_btns').show();
                                $('.page-header').hide();
                            } else {
                                $('.rg_datatable_onselect_btns').hide();
                                $('.page-header').show();
                            }
                        }
                    },
                },
                {
                    'targets': [0, 1],
                    "orderable": false
                }
            ],
            aaSorting: [[2, 'asc']],
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            //order: [[0, false]],
            //ordering: false,
            //info: false,
            //bLengthChange: false,
            //bFilter: false,
            aoColumns: [
                {"mDataProp": 'id'},
                {"mDataProp": null, "sClass": "text-center pl-5"},
                {"mDataProp": "name", "sClass": "pl-5"},
                {"mDataProp": "display_name", "sClass": ""},
                {"mDataProp": "contact_mobile", "sClass": ""},
                {"mDataProp": "types", "sClass": ""},
                {"mDataProp": 'receviables', "sClass": "text-right"},
                {"mDataProp": 'payables', "sClass": "text-right"}
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {

                if (aData.status === 'inactive') {
                    $(nRow).addClass('danger');
                    $('td:eq(1)', nRow).html('<i class="icon-alert text-danger" title="Deactivated"></i>');
                } else {
                    $('td:eq(1)', nRow).html('<a href="' + APP_URL + '/contacts/' + aData['id'] + '/edit"><i class="icon-pencil7"></i></a>');
                }

                //$('td:eq(2)', nRow).html('<a href="' + APP_URL + '/contacts/' + aData['id'] + '">' + ((aData.salutation === null) ? '' : aData.salutation) + ' ' + aData.name + '</a>');
                $('td:eq(2)', nRow).html('<a href="' + APP_URL + '/contacts/' + aData['id'] + '" title="' + ((aData.salutation === null) ? '' : aData.salutation) + ' ' + aData.name + '">' + aData.name + '</a>');

                var x = '';
                $.each(aData.types, function (index, category) {
                    //x += '<span class="badge badge-default text-uppercase mr-10">' + category + '</span>';
                    x += '<code class="mr-10">' + category + '</code>';
                });

                $('td:eq(5)', nRow).html(x);

                $('td:eq(6)', nRow).html(rg_number_format(aData.receviables)+' <small>'+aData.currency+'</small>');
                $('td:eq(7)', nRow).html(rg_number_format(aData.payables)+' <small>'+aData.currency+'</small>');

            }
        });

        // Datatable Search
        $('#navbar_top_search').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                dtable.search($(this).val()).draw();
            }
        });

        $('.rg_datatable_selected_delete, .rg_datatable_row_delete').on('click', function (ev) {

            ev.stopPropagation();
            ev.preventDefault();

            var ids = [];
            var url = (rg_empty($(this).data('url')) ? $(this).attr('href') : $(this).data('url'));

            //console.log(url);

            var rows_selected = dtable.column(0).checkboxes.selected();

            // Iterate over all selected checkboxes
            $.each(rows_selected, function (index, rowId) {
                ids[index] = rowId;
            });

            var ajaxData = {
                ids: ids,
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'post'
            };

            //console.log(ids);

            swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover the contact(s)!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#EF5350",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel pls!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    showLoaderOnConfirm: true
                },
                function (isConfirm) {
                    if (isConfirm) {

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: ajaxData,
                            dataType: "json",
                            success: function (response, status, xhr, $form) {

                                //Update the cross dite tocken
                                //form.find('[name=ci_csrf_token]').val(Cookies.get('ci_csrf_token'));

                                if (response.status === true) {
                                    swal({
                                        title: "Deleted!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "success",
                                        timer: 2000
                                    });

                                    //Redraw the the table
                                    dtable.ajax.reload();
                                    $('.rg_datatable_onselect_btns').slideUp(100);

                                } else {
                                    swal({
                                        title: "Failed!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "danger",
                                        timer: 2000
                                    });
                                }

                            }
                        });

                    }
                });
        });

        $('.rg_datatable_selected_deactivate, .rg_datatable_row_deactivate').on('click', function (ev) {

            ev.stopPropagation();
            ev.preventDefault();

            var ids = [];
            var url = (rg_empty($(this).data('url')) ? $(this).attr('href') : $(this).data('url'));

            //console.log(url);

            var rows_selected = dtable.column(0).checkboxes.selected();

            // Iterate over all selected checkboxes
            $.each(rows_selected, function (index, rowId) {
                ids[index] = rowId;
            });

            var ajaxData = {
                ids: ids,
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'post'
            };

            //console.log(ajaxData);

            swal({
                    title: "Are you sure?",
                    text: "You want to deactivate contact(s)!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#EF5350",
                    confirmButtonText: "Yes, deactivate it!",
                    cancelButtonText: "No, cancel pls!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    showLoaderOnConfirm: true
                },
                function (isConfirm) {
                    if (isConfirm) {

                        //console.log('calling deactivate ajax: '+url);

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: ajaxData,
                            dataType: "json",
                            success: function (response, status, xhr, $form) {

                                //Update the cross dite tocken
                                //form.find('[name=ci_csrf_token]').val(Cookies.get('ci_csrf_token'));

                                if (response.status === true) {
                                    swal({
                                        title: "Deactivated!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "success",
                                        timer: 2000
                                    });

                                    dtable.ajax.reload();
                                    $('.rg_datatable_onselect_btns').slideUp(100);

                                } else {
                                    swal({
                                        title: "Failed!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "error",
                                        timer: 2000
                                    });
                                }

                            }
                        });

                    }
                });
        });

        $('.rg_datatable_selected_activate, .rg_datatable_row_activate').on('click', function (ev) {

            ev.stopPropagation();
            ev.preventDefault();

            var ids = [];
            var url = (rg_empty($(this).data('url')) ? $(this).attr('href') : $(this).data('url'));

            //console.log(url);

            var rows_selected = dtable.column(0).checkboxes.selected();

            // Iterate over all selected checkboxes
            $.each(rows_selected, function (index, rowId) {
                ids[index] = rowId;
            });

            var ajaxData = {
                ids: ids,
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'post'
            };

            //console.log(ids);

            swal({
                    title: "Are you sure?",
                    text: "You want to activate contact(s)!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#EF5350",
                    confirmButtonText: "Yes, activate it!",
                    cancelButtonText: "No, cancel pls!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    showLoaderOnConfirm: true
                },
                function (isConfirm) {
                    if (isConfirm) {

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: ajaxData,
                            dataType: "json",
                            success: function (response, status, xhr, $form) {

                                //Update the cross dite tocken
                                //form.find('[name=ci_csrf_token]').val(Cookies.get('ci_csrf_token'));

                                if (response.status === true) {
                                    swal({
                                        title: "Activated!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "success",
                                        timer: 2000
                                    });

                                    dtable.ajax.reload();
                                    $('.rg_datatable_onselect_btns').slideUp(100);

                                } else {
                                    swal({
                                        title: "Failed!",
                                        text: response.message,
                                        confirmButtonColor: "#66BB6A",
                                        type: "error",
                                        timer: 2000
                                    });
                                }

                            }
                        });

                    }
                });
        });

        return dtable;

    };

    var datatable_sales = function () {

        var datatableSettings = {
            //serverSide: true,
            processing: true,
            data: [],
            pagingType: "simple",
            language: {
                paginate: {'next': 'Next &rarr;', 'previous': '&larr; Prev'}
            },
            columnDefs: [
                {
                    'targets': 'no-sort',
                    "orderable": false
                }
            ],
            aaSorting: [[0, 'desc']],
            //ordering: false,
            info: false,
            bLengthChange: false,
            bFilter: false,
            iDisplayLength: 15,
            aoColumns: [
                {"mDataProp": "date", "sClass": ""},
                {"mDataProp": "number", "sClass": ""},
                {"mDataProp": "reference", "sClass": ""},
                {"mDataProp": "status", "sClass": ""},
                {"mDataProp": "total", "sClass": "text-right"},
                //{ "mDataProp": null, "sClass": "" },
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //var display_name = (rg_empty(aData['display_name'])? '': ' - <span class="text-muted pl-5">'+aData['display_name']+'</span>');
                //$('td:eq(0)', nRow).html('<a href="/contacts/summary/'+aData['id']+'">'+aData['name']+'</a>'+display_name);
                //$('td:eq(5)', nRow).html('');
                $('td:eq(4)', nRow).html(rg_number_format(aData['total']) + ' ' + aData['base_currency']);
            }
        };

        $('.datatable-load-data').on('click', function () {
            var t = $(this);
            if (t.hasClass('collapsed')) {
                console.log("button click:" + t.data('datatable'));
                if (!$.fn.DataTable.isDataTable(t.data('datatable'))) {
                    dtable = $(t.data('datatable')).DataTable(datatableSettings);
                }
                dtable.ajax.url(t.data('ajax')).load();
            }
        });

        //return dtable;

    };

    return {
        // public functions
        init: function () {

            try {
                datatable_sidebar();
            } catch (e) {
                console.log(e);
            }

            try {
                datatable();
            } catch (e) {
                console.log(e);
            }

            try {
                datatable_sales();
            } catch (e) {
                console.log(e);
            }
        }
    };
}();

jQuery(document).ready(function () {

    // Setting datatable defaults
    $.extend($.fn.dataTable.defaults, {
        autoWidth: false,
        columnDefs: [{
            orderable: false,
            width: '100px',
            targets: [5]
        }],
        //dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
        dom: '<"datatable-scroll-wrap"t><"datatable-footer"ip>',
        language: {
            search: '_INPUT_', //'<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to search ...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: {'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;'}
        },
        drawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
        },
        preDrawCallback: function () {
            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
        }
    });

    rg_contacts.init();

});
