$(function () {
    var datepickerenddate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);//
    datepickerenddate = datepickerenddate.getDate()+'-'+(datepickerenddate.getMonth()+1)+'-'+datepickerenddate.getFullYear();
    //selectTriggerFlag = 0;

    //new account registration link for select2
    accountRegistrationLink    = "No account found. <a href='/account/register'>Register new account</a>";
    
    //Date picker
    $('.datepicker').datepicker({
        todayHighlight: true,
        //startDate: today,
        endDate: datepickerenddate,
        format: 'dd-mm-yyyy',
        autoclose: true,
    });

    //setting current date as selected
    $('.datepicker').datepicker('setDate', new Date());    
    //$(".datepicker").datepicker("update", new Date());

    //Timepicker
    $(".timepicker").timepicker({
        minuteStep : 5,
        showInputs : false,
        showMeridian : false
    });

    // update timepicker value
    setInterval(function() { updateTimepicker() }, 300000);

    //Initialize Select2 Element for account select box
    $("#cash_voucher_account_id").select2({
        minimumResultsForSearch: 5,
        language: {
             noResults: function() {
                return accountRegistrationLink;
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }
    });

    //select name for the selected account
    $('body').on("change", "#cash_voucher_account_id", function () {
        var accountId = $('#cash_voucher_account_id').val();

        $('#cash_voucher_account_name').val('');
        if(accountId) {
            $.ajax({
                url: "/account/detail/by/account/" + accountId,
                method: "get",
                success: function(result) {
                    if(result && result.flag) {
                        var name  = result.name;
                        
                        $('#cash_voucher_account_name').val(name);
                    } else {
                        $('#cash_voucher_account_name').val('');
                    }
                },
                error: function () {
                    $('#cash_voucher_account_name').val('');
                }
            });
        } else {
            $('#cash_voucher_account_name').val('');
        }
    });
});
// timepicker value updation
function updateTimepicker() {
    currentDate     = new Date();
    currentHour     = currentDate.getHours();
    currentMinute   = currentDate.getMinutes();

    if(currentHour < 10) {
        currentHour = '0' + currentHour;
    }
    if(currentMinute < 10) {
        currentMinute = '0' + currentMinute;
    }

    currentTime = currentHour + ':' + currentMinute;
    $(".timepicker").val(currentTime);  
}
