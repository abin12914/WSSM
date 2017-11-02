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
    $(".customer").select2({
        placeholder: "Select customer",
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
    });

    $('body').on("change", "#customer_account_id", function () {
        var accountId  = $(this).val();

        if(accountId) {
            $.ajax({
                url: "/sale/detail/by/account/"+accountId,
                method: "get",
                success: function(result) {
                    if(result && result.flag) {
                        //clear existing products and details
                        $("#bill_body").html('');
                        $.each($.parseJSON(result.saleDetailTemp), function(index,detail) {
                            var html = '<tr id="product_row_'+ (detail.id) + '" data-tempdetail-id="'+ (detail.id) +'">'+
                                '<td class="serial_number">'+ (index+1) +'</td>'+
                                '<td id="td_product_id_'+ (index+1) +'">'+
                                    '<label class="form-control">'+ (detail.product.name) +'</label>'+
                                '<td>'+
                                    '<input name="quantity_'+ (index+1) +'" class="form-control quantity" type="text" style="width: 100%; height: 35px;" value="'+ (detail.quantity) +'" data-default-quantity="'+ (detail.quantity) +'">'+
                                '</td>'+
                                '<td>'+
                                    '<input id="measure_unit_'+ (index+1) +'" class="form-control" type="text" readonly style="width: 100%; height: 35px;" value="'+ (detail.product.measure_unit.name) +'">'+
                                '</td>'+
                                '<td>'+
                                    '<input name="rate_'+ (index+1) +'" class="form-control rate" type="text" style="width: 100%; height: 35px;" value="'+ (detail.rate) +'" data-default-rate="'+ (detail.rate) +'">'+
                                '</td>'+
                                '<td>'+
                                    '<input name="sub_total_'+ (index+1) +'" class="form-control sub_total" type="text" style="width: 100%; height: 35px;" value="'+ (detail.total) +'">'+
                                '</td>'+
                                '<td class="no-print">'+
                                    '<button data-detail-id="'+ (detail.id) +'" id="remove_button_'+ (index + 1) +'" type="button" class="form-control remove_button">'+
                                        '<i style="color: red;" class="fa fa-close"></i>'+
                                    '</button>'+
                                '</td>'+
                            '</tr>';
                            $("#bill_body").append(html);
                        });
                        oldBalance = (result.totalDebit - result.totalCredit) * 1;
                        oldBalanceAmount = oldBalance;
                        totalBill = result.totalBill;

                        if(oldBalance < 0) {
                            $('#old_balance_label').html('<b style="color: green;">Previous Adance</b>');
                            oldBalanceAmount = oldBalance * -1;
                        } else {
                            $('#old_balance_label').html('<b style="color: red;">Previous Balance</b>');
                        }

                        $('#bill_amount').val(totalBill);
                        $('#deducted_total').val(totalBill);
                        $('#old_balance_amount').html(oldBalanceAmount);
                        $('#old_balance').val(oldBalance);
                        calculateTotalBill();
                    } else {
                        console.log("ajax request failed #5");
                    }
                },
                error: function () {
                    console.log("ajax request failed #6");
                }
            });
            if(accountId == 1) {
                console.log('x');
                $('.ob_payment_block').hide();
            } else {
                console.log('y');
                $('.ob_payment_block').show();
            }
        }
    });

    //the following code must be put unnder the above code.
    var customerAccountId  = $('#customer_account_id').val();
    if(customerAccountId) {
        $('#customer_account_id').trigger('change');
    }

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

    $('body').on("keydown", "#description", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        var fieldValue  = $(this).val();

        if(charCode == 13) {
            evt.preventDefault();
            if(fieldValue.length == 0) {
                $("#product_id_main").focus();
            }
        }
    });

    $('body').on("keyup", "#rate_main", function (evt) {
        calculateMainSubTotal();
    });

    $('body').on("keyup", "#discount", function (evt) {
        calculateTotalBill();
    });

    $('body').on("keyup", "#payment", function (evt) {
        calculateTotalBill();
    });

    $('body').on("keydown", ".quantity", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        var quantity  = $(this).val();

        if(charCode == 13) {
        
        }
    });

    $('body').on("click", "#button_main", function (evt) {
        var accountId   = $('#customer_account_id').val();
        var productId   = $('#product_id_main').val();
        var productName = $('#product_id_main option:selected').text();
        var quantity    = $('#quantity_main').val();
        var rate        = $('#rate_main').val();
        var subTotal    = $('#sub_total_main').val();
        var subTotal    = $('#sub_total_main').val();

        if(!accountId) {
            alert("Select customer!");
            return false;
        }
        if(accountId && productId && quantity && rate) {
            $.ajax({
                url: "/sale/detail/add",
                method: "post",
                data: {
                    _token: token,
                    account_id: accountId,
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

        calculateMainSubTotal();
    });

    $('body').on("click", ".remove_button", function (evt) {
        calculateTotalBill();
        id = $(this).data('detail-id');
        currentRow =  $(this).parent().parent();

        if(id) {
            $.ajax({
                url: "/sale/detail/delete",
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
                        $(".serial_number").each(function(i) {
                            $(this).html(i+1);
                        });
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

    $('body').on("keydown", ".rate", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;

        if(charCode == 13) {
            evt.preventDefault();
            var rate            = $(this).val();
            var rateElement     = this;
            var quantity        = $(this).parent().parent().find(".quantity").val();
            var tempDetailId    =  $(this).parent().parent().data('tempdetail-id');

            if(rate <= 0) {
                $(this).addClass('has-error');
                var defaultRate = $(this).data('default-rate');
                $(this).val(defaultRate);
                return false;
            }
            if(quantity <= 0) {
                $(this).parent().parent().find(".quantity").addClass('has-error');
                var defaultQuantity = $(this).parent().parent().find(".quantity").data('default-quantity');
                $(this).parent().parent().find(".quantity").val(defaultQuantity);
                return false;
            }

            var subTotal    = (rate * 1) * (quantity * 1);
            $(this).parent().parent().find(".sub_total").val(subTotal);

            if(tempDetailId) {
                $.ajax({
                    url: "/sale/detail/edit",
                    method: "post",
                    data: {
                        _token: token,
                        id: tempDetailId,
                        rate: rate,
                        elementFlag: 1,
                    },
                    success: function(result) {
                        if(result && result.flag) {
                            var totalBill = result.totalBill;
                            $('#bill_amount').val(totalBill);
                            $(rateElement).data('default-rate', result.defaultRate);
                            calculateTotalBill();
                        } else {
                            console.log("ajax request failed #7");
                        }
                    },
                    error: function () {
                        console.log("ajax request failed #8");
                    }
                });
            }
        }
    });

    $('body').on("keydown", ".quantity", function (evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;

        if(charCode == 13) {
            evt.preventDefault();
            var quantity        = $(this).val();
            var quantityElement = this;
            var rate            = $(this).parent().parent().find(".rate").val();
            var tempDetailId    =  $(this).parent().parent().data('tempdetail-id');

            if(rate <= 0) {
                $(this).parent().parent().find(".rate").addClass('has-error');
                var defaultRate = $(this).parent().parent().find(".rate").data('default-rate');
                $(this).parent().parent().find(".rate").val(defaultRate);
                return false;
            }
            if(quantity <= 0) {
                $(this).addClass('has-error');
                var defaultQuantity = $(this).data('default-quantity');
                $(this).val(defaultQuantity);
                return false;
            }

            var subTotal    = (rate * 1) * (quantity * 1);
            $(this).parent().parent().find(".sub_total").val(subTotal);

            if(tempDetailId) {
                $.ajax({
                    url: "/sale/detail/edit",
                    method: "post",
                    data: {
                        _token: token,
                        id: tempDetailId,
                        quantity: quantity,
                        elementFlag: 1,
                    },
                    success: function(result) {
                        if(result && result.flag) {
                            var totalBill = result.totalBill;
                            $('#bill_amount').val(totalBill);
                            $(quantityElement).data('default-quantity', result.defaultQuantity);
                            calculateTotalBill();
                        } else {
                            console.log("ajax request failed #9");
                        }
                    },
                    error: function () {
                        console.log("ajax request failed #10");
                    }
                });
            }
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
    totalBill       = ($('#bill_amount').val() * 1);
    tax             = ($('#tax_amount').val() * 1);
    discount        = $('#discount').val();
    oldBalance      = ($('#old_balance').val() * 1);
    payment         = $('#payment').val();

    if(discount.length == 2 && discount.charAt(discount.length-1) == '.') {
        //code for removing last '.'
        discount = discount.slice(0,-1);
    }
    //for removing the preceding zero
    discount = discount * 1;

    if(payment.length == 2 && payment.charAt(payment.length-1) == '.') {
        //code for removing last '.'
        payment = payment.slice(0,-1);
    }
    //for removing the preceding zero
    payment = payment * 1;

    if(totalBill && totalBill >= 1 && totalBill > discount) {
        deductedTotal   = 0;
        deductedTotal  = (((totalBill * 1) + (tax * 1)) - discount);
    } else {
        deductedTotal  = (((totalBill * 1) + (tax * 1)));
        $('#discount').val(0);
    }

    totalAmount = 0;
    balance     = 0;
    totalAmount = deductedTotal + oldBalance;
    balance     = totalAmount - payment;
    totalAmountDisplay  = totalAmount;
    balanceAmount       = balance;

    if(balance < 0) {
        $('#balance_label').html('<b style="color: green;">Adance</b>');
        balanceAmount = balance * -1;
    } else {
        $('#balance_label').html('<b style="color: red;">Balance</b>');
    }

    if(totalAmount < 0) {
        $('#total_amount_label').html('<b style="color: green;">Outstanding Amount[Advance]</b>');
        totalAmountDisplay = totalAmount * -1;
    } else {
        $('#total_amount_label').html('<b style="color: red;">Outstanding Amount[Balance]</b>');
    }

    $('#deducted_total').val(deductedTotal);
    $('#total_amount').val(totalAmount);
    $('#total_amount_display').html(totalAmountDisplay);
    $('#balance').val(balance);
    $('#balance_amount').html(balanceAmount);
}