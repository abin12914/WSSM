$(function () {
    var datepickerenddate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);//
    datepickerenddate = datepickerenddate.getDate()+'-'+(datepickerenddate.getMonth()+1)+'-'+datepickerenddate.getFullYear();
    //new account registration link for select2
    accountRegistrationLink = "No results found. <a href='/account/register'>Register new account</a>";
    
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

    //Timepicker
    $(".timepicker").timepicker({
        minuteStep : 1,
        showInputs : false,
        showMeridian : false
    });

    // update timepicker value
    setInterval(function() { updateTimepicker() }, 60000);

    //Initialize Select2 Element for vehicler number select box
    $(".supplier").select2({
        placeholder: "Select supplier",
    }).on("select2:close", function () {
        var fieldValue = $(this).val();
        if(fieldValue) {
            setTimeout(function() {
                $("#description").focus();
            }, 1);
        }
    });

    //Initialize Select2 Element for product select box
    $(".product").select2({
        placeholder: "Select a product",
    }).on("select2:close", function () {
        var fieldValue = $(this).val();
        if(fieldValue) {
            setTimeout(function() {
                $("#quantity_main").focus();
            }, 1);
        }
    });

    $("#supplier_account_id").focus();

    $('body').on("change", "#product_id_main", function () {
        var fieldValue  = $(this).val();
        
        if(fieldValue) {
            $("#quantity_main").focus();
        }
    })

    $('body').on("keydown", "#quantity_main", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        var fieldValue  = $(this).val();

        if(charCode == 13) {
            evt.preventDefault();
            if(fieldValue.length == 0) {
                alert("Rate field should not be empty.");
                return false;
            }
            $("#rate_main").focus();
        }
    });

    $('body').on("keyup", "#quantity_main", function (evt) {
        calculateMainSubTotal();
    });

    $('body').on("keydown", "#rate_main", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        var fieldValue  = $(this).val();

        if(charCode == 13) {
            evt.preventDefault();
            if(fieldValue.length == 0) {
                alert("Rate field should not be empty.");
                return false;
            }
            $("#button_main").focus();
        }
    });

    $('body').on("keyup", "#rate_main", function (evt) {
        calculateMainSubTotal();
    });

    $('body').on("keyup", "#discount", function (evt) {
        calculateTotalBill();
    });

    $('body').on("click", "#button_main", function (evt) {
        calculateMainSubTotal();
        var productId   = $('#product_id_main').val();
        var productName = $('#product_id_main option:selected').text();
        var quantity    = $('#quantity_main').val();
        var rate        = $('#rate_main').val();
        var subTotal    = $('#sub_total_main').val();
        var subTotal    = $('#sub_total_main').val();

        if(productId && quantity && rate) {
            $.ajax({
                url: "/purchase/detail/add",
                method: "post",
                data: {
                    _token: token,
                    product_id: productId,
                    quantity: quantity,
                    rate: rate,
                    total: subTotal
                },
                success: function(result) {
                    if(result && result.flag) {
                        $("#bill_body").append(result.data);
                        $('#bill_amount').val(result.totalBill);
                        calculateTotalBill();
                    } else {
                        console.log("ajax request failed #1");
                    }
                },
                error: function () {
                    console.log("ajax request failed #2");
                }
            });
        } else {
            alert("Fill all fields!")
        }
        
        $('#product_id_main').val('');
        $('#quantity_main').val('');
        $('#rate_main').val('');
        $('#sub_total_main').val('');
        $('#product_id_main').trigger('change');
        $('#product_id_main').focus();
    });

    $('body').on("click", ".remove_button", function (evt) {
        calculateTotalBill();
        id = $(this).data('detail-id');
        currentRow =  $(this).parent().parent();

        if(id) {
            $.ajax({
                url: "/purchase/detail/delete",
                method: "post",
                data: {
                    _token: token,
                    id: id
                },
                success: function(result) {
                    if(result && result.flag) {
                        currentRow.remove();

                        var lessValue = result.amount;
                        var deductedTotal = (($('#bill_amount').val() * 1) - (lessValue * 1));
                        $('#bill_amount').val(deductedTotal);
                        calculateTotalBill();
                    } else {
                        console.log("ajax request failed #3");
                    }
                },
                error: function () {
                    console.log("ajax request failed #4");
                }
            });
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

function calculateMainSubTotal() {
    quantity    = $('#quantity_main').val();
    rate        = $('#rate_main').val();
    subTotal    = 0;

    if(quantity != 0 && quantity.charAt(quantity.length-1) != '.') {
        //for removing the preceding zero
        quantity = quantity * 1;
    }
    if(rate != 0 && rate.charAt(rate.length-1) != '.') {
        //for removing the preceding zero
        rate = rate * 1;
    }
    if(quantity && rate) {
        subTotal    = quantity * rate;
    }
    $('#quantity_main').val(quantity);
    $('#rate_main').val(rate);
    $('#sub_total_main').val(subTotal);
}

function calculateTotalBill() {
    totalBill       = $('#bill_amount').val()
    tax             = $('#tax_amount').val();
    discount        = $('#discount').val();
    deductedTotal   = 0;

    if(discount != 0 && discount.charAt(discount.length-1) != '.') {
        //for removing the preceding zero
        discount = discount * 1;
    }
    if(totalBill && totalBill >= 1 && totalBill > discount) {
        deductedTotal  = (((totalBill * 1) + (tax * 1)) - discount);
        $('#discount').val(discount);
    } else {
        $('#discount').val('0');        
    }

    $('#deducted_total').val(deductedTotal);
}