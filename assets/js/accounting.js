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