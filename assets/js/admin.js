$(document).ready(function () {

    $('#usersTable').DataTable({

        pageLength: 10,

        responsive: true,

        ordering: true,

        searching: true,

        info: true,

        lengthMenu: [
            [10,25,50,100,-1],
            [10,25,50,100,"All"]
        ],

        language: {

            search: "Search:",

            lengthMenu: "Show _MENU_ users",

            zeroRecords: "No users found.",

            info: "Showing _START_ to _END_ of _TOTAL_ users",

            infoEmpty: "No users available",

            paginate: {

                first: "First",

                last: "Last",

                next: "Next",

                previous: "Previous"

            }

        }

    });

});

/*
|--------------------------------------------------------------------------
| Location Management DataTable
|--------------------------------------------------------------------------
*/

$(document).ready(function () {
    if ($('#locationsTable').length) {
        $('#locationsTable').DataTable({
            pageLength: 10,
            responsive: true,
            ordering: true,
            searching: true,
            info: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ locations",
                zeroRecords: "No locations found.",
                info: "Showing _START_ to _END_ of _TOTAL_ locations",
                infoEmpty: "No locations available",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});

$(document).ready(function(){
    $('#accountingTable').DataTable({
        responsive:true,
        autoWidth:false,
        pageLength:25,
        order:[[1,'asc']],
        columnDefs:[
            {
                orderable:false,
                targets:[5]
            }
        ],
        language:{
            emptyTable:'No accounting users found.'
        }
    });
});

document.getElementById('checkAll').addEventListener('change', function(){
    document.querySelectorAll('.store-checkbox').forEach(function(box){
        box.checked = document.getElementById('checkAll').checked;
    });
});