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