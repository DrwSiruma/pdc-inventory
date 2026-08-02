$(document).ready(function(){
    $('#deliveryTable').DataTable({
        responsive:true,
        ordering:true,
        searching:true,
        pageLength:10,
        order:[[0,'asc']],
        language:{
            emptyTable:'No delivery records found.'
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    function computeShort(row) {
        const expected = parseFloat(
            row.querySelector('.expectedQty').value
        ) || 0;
        const actual = parseFloat(
            row.querySelector('.actualQty').value
        ) || 0;
        let shortQty = expected - actual;
        if (shortQty < 0) {
            shortQty = 0;
        }
        row.querySelector('.shortQty').value =
            shortQty.toFixed(2);
    }
    document.querySelectorAll('#deliveryItemsTable tbody tr')
        .forEach(function (row) {
            computeShort(row);
            row.querySelector('.actualQty')
                .addEventListener('input', function () {
                    computeShort(row);
                });
        });
});

$(document).ready(function () {
    $('#deliveryItemsTable').DataTable({
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        responsive: true
    });
});

$('form').on('submit', function () {
    let valid = true;
    $('.actualQty').each(function () {
        let actual = parseFloat($(this).val());
        if (isNaN(actual) || actual < 0) {
            valid = false;
            $(this).focus();
            alert('Please enter a valid Actual Quantity.');
            return false;
        }
    });
    return valid;
});

$(document).ready(function () {
    $('#historyTable').DataTable({
        responsive: true,
        ordering: true,
        searching: true,
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            emptyTable: 'No delivery history found.'
        }
    });
});

$(document).ready(function () {
    /*
    |--------------------------------------------------------------------------
    | Auto Refresh Status
    |--------------------------------------------------------------------------
    | Refresh every 5 minutes so users can immediately see if today's
    | inventory has been submitted or verified.
    |--------------------------------------------------------------------------
    */
    setTimeout(function () {
        location.reload();
    }, 300000);
});

/*
|--------------------------------------------------------------------------
| Store Product Inventory
|--------------------------------------------------------------------------
*/

$(document).ready(function () {
    if (!$('#inventoryTable').length) {
        return;
    }
    $('#inventoryTable tbody tr').each(function () {
        const row = $(this);
        const beginning =
            parseFloat(
                row.find('td:eq(2) input').val()
            ) || 0;
        const received =
            parseFloat(
                row.find('td:eq(3) input').val()
            ) || 0;
        const pdr =
            row.find('.pdrQty');
        const throwAway =
            row.find('.throwQty');
        const ending =
            row.find('.endingQty');
        const expected =
            row.find('.expectedQty');
        const variance =
            row.find('.varianceQty');
        function computeInventory() {
            const pdrQty =
                parseFloat(pdr.val()) || 0;
            const throwQty =
                parseFloat(throwAway.val()) || 0;
            const endingQty =
                parseFloat(ending.val()) || 0;
            const expectedQty =
                beginning +
                received +
                pdrQty -
                throwQty;
            const varianceQty =
                expectedQty -
                endingQty;
            expected.val(
                expectedQty.toFixed(2)
            );
            variance.val(
                varianceQty.toFixed(2)
            );
        }
        pdr.on(
            'input',
            computeInventory
        );
        throwAway.on(
            'input',
            computeInventory
        );
        ending.on(
            'input',
            computeInventory
        );
        computeInventory();
    });
    if ($.fn.DataTable) {
        $('#inventoryTable').DataTable({
            paging: false,
            ordering: true,
            searching: true,
            info: false,
            autoWidth: false,
            responsive: true,
            columnDefs: [
                {
                    orderable: false,
                    targets: [2, 3, 4, 5, 6, 7, 8]
                }
            ]
        });
    }
});

/*
|--------------------------------------------------------------------------
| Product Inventory History
|--------------------------------------------------------------------------
*/

$(document).ready(function () {
    if ($('#inventoryHistoryTable').length) {
        $('#inventoryHistoryTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            columnDefs: [
                {
                    orderable: false,
                    targets: [4]
                }
            ]
        });
    }
});

/*
|--------------------------------------------------------------------------
| Product Throw Away
|--------------------------------------------------------------------------
*/

$(document).ready(function () {
    if ($('#throwAwayTable').length) {
        $('#throwAwayTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                {
                    orderable: false,
                    targets: [6]
                }
            ]
        });
    }
});

/*
|--------------------------------------------------------------------------
| Throw Away History
|--------------------------------------------------------------------------
*/

$(document).ready(function () {
    if ($('#throwAwayHistoryTable').length) {
        $('#throwAwayHistoryTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }
});