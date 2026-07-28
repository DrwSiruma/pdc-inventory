$(document).ready(function () {

    $('#datatable').DataTable({

        order: [[0, 'desc']],

        pageLength: 25,

        responsive: true

    });

});

$(document).ready(function () {

    $('#productInventoryTable').DataTable({

        order: [[0, 'desc']],

        responsive: true,

        pageLength: 25,

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        autoWidth: false

    });

});

$(document).ready(function () {
    if ($('#productInventoryDetailsTable').length) {
        $('#productInventoryDetailsTable').DataTable({
            responsive: true,
            ordering: true,
            pageLength: 25,
            autoWidth: false,
            order: [[1, 'asc']],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ]
        });
    }
});

if ($('#throwAwayTable').length) {
    $('#throwAwayTable').DataTable({
        responsive: true,
        pageLength: 25,
        autoWidth: false,
        order: [[0,'desc']]
    });
}

if ($('#throwAwayDetailsTable').length) {
    $('#throwAwayDetailsTable').DataTable({
        responsive: true,
        pageLength: 25,
        autoWidth: false,
        ordering: true,
        order: [[1,'asc']],
        lengthMenu: [
            [10,25,50,100,-1],
            [10,25,50,100,"All"]
        ]
    });
}

$(document).ready(function(){
    $('#varianceTable').DataTable({
        responsive:true,
        autoWidth:false,
        pageLength:25,
        order:[[1,'asc']],
        lengthMenu:[
            [10,25,50,100,-1],
            [10,25,50,100,"All"]
        ]
    });
});

$(document).ready(function(){
    $('#variancesTable').DataTable({
        responsive:true,
        autoWidth:false,
        pageLength:25,
        order:[[0,'desc']],
        columnDefs:[
            {
                orderable:false,
                targets:[5]
            }
        ],
        language:{
            emptyTable:'No product variance records found.'
        }
    });
});

$(document).ready(function(){
    $('#historyTable2').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
            [0,'desc']
        ],
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [7]
            }
        ],
        language: {
            emptyTable: 'No generated or locked business days found.',
            zeroRecords: 'No matching business day found.',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ records',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'No records available',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    });
});$(document).ready(function(){
    $('#historyTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
            [0,'desc']
        ],
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [7]
            }
        ],
        language: {
            emptyTable: 'No generated or locked business days found.',
            zeroRecords: 'No matching business day found.',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ records',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'No records available',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    });
});

$(document).ready(function () {
    $('#dailyTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
            [0, 'asc']
        ],
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [6]
            }
        ],
        language: {
            emptyTable: "No stores available.",
            zeroRecords: "No matching records found.",
            search: "Search Store:",
            lengthMenu: "Show _MENU_ stores",
            info: "Showing _START_ to _END_ of _TOTAL_ stores",
            infoEmpty: "No records available",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});

$(document).ready(function(){
    $('#varianceHistoryTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [
            [0,'desc']
        ],
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [6]
            }
        ],
        language: {
            emptyTable: 'No variance history available.',
            zeroRecords: 'No matching variance history found.',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ records',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'No records available',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    });
});