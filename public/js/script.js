var script = (function () {
    function randomString() {
        var possible =
            "AbCdEfGhIjKlMnOpQrStUvWxYzaBcDeFgHiJkLmNoPqRsTuVwXyZ135792468";
        var randomString = "";
        var character, randomNumber;
        while (randomString.length < 5) {
            randomNumber = Math.floor(Math.random() * Math.floor(60));
            character = possible.substr(randomNumber, 1);
            randomString += character;
        }
        return randomString;
    }

    function calcTotal() {
        var total = 0;
        $("#calc-total .figure").each(function () {
            var curFigure = $(this).val() != "" ? $(this).val() : 0;
            total += parseFloat(curFigure);
        });
        $("#figure-total").val(total.toFixed(2));
    }

    function toggleSubmitButton() {
        if ($("#selected-employees table").length > 0) {
            $("#outcome-submit-button").removeClass("hide");
            $("#outcome-save-button").removeClass("hide");
        } else {
            $("#outcome-submit-button").addClass("hide");
            $("#outcome-save-button").addClass("hide");
        }
    }
    function initialize() {
        $("[data-toggle='tooltip']").tooltip();
        if ($("#disable-return").length > 0) {
            $(document).keydown(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                }
            });
        }
        $("#change-pw-form").on("submit", function (e) {
            var form = $(this);
            var firstField = $("#new-pw-1").val();
            var secondField = $("#new-pw").val();
            if (firstField !== secondField) {
                e.preventDefault();
                $.alert(
                    "Please enter the same password twice for confirmation"
                );
            } else {
                return true;
            }
        });
        $(".editconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to edit this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".viewall-confirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content:
                    "Any unsaved changes will be lost. Are you sure you want to view all saved records?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".closeconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to close this PMS Round?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".openconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to open this PMS Round?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".logout-confirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to logout?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".deleteconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content:
                    "Are you sure you want to delete this record? This action cannot be undone.",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(document).on("change", "#DepartmentId", function () {
            var deptId = $(this).val();
            if (deptId !== "") {
                $("#DesignationLocation option").each(function () {
                    var curOption = $(this);
                    var deptIdOption = curOption.data("deptids");
                    var check = false;
                    for (var x in deptIdOption) {
                        if (parseInt(deptIdOption[x]) === parseInt(deptId)) {
                            check = true;
                        }
                    }
                    if (!check) {
                        curOption.addClass("hide").prop("disabled", true);
                    } else {
                        curOption.removeClass("hide").prop("disabled", false);
                    }
                });
                $("#DesignationLocation").val("");
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            } else {
                $("#DesignationLocation option")
                    .removeClass("hide")
                    .prop("disabled", false);
                $("#DesignationLocation").val("");
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            }
        });
        $(".select2:not(.select2multiple)").select2({
            theme: "bootstrap4",
        });
        $(".select2multiple").select2({
            theme: "bootstrap4",
            placeholder: "All",
        });
        $(".select2multiple2").select2({
            theme: "bootstrap4",
            placeholder: "All Departments",
        });

        if ($("#DepartmentId").length && $("#DesignationLocation").length) {
            var deptId = $("#DepartmentId").val();
            if (deptId !== "") {
                $("#DesignationLocation option[value!='']").each(function () {
                    var curOption = $(this);
                    var deptIdOption = curOption.data("deptids");
                    var check = false;
                    for (var x in deptIdOption) {
                        if (parseInt(deptIdOption[x]) === parseInt(deptId)) {
                            check = true;
                        }
                    }
                    if (!check) {
                        curOption.addClass("hide").prop("disabled", true);
                    } else {
                        curOption.removeClass("hide").prop("disabled", false);
                    }
                });
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            }
        }
        $(".reset-password").on("click", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var name = $(this).data("name");
            var baseUrl = $("body").data("baseurl");
            $.confirm({
                title: "Reset Password",
                content:
                    "" +
                    '<form action="' +
                    baseUrl +
                    '/resetpassword" class="formName" >' +
                    '<div class="form-group">' +
                    "<label>Enter new password for " +
                    name +
                    "</label>" +
                    '<input type="hidden" placeholder="Your name" value="' +
                    id +
                    '" name="Id" class="id form-control" required />' +
                    '<input type="text" placeholder="New Password" name="Password" class="password form-control" required />' +
                    "</div>" +
                    "</form>",
                buttons: {
                    formSubmit: {
                        text: "Submit",
                        btnClass: "btn-blue",
                        action: function () {
                            var password = this.$content
                                .find(".password")
                                .val();
                            var id = this.$content.find(".id").val();
                            if (!password) {
                                $.alert("Provide a Password");
                                return false;
                            } else {
                                password = encodeURIComponent(
                                    encodeURI(password)
                                );
                                window.location.href =
                                    baseUrl +
                                    "/resetpassword?Id=" +
                                    id +
                                    "&Password=" +
                                    password;
                            }
                        },
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find("form").on("submit", function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger("click"); // reference the button and click it
                    });
                },
            });
        });
        $(".fetch-error-detail").on("click", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var baseUrl = $("body").data("baseurl");
            $("#error-detail .modal-body").empty();
            $.ajax({
                url: baseUrl + "/fetcherrordetail",
                dataType: "JSON",
                type: "POST",
                data: { id: id },
                success: function (data) {
                    $("#error-detail .modal-body").html(
                        "<pre>" + data.detail + "</pre>"
                    );
                    $("#error-detail").modal("show");
                },
            });
        });
        $(".setcriteria").on("click", function () {
            var deptId = $(this).data("deptid");
            var positionId = $(this).data("positionid");
            var dept = $(this).data("dept");
            var position = $(this).data("position");
            $("#position-name").html("<em>" + position + "</em>");
            $("#dept-name").text(dept + " Department");
            var baseUrl = $("body").data("baseurl");
            $("#criteria-modal-form").load(
                baseUrl + "/fetchcriteriainput",
                { deptId: deptId, positionId: positionId },
                function () {
                    $("#criteria-modal").modal("show");
                }
            );
        });
        $(document).on("click", ".add-new-row", function () {
            var curElement = $(this);
            var curTable = curElement.closest("table");
            var lastRow = curTable.find("tr").not(".dont-clone").last();
            var clonedRow = lastRow.clone();
            var curControl, curControlName, indexOfFirstBracket, oldString;
            var newString = randomString();
            clonedRow.find("input,select,textarea").each(function () {
                curControl = $(this);
                if (
                    curControl.attr("type") != "checkbox" &&
                    curControl.attr("type") != "radio"
                ) {
                    curControl.val("");
                }

                curControlName = curControl.attr("name");
                indexOfFirstBracket = curControlName.indexOf("[");
                oldString = curControlName.substr(indexOfFirstBracket + 1, 5);
                curControl.attr(
                    "name",
                    curControlName.replace(oldString, newString)
                );
                if (
                    curControl.attr("type") == "checkbox" ||
                    curControl.attr("type") == "radio"
                ) {
                    curControl.removeAttr("checked");
                    curControl.prop("checked", false);
                }
            });
            lastRow.after(clonedRow);
        });
        $(document).on("click", ".delete-row", function () {
            var curElement = $(this);
            var curTable = curElement.closest("table");
            var rowCount = curTable.find("tbody tr").not(".dont-clone").length;
            if (rowCount > 1) {
                var reply = true;
                if (curElement.hasClass("has-confirmation")) {
                    reply = confirm(curElement.data("message"));
                }
                if (reply) {
                    curElement.closest("tr").remove();
                }
            } else {
                $.alert("<strong>Sorry!</strong> You cannot delete all rows");
            }
            if (curTable.find(".goal-weightage").length) {
                console.log("here");
                var tableTotal = 0;
                curTable.find(".goal-weightage").each(function () {
                    tableTotal +=
                        $(this).val() === "" ? 0 : parseFloat($(this).val());
                });
                curTable.find(".goal-total").val(tableTotal.toFixed(1));
            }
        });
        $(document).on("submit", "#set-criteria-form", function (e) {
            var passed = true;
            var form = $(this).closest("form");
            var value1 = $("#WeightageForLevel1").val();
            var value2 =
                $("#WeightageForLevel2").val() == ""
                    ? 0
                    : $("#WeightageForLevel2").val();
            var ratingTotal = parseFloat(value1) + parseFloat(value2);
            var message = "";
            if (ratingTotal != 100.0) {
                passed = false;
                message += "<br/>The total for Rating Weightage should be 100.";
            }
            var assessmentTotal = 0;
            $(".assessment-weightage").each(function () {
                if ($(this).val() != "") {
                    assessmentTotal += parseFloat($(this).val());
                }
            });
            if (parseFloat(assessmentTotal) != 100.0) {
                passed = false;
                message +=
                    "<br/>The total for Assessment Area Weightage should be 100.";
            }
            if (!passed) {
                e.preventDefault();
                $.alert(
                    message +
                        "<br/><br/><strong>Please correct these errors in order to set criteria</strong>"
                );
            } else {
                $("#validate-criteria").attr("disabled", "disabled");
                return true;
            }
        });
        // $("#ExcelApplicant").on('change',function(){
        //     if(!$("#ExcelApplicant").val()){
        //         $.alert("Please select a file to upload!");
        //     }else{
        //         $("#score").val('');
        //         var formData = new FormData();
        //         formData.append('file',$("#ExcelApplicant")[0].files[0]);
        //         var baseUrl = $("body").data('baseurl');
        //
        //         $.ajax({
        //             url: baseUrl+"/uploadexcelapplicant",
        //             type: "POST",
        //             contentType: false,
        //             processData: false,
        //             data: formData,
        //             success: function (data) {
        //
        //                 if(data.success == false){
        //                     $.alert(data.message);
        //                 }else{
        // var finalScore = data.score;
        // var max = $("#score").attr('max');
        // var min = $("#score").attr('min');
        // var description = $("#score").closest('tr').find('.description').text();
        // if(parseFloat(finalScore) > parseFloat(max)){
        //     $.alert(description+" cannot have more than "+max+" points. Please enter correct value.");
        // }else if(parseFloat(finalScore) < parseFloat(min)){
        //     $.alert(description+" cannot have less than "+min+" points. Please enter correct value.");
        // }else{
        //     $("#score").val(finalScore);
        // }
        // calcTotal();
        //                 }
        //             }
        //         });
        //     }
        // });
        // $("#ExcelApplicant2").on('change',function(){
        //     if(!$("#ExcelApplicant2").val()){
        //         $.alert("Please select a file to upload!");
        //     }else{
        //         $("#score").val('');
        //         var formData = new FormData();
        //         formData.append('file',$("#ExcelApplicant2")[0].files[0]);
        //         var baseUrl = $("body").data('baseurl');
        //
        //         $.ajax({
        //             url: baseUrl+"/uploadexcelapplicant2",
        //             type: "POST",
        //             contentType: false,
        //             processData: false,
        //             data: formData,
        //             success: function (data) {
        //                 if(data.success == false){
        //                     $.alert(data.message);
        //                 }
        //             }
        //         });
        //     }
        // });
        $(document).on("keyup change click", ".figure", function () {
            var value = $(this).val();
            var max = $(this).attr("max");
            var min = $(this).attr("min");
            var description = $(this).closest("tr").find(".description").text();
            if (parseFloat(value) > parseFloat(max)) {
                $(this).val("");
                $.alert(
                    description +
                        " cannot have more than " +
                        max +
                        " points. Please enter correct value."
                );
            } else if (parseFloat(value) < parseFloat(min)) {
                $(this).val("");
                $.alert(
                    description +
                        " cannot have less than " +
                        min +
                        " points. Please enter correct value."
                );
            }
            calcTotal();
        });
        $(document)
            .ajaxStart(function () {
                $("#loader").removeClass("hide");
            })
            .ajaxStop(function () {
                $("#loader").addClass("hide");
            });
        $(document).on("click", "#send-back", function (e) {
            e.preventDefault();
            var linkHref = $(this).attr("href");
            var remarks = $("#Remarks").val();
            if (remarks == "") {
                $.alert(
                    "Please enter a remarks when Sending back an Application"
                );
            } else {
                window.location.href = linkHref + "?remarks=" + remarks;
            }
        });
        $(document).on("submit", "form", function () {
            $(this)
                .find("button[type='submit']")
                .not("#validate-criteria")
                .not(".dont-disable")
                .attr("disabled", "disabled");
        });
        $(document).on("keyup keydown change", ".goal-weightage", function () {
            var table = $(this).closest("table");
            var tableTotal = 0;
            table.find(".goal-weightage").each(function () {
                tableTotal +=
                    $(this).val() === "" ? 0 : parseFloat($(this).val());
            });
            table.find(".goal-total").val(tableTotal.toFixed(1));
        });
        $(".save-goals").on("click", function (e) {
            var status = $(this).data("status");
            var form = document.getElementById("goals-form");
            $("#goal-status").val(status);
            if (form.checkValidity()) {
                var tableTotal = 0;
                $(".goal-weightage").each(function () {
                    tableTotal +=
                        $(this).val() === "" ? 0 : parseFloat($(this).val());
                });
                if (parseFloat(tableTotal) !== 100.0) {
                    e.preventDefault();
                    $.alert("Total must equal 100 marks");
                }
            }
        });
        $("#subordinate-performance-goal").on("click", function (e) {
            var id = $("input[name='Id']").val();
            console.log(id);
            $("#subordinate-pg-form").load(
                "/fetchsubordinategoals",
                { id: id },
                function (data) {
                    $("#subordinate-pg-modal").modal("show");
                }
            );
        });
        $("#subordinate-performance-goal-l2").on("click", function (e) {
            var id = $("input[name='Id']").val();
            console.log(id);
            $("#subordinate-pg-form").load(
                "/fetchsubordinategoalsl2",
                { id: id },
                function (data) {
                    $("#subordinate-pg-modal").modal("show");
                }
            );
        });
        $(document).on("click", "#save-appraisee", function () {
            var form = $(this).closest("form");
            var inputs = new FormData(form[0]);
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/saveappraisee",
                type: "POST",
                data: inputs,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.success == true) {
                        window.location.reload();
                    } else {
                        $.alert(data.message);
                    }
                },
            });
        });
        $(document).on("click", "#save-appraiser", function () {
            var form = $(this).closest("form");
            var inputs = new FormData(form[0]);
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/saveappraiser",
                type: "POST",
                data: inputs,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.success == true) {
                        window.location.reload();
                    } else {
                        $.alert(data.message);
                    }
                },
            });
        });
        $(document).on("change", "#PMSOutComeId", function () {
            var outcomeId = $(this).val();
            var reference = $("option:selected", this).data("reference");
            if (outcomeId != "") {
                $(
                    "#PaySlabWrapper input, #PayScaleWrapper input, #PaySlabWrapper select, #PayScaleWrapper select,#PositionWrapper input,#PositionWrapper select,#DesignationLocationWrapper input, #DesignationLocationWrapper select,#BasicPayWrapper input,#BasicPayWrapper select"
                ).val("");
                $(
                    "#PaySlabWrapper .select2, #PayScaleWrapper .select2, #BasicPayWrapper .select2, #PositionWrapper .select2, #DesignationLocationWrapper .select2"
                ).select2({
                    theme: "bootstrap4",
                });
                var payChange = $("option:selected", this).data("paychange");
                var positionChange = $("option:selected", this).data(
                    "positionchange"
                );
                var basicPayChange = $("option:selected", this).data(
                    "basicpaychange"
                );
                var designationLocationChange = $("option:selected", this).data(
                    "designationlocationchange"
                );

                if (payChange == 1) {
                    $("#PayScaleWrapper").removeClass("hide");
                    $("#PaySlabWrapper").removeClass("hide");
                } else {
                    $("#PayScaleWrapper").addClass("hide");
                    $("#PaySlabWrapper").addClass("hide");
                }

                if (positionChange == 1) {
                    $("#PositionWrapper").removeClass("hide");
                } else {
                    $("#PositionWrapper").addClass("hide");
                }

                if (basicPayChange == 1) {
                    $("#BasicPayWrapper").removeClass("hide");
                    $("#PaySlabWrapper").removeClass("hide");
                } else {
                    $("#BasicPayWrapper").addClass("hide");
                    $("#PaySlabWrapper").addClass("hide");
                }

                if (designationLocationChange == 1) {
                    $("#DesignationLocationWrapper").removeClass("hide");
                } else {
                    $("#DesignationLocationWrapper").addClass("hide");
                }

                if (reference == 1 || reference == 2 || reference == 5) {
                    var oldBasicPay = $("#old-basic-pay").val();
                    var increment = $("#old-payscale-increment").val();
                    if (reference == 1 || reference == 5) {
                        var newBasicPay =
                            parseFloat(oldBasicPay) + parseFloat(increment);
                    } else {
                        var newBasicPay =
                            parseFloat(oldBasicPay) + 2 * parseFloat(increment);
                    }
                    $("#BasicPay").val(newBasicPay.toFixed(0));
                }
            } else {
                $("#PayScaleWrapper").addClass("hide");
                $("#PositionWrapper").addClass("hide");
                $("#BasicPayWrapper").addClass("hide");
                $("#DesignationLocationWrapper").addClass("hide");
                $("#PaySlabWrapper").addClass("hide");
            }
        });
        $(document).on("change", "#fetch-employees-dept", function () {
            var deptId = $(this).val();
            var baseUrl = $("body").data("baseurl");
            if (deptId != "") {
                $.ajax({
                    url:
                        baseUrl +
                        "/fetchdepartmentemployees/" +
                        deptId +
                        "/" +
                        0 +
                        "/" +
                        1,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        $("select#fetched-employees").empty();
                        var html = "<option value=''>--SELECT ONE--</option>";
                        for (var x in data) {
                            html +=
                                "<option value='" +
                                data[x].Id +
                                "'>" +
                                data[x].Name +
                                " - CID No:" +
                                data[x].CIDNo +
                                ", Emp Id:" +
                                data[x].EmpId +
                                " (" +
                                data[x].Designation +
                                ")</option>";
                        }
                        $("select#fetched-employees").html(html);
                        $("#fetched-employees").select2("destroy");
                        $("#fetched-employees").select2({
                            theme: "bootstrap4",
                        });
                    },
                });
            } else {
            }
        });
        $(document).on("change", ".populate-basic-pay", function () {
            if ($(this).val() != "") {
                var basicPay = $("option:selected", this).data("basicpay");
                if (basicPay > 0) $("#BasicPay").val(basicPay);
                else $("#BasicPay").val("");
            } else {
                $("#BasicPay").val("");
            }
        });

        $(document).on("change", "#filter-section", function () {
            var deptId = $(this).val();
            if (deptId != "") {
                $("#select-department option[value!='']").each(function () {
                    var curOption = $(this);
                    var optionDeptId = curOption.data("departmentid");
                    if (optionDeptId == deptId) {
                        $(this).removeClass("hide").prop("disabled", false);
                    } else {
                        $(this).addClass("hide").prop("disabled", true);
                    }
                });
                if ($(this).hasClass("fetch-employee-on-dept")) {
                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url:
                            baseUrl +
                            "/fetchdepartmentemployees/" +
                            deptId +
                            "/" +
                            0 +
                            "/" +
                            1,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            $("select#fetched-employees").empty();
                            var html = "<option value=''>All</option>";
                            for (var x in data) {
                                html +=
                                    "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    " (" +
                                    data[x].Designation +
                                    ")</option>";
                            }
                            $("select#fetched-employees").html(html);
                            $("#fetched-employees").select2("destroy");
                            $("#fetched-employees").select2({
                                theme: "bootstrap4",
                            });
                        },
                    });
                }
            } else {
                $("select#fetched-employees").empty();
                var html = "<option value=''>All</option>";
                $("select#fetched-employees").html(html);
                $("#fetched-employees").select2("destroy");
                $("#fetched-employees").select2({
                    theme: "bootstrap4",
                });

                $("#select-department option")
                    .removeClass("hide")
                    .prop("disabled", false);
            }
            $("#select-department option").prop("selected", false);
            $("#select-department").select2("destroy");
            $("#select-department").select2({
                theme: "bootstrap4",
            });
        });

        $(document).on("change", "#select-department", function () {
            var sectionId = $(this).val();
            if (sectionId != "") {
                var deptId = $("option:selected", this).data("departmentid");
                $("#filter-section option[value='" + deptId + "']").prop(
                    "selected",
                    true
                );
                $("#filter-section").select2("destroy");
                $("#filter-section").select2({
                    theme: "bootstrap4",
                });

                if ($(this).hasClass("fetch-employee-on-section")) {
                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url:
                            baseUrl +
                            "/fetchsectionemployees/" +
                            sectionId +
                            "/" +
                            0 +
                            "/" +
                            1,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            $("select#fetched-employees").empty();
                            var html = "<option value=''>All</option>";
                            for (var x in data) {
                                html +=
                                    "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    " (" +
                                    data[x].Designation +
                                    ")</option>";
                            }
                            $("select#fetched-employees").html(html);
                            $("#fetched-employees").select2("destroy");
                            $("#fetched-employees").select2({
                                theme: "bootstrap4",
                            });
                        },
                    });
                }
            } else {
                $("select#fetched-employees").empty();
                var html = "<option value=''>All</option>";
                $("select#fetched-employees").html(html);
                $("#fetched-employees").select2("destroy");
                $("#fetched-employees").select2({
                    theme: "bootstrap4",
                });

                $("#filter-section option").prop("selected", false);
                $("#filter-section").select2("destroy");
                $("#filter-section").select2({
                    theme: "bootstrap4",
                });
            }
        });
        $("#FilterType").on("change", function () {
            var value = $(this).val();
            $(".toggle-filter[data-filtertype='" + value + "']").removeClass(
                "hide"
            );
            $(
                ".toggle-filter[data-filtertype='" + value + "'] select"
            ).removeAttr("disabled");
            $(".toggle-filter[data-filtertype!='" + value + "']").addClass(
                "hide"
            );
            $(".toggle-filter[data-filtertype!='" + value + "'] select").attr(
                "disabled",
                "disabled"
            );
        });
        $("#TargetRevenue,#AchievedRevenue,#FinalAdjustmentPercent").on(
            "keyup change",
            function () {
                var targetRevenue = $("#TargetRevenue").val();
                var achievedRevenue = $("#AchievedRevenue").val();
                var finalAdjustment = $("#FinalAdjustmentPercent").val();
                if (
                    targetRevenue != "" &&
                    achievedRevenue != "" &&
                    finalAdjustment != ""
                ) {
                    var calculated =
                        (parseFloat(achievedRevenue) /
                            parseFloat(targetRevenue)) *
                        parseFloat(finalAdjustment);
                    calculated =
                        calculated > finalAdjustment
                            ? finalAdjustment
                            : calculated;
                    calculated = parseFloat(calculated);
                    $("#calculated").val(calculated.toFixed(2));
                } else {
                    $("#calculated").val("");
                }
            }
        );

        $("#Grade").on("change", function () {
            var payScaleType = $(this).data("payscaletype");
            var oldBasicPay = $("#old-basic-pay").val();
            oldBasicPay = parseFloat(oldBasicPay);

            var basicPay;
            var lastpay;
            var middlePay;
            var increment;
            var firstIncrement;
            var secondIncrement;
            var newBasicPay;
            var outcomeReference;

            if (parseInt(payScaleType) === 1) {
                basicPay = $("option:selected", this).data("basepay");
                lastpay = $("option:selected", this).data("lastpay");
                basicPay = parseFloat(basicPay);
                increment = $("option:selected", this).data("increment");
                increment = parseFloat(increment);
                newBasicPay = 0;
                outcomeReference = $("option:selected #PMSOutComeId").data(
                    "reference"
                );
            } else {
                basicPay = $("option:selected", this).data("basepay");
                middlePay = $("option:selected", this).data("middlepay");
                lastpay = $("option:selected", this).data("lastpay");
                basicPay = parseFloat(basicPay);
                middlePay = parseFloat(middlePay);
                firstIncrement = $("option:selected", this).data(
                    "firstincrement"
                );
                secondIncrement = $("option:selected", this).data(
                    "secondincrement"
                );
                firstIncrement = parseFloat(firstIncrement);
                secondIncrement = parseFloat(secondIncrement);
                newBasicPay = 0;
                outcomeReference = $("option:selected #PMSOutComeId").data(
                    "reference"
                );
                increment =
                    middlePay <= oldBasicPay ? secondIncrement : firstIncrement;
                basicPay = middlePay <= oldBasicPay ? middlePay : basicPay;
            }
            if (basicPay - oldBasicPay < increment) {
                newBasicPay = basicPay + increment;
                while (
                    newBasicPay - oldBasicPay < increment &&
                    newBasicPay + increment <= lastpay
                ) {
                    newBasicPay = newBasicPay + increment;
                }
            } else {
                newBasicPay = basicPay;
            }
            $("#BasicPay").val(newBasicPay.toFixed(0));
        });

        $("#FinalOutcomeId").on("change", function () {
            var value = $(this).val();
            if (value == 1) {
                $(".select-container").addClass("hide");
            } else {
                $(".select-container").removeClass("hide");
            }
        });
        if ($("#FinalOutcomeId").val() != "") {
            var value = $("#FinalOutcomeId").val();
            var outcomeId = value;
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            if (value !== "") {
                $(".SavedPMSOutcomeId").each(function () {
                    if (value == $(this).val()) {
                        var curRow = $(this).closest("tr");
                        var employeeId = curRow.find(".emp-id").val();
                        console.log("check");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .attr("checked", "checked");
                        var checkTableExists = $(
                            "#outcome-" + outcomeId
                        ).length;
                        var clonedRow = curRow.clone();
                        clonedRow.find(".select-container").remove();
                        clonedRow.find(".remove-container").removeClass("hide");
                        clonedRow.find(".emp-id").removeAttr("disabled");
                        clonedRow.find(".submission-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").val(outcomeId);
                        if (checkTableExists == 0) {
                            var tableToClone = $("#table-to-clone");
                            var tableHtml =
                                '<input type="hidden" name="DeletedEmployeeIds" id="deleted-emp-ids"/><div class="row"><div class="col-md-12"><br/><h5 style="color:#fff;">Employees nominated for ' +
                                outcomeName +
                                '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                                outcomeId +
                                '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                            var theader = tableToClone.find("thead").html();
                            tableHtml += "<thead>";
                            tableHtml += theader;
                            tableHtml += "</thead>";
                            tableHtml += "<tbody>";
                            tableHtml += "</tbody>";
                            tableHtml += "</table></div></div></div>";
                            $("#selected-employees").append(tableHtml);
                        }
                        $("#outcome-" + outcomeId)
                            .find("tbody")
                            .append(clonedRow);
                        $("#outcome-save-button").removeClass("hide");
                        $("#outcome-submit-button").removeClass("hide");
                    } else {
                        console.log("uncheck");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .removeAttr("checked");
                        var rowInSelected = $("#selected-employees")
                            .find(".emp-id[value='" + employeeId + "']")
                            .closest("tr");
                        var containerSelected =
                            rowInSelected.closest("div.row");
                        rowInSelected.remove();
                        if (containerSelected.find("tbody tr").length === 0) {
                            containerSelected.remove();
                        }
                    }
                });
            }
        }
        $(document).on("change", "#FinalOutcomeId", function () {
            var value = $("#FinalOutcomeId").val();
            var outcomeId = value;
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            if (value !== "") {
                $(".SavedPMSOutcomeId").each(function () {
                    var curRow = $(this).closest("tr");
                    var employeeId = curRow.find(".emp-id").val();
                    if (value == $(this).val()) {
                        console.log("check");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .attr("checked", "checked");
                        var checkTableExists = $(
                            "#outcome-" + outcomeId
                        ).length;
                        var clonedRow = curRow.clone();
                        clonedRow.find(".select-container").remove();
                        clonedRow.find(".remove-container").removeClass("hide");
                        clonedRow.find(".emp-id").removeAttr("disabled");
                        clonedRow.find(".submission-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").val(outcomeId);
                        if (checkTableExists == 0) {
                            var tableToClone = $("#table-to-clone");
                            var tableHtml =
                                '<div class="row"><div class="col-md-12"><br/><h5 stydden" name="DeletedEmployeeIds" id="deleted-emp-ids"/>le="color:#fff;">Employees nominated for ' +
                                outcomeName +
                                '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                                outcomeId +
                                '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                            var theader = tableToClone.find("thead").html();
                            tableHtml += "<thead>";
                            tableHtml += theader;
                            tableHtml += "</thead>";
                            tableHtml += "<tbody>";
                            tableHtml += "</tbody>";
                            tableHtml += "</table></div></div></div>";
                            $("#selected-employees").append(tableHtml);
                        }
                        $("#outcome-" + outcomeId)
                            .find("tbody")
                            .append(clonedRow);
                        $("#outcome-save-button").removeClass("hide");
                        $("#outcome-submit-button").removeClass("hide");
                    } else {
                        console.log("uncheck");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .removeAttr("checked");
                        var rowInSelected = $("#selected-employees")
                            .find(".emp-id[value='" + employeeId + "']")
                            .closest("tr");
                        var containerSelected =
                            rowInSelected.closest("div.row");
                        rowInSelected.remove();
                        if (containerSelected.find("tbody tr").length === 0) {
                            containerSelected.remove();
                        }
                    }
                });
            }
        });
        $(document).on("change", ".select-employee", function () {
            console.log("here");
            var curRow = $(this).closest("tr");
            var outcomeId = $("#FinalOutcomeId").val();
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            var employeeId = curRow.find(".emp-id").val();
            if ($(this).is(":checked")) {
                var checkTableExists = $("#outcome-" + outcomeId).length;
                var clonedRow = curRow.clone();
                clonedRow.find(".select-container").remove();
                clonedRow.find(".remove-container").removeClass("hide");
                clonedRow.find(".emp-id").removeAttr("disabled");
                clonedRow.find(".submission-id").removeAttr("disabled");
                clonedRow.find(".outcome-id").removeAttr("disabled");
                clonedRow.find(".outcome-id").val(outcomeId);
                if (checkTableExists == 0) {
                    var tableToClone = $("#table-to-clone");
                    var tableHtml =
                        '<input type="hidden" name="DeletedEmployeeIds" id="deleted-emp-ids"/><div class="row"><div class="col-md-12"><br/><h5 style="color:#fff;">Employees nominated for ' +
                        outcomeName +
                        '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                        outcomeId +
                        '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                    var theader = tableToClone.find("thead").html();
                    tableHtml += "<thead>";
                    tableHtml += theader;
                    tableHtml += "</thead>";
                    tableHtml += "<tbody>";
                    tableHtml += "</tbody>";
                    tableHtml += "</table></div></div></div>";
                    $("#selected-employees").append(tableHtml);
                }
                $("#outcome-" + outcomeId)
                    .find("tbody")
                    .append(clonedRow);
            } else {
                var rowInSelected = $("#selected-employees")
                    .find(".emp-id[value='" + employeeId + "']")
                    .closest("tr");
                var containerSelected = rowInSelected.closest("div.row");
                rowInSelected.remove();
                if (containerSelected.find("tbody tr").length === 0) {
                    containerSelected.remove();
                }
            }
            toggleSubmitButton();
        });
        $(document).on("click", ".remove-employee", function () {
            var curRow = $(this).closest("tr");
            var employeeId = curRow.find(".emp-id").val();
            var containerSelected = curRow.closest("div.row");
            curRow.remove();
            var oldValueOfHiddenField = $("#deleted-emp-ids").val();
            if (!oldValueOfHiddenField) {
                oldValueOfHiddenField = employeeId;
            } else {
                oldValueOfHiddenField += "," + employeeId;
            }
            $("#deleted-emp-ids").val(oldValueOfHiddenField);
            if (containerSelected.find("tbody tr").length === 0) {
                containerSelected.remove();
            }
            $("#table-to-clone .emp-id[value='" + employeeId + "']")
                .closest("tr")
                .find(".select-employee")
                .prop("checked", false);
            toggleSubmitButton();
        });
        var outcomeFormSubmitted = false;
        $(document).on("submit", "#outcome-form", function () {
            outcomeFormSubmitted = true;
        });
        $(document).on("click", "#outcome-save-button", function (e) {
            e.preventDefault();
            $("#outcome-form input[name='_token']").after(
                "<input type='hidden' name='SubmitType' value='1'/>"
            );
            $("#outcome-form").submit();
        });
        $(document).on("click", "#outcome-submit-button", function (e) {
            e.preventDefault();
            $("#outcome-form input[name='_token']").after(
                "<input type='hidden' name='SubmitType' value='2'/>"
            );
            $("#outcome-form").submit();
        });
        if ($("#table-to-clone").length) {
            $(window).bind("beforeunload", function () {
                if (outcomeFormSubmitted == false) {
                    if ($("#selected-employees table").length > 0) {
                        return "Are you sure you want to leave? Changes not saved will be lost!";
                    }
                }
            });
        }
        $(document).on("click", ".remove-required", function () {
            $("input[type!='hidden'], select").removeAttr("required");
        });
        $(document).on("click", ".back-btn", function (e) {
            e.preventDefault();
            history.back();
        });

        if (window.File && window.FileReader && window.Blob) {
            //SUPPORTS FILE APIs
            $(document).on(
                "change",
                "input[type='file']:not(.guideline-doc)",
                function () {
                    if ($(this).val()) {
                        var file = this.files[0];
                        var size = file.size;
                        var sizeInMB = size / 1024 / 1024;
                        if (sizeInMB > 5) {
                            $(this).val("");
                            $.alert(
                                "Error! Please select a file that is smaller than 5 MB in size"
                            );
                        }
                    }
                }
            );
        }
        if ($("[data-rel='lightcase']").length > 0) {
            $("[data-rel='lightcase']").lightcase();
        }
        $(document).on(
            "change",
            "#form-daterestriction #FromDate",
            function () {
                var fromDate = $(this).val();
                var toDate = $("#ToDate").val();
                if (fromDate != "") {
                    $("#ToDate").attr("min", fromDate);
                    if (toDate != "") {
                        var fromDateFormatted = new Date(fromDate);
                        var toDateFormatted = new Date(toDate);
                        if (fromDateFormatted > toDateFormatted) {
                            $("#ToDate").val("");
                        }
                    }
                }
            }
        );
        $(document).on("keyup", "#SearchBox", function () {
            var searchKeyword = $(this).val();
            searchKeyword = searchKeyword.toLowerCase();
            if (searchKeyword !== "") {
                $("#accordion .ui-accordion-header").each(function () {
                    var accordionMatch = false;
                    var curAccordion = $(this);
                    var accordionId = curAccordion.attr("id");
                    var curAccordionBody = $(
                        "[aria-labelledby='" + accordionId + "']"
                    );
                    curAccordionBody.find("tbody tr").each(function () {
                        var curRow = $(this);
                        var rowMatch = false;
                        curRow.find("td:not(:last-child)").each(function () {
                            var text = $(this).text();
                            text = text.toLowerCase();
                            if (text.indexOf(searchKeyword) > -1) {
                                accordionMatch = true;
                                rowMatch = true;
                            }
                        });
                        if (rowMatch === false) {
                            curRow.addClass("hide");
                        } else {
                            curRow.removeClass("hide");
                        }
                    });
                    if (accordionMatch === false) {
                        curAccordion.addClass("hide");
                        curAccordionBody.addClass("hide");
                    } else {
                        curAccordion.removeClass("hide");
                        curAccordionBody.removeClass("hide");
                    }
                });
            } else {
                $("#accordion .ui-accordion-header").removeClass("hide");
                $("#accordion .ui-accordion-content").removeClass("hide");
                $("#accordion tr").removeClass("hide");
            }
        });
        // $(document).on('keyup change click',".total-in-hidden",function(){
        //     var curRow = $(this).closest('tr');
        //     var figure = curRow.find('.figure');
        //     var total = 0;
        //     $(".total-in-hidden").each(function(){
        //         if($(this).val()!=''){
        //             total += parseFloat($(this).val());
        //         }
        //     });
        //     curRow.find(".figure").val(total);
        //
        //     var value = figure.val();
        //     var max = figure.attr('max');
        //     var min = figure.attr('min');
        //     var description = $(this).closest('tr').find('.description').text();
        //     if(parseFloat(value) > parseFloat(max)){
        //         figure.val('');
        //         $(this).val('');
        //         $.alert(description+" cannot have more than "+max+" points. Please enter correct value.");
        //     }else if(parseFloat(value) < parseFloat(min)) {
        //         figure.val('');
        //         $(this).val('');
        //         $.alert(description + " cannot have less than " + min + " points. Please enter correct value.");
        //     }
        //     calcTotal();
        // });
        $(".emailofficeorder").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            var empEmail = $(this).data("email");
            $.confirm({
                title: "Confirm email address!",
                content:
                    "" +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    "<label>Please confirm email of employee</label>" +
                    '<input type="text" placeholder="Email" value="' +
                    empEmail +
                    '" class="email form-control" required />' +
                    "</div>" +
                    "</form>",
                buttons: {
                    formSubmit: {
                        text: "Submit",
                        btnClass: "btn-blue",
                        action: function () {
                            var email = this.$content.find(".email").val();
                            if (!email) {
                                $.alert("provide a valid email address");
                                return false;
                            }
                            window.location.href = href + "&email=" + email;
                        },
                    },
                    cancel: function () {
                        //close
                    },
                },
            });
        });
        $(document).on("change", ".filter-category", function () {
            var value = $(this).val();
            if (value !== "") {
                $(
                    ".category option[data-departmentid!='" +
                        value +
                        "'][value!='']"
                )
                    .addClass("hide")
                    .attr("disabled", "disabled");
                $(
                    ".category option[data-departmentid='" +
                        value +
                        "'][value!='']"
                )
                    .removeClass("hide")
                    .removeAttr("disabled");
            } else {
                $(".category option")
                    .removeClass("hide")
                    .removeAttr("disabled");
            }
            $(".category").val("");
        });
        $(document).on("change", ".filter-category-ajax", function () {
            var value = $(this).val();
            if (value !== "") {
                $.ajax({
                    url:
                        $("body").data("baseurl") +
                        "/fetchcategoriesondepartment",
                    dataType: "JSON",
                    type: "POST",
                    data: { deptId: value },
                    success: function (data) {
                        console.log(JSON.stringify(data));
                        $(".category option[value!='']").remove();
                        for (var x in data) {
                            $(".category").append(
                                "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    "</option>"
                            );
                        }
                    },
                });
            } else {
                $(".category option[value!='']").remove();
            }
            $(".category").val("");
        });
        $(document).on("click", ".open-file-modal", function (e) {
            e.preventDefault();
            var file = $(this).data("filepath");
            var title = $(this).data("title");
            var baseUrl = $("body").data("baseurl");
            $("#file-iframe").attr(
                "src",
                baseUrl + "/filedisplay?file=" + file
            );
            $("#file-modal .modal-title").text(title);
            $("#file-modal").modal("show");
        });
        $(document).on("change", "#VisibilityLevel1", function () {
            var value = $(this).val();
            if (value == 99) {
                $("#VisibilityLevel").attr("disabled", "disabled").val("");
                $("#VisibilityLevel").select2("destroy");
                $("#VisibilityLevel").select2({
                    theme: "bootstrap4",
                    placeholder: "--SELECT--",
                });
            } else {
                $("#VisibilityLevel").removeAttr("disabled");
            }
        });
        $(document).on("click", ".upload-kpi-template", function () {
            var type = $(this).data("type");
            var file = $(this).closest(".row").find(".kpi-file")[0];
            var formData = new FormData();
            formData.append("file", file.files[0]);
            formData.append("_token", $("input[name='_token']").val());
            formData.append("type", type);
            $.ajax({
                url: "/uploadkpifile",
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (data) {
                    $("tbody[data-type='" + type + "']").html(data);
                },
            });
        });
        $(document).on("change", ".clear-pms-period", function () {
            if ($(this).val() != "") {
                $("#PMSPeriod").val("");
                $("#PMSPeriod").select2("destroy");
                $("#PMSPeriod").select2({
                    theme: "bootstrap4",
                    placeholder: "All",
                });
            }
        });
        $(document).on("select2:select", "#PMSPeriod", function () {
            console.log("here");
            if ($(this).val() != "") {
                $(".clear-pms-period").val("");
                $(".clear-pms-period").select2("destroy");
                $(".clear-pms-period").select2({
                    theme: "bootstrap4",
                    placeholder: "All",
                });
            }
        });
        $(document).on("click", "#submit-process", function (e) {
            e.preventDefault();
            var isValid = $("#process-form")[0].checkValidity();
            if (!isValid) {
                $("#process-form")[0].reportValidity();
            } else {
                //check via ajax
                $.ajax({
                    url: "/checkappraisersubmitted",
                    dataType: "JSON",
                    type: "POST",
                    data: { id: $("input[name='Id']").val() },
                    success: function (data) {
                        if (data.pass === false) {
                            $.alert(
                                "Please mark the employee's Goal and Targets"
                            );
                        } else {
                            $("#process-form").append(
                                "<input type='hidden' name='SaveType' value='1'/>"
                            );
                            $("#process-form").submit();
                        }
                    },
                });
                //end check
            }
        });

        ////// pay slab for increment and promotion for employees (pay slip scale)
        var pmsOutComeId = $("#PMSOutComeId").val();
        var basicpayscale = "";
        var basicPay = 0;
        var paySlab = 0;

        if (pmsOutComeId != "") {
            if (pmsOutComeId == 2) {
                // calculating basic pay for generating office order
                $(".pay-slab-id").on("change", function () {
                    paySlab = Number($(".pay-slab-id").val());
                    basicPay = Number($("#OldBasicPay").val());
                    basicpayscale = $("#BasicPayScale").val();

                    var payScaleSplit = basicpayscale.split("-");
                    var firstPayScale = Number(payScaleSplit[0]);
                    var increment = Number(payScaleSplit[1]);
                    var lastPayScale = Number(payScaleSplit[2]);

                    if (paySlab != "") {
                        var newBasicPay = 0;
                        var incrementRaise = 0;
                        if (
                            basicPay >= firstPayScale &&
                            basicPay <= lastPayScale
                        ) {
                            incrementRaise = increment * paySlab;
                            if (incrementRaise <= 600) {
                                incrementRaise = 600;
                                newBasicPay = incrementRaise + basicPay;
                            } else {
                                newBasicPay = incrementRaise + basicPay;
                            }
                        } else if (basicPay > lastPayScale) {
                            incrementRaise = increment * paySlab;
                            if (incrementRaise <= 600) {
                                incrementRaise = 600;
                                newBasicPay = incrementRaise + basicPay;
                            } else {
                                newBasicPay = incrementRaise + basicPay;
                            }
                        }

                        $("#BasicPay").val(newBasicPay.toFixed(0));
                    }
                });
            } else if (pmsOutComeId == 4) {
                $("#Grade").on("change", function () {
                    var stepId = $("#Grade").val();
                    if (stepId != "") {
                        var baseUrl = $("body").data("baseurl");
                        $.ajax({
                            url: baseUrl + "/getgradestep/" + stepId,
                            type: "GET",
                            dataType: "JSON",
                            success: function (data) {
                                basicpayscale = data[0].PayScale;
                                $("#BasicPayScale").val(basicpayscale);

                                var payScaleSplit = basicpayscale.split("-");
                                var firstPayScale = Number(payScaleSplit[0]);
                                var increment = Number(payScaleSplit[1]);
                                var lastPayScale = Number(payScaleSplit[2]);

                                basicPay = Number($("#OldBasicPay").val());

                                // calculating basic pay for generating office order
                                $(".pay-slab-id").on("change", function () {
                                    paySlab = Number($(".pay-slab-id").val());
                                    if (paySlab != "") {
                                        var newBasicPay = 0;
                                        var incrementRaise = 0;
                                        if (
                                            basicPay >= firstPayScale &&
                                            basicPay <= lastPayScale
                                        ) {
                                            incrementRaise =
                                                increment * paySlab;
                                            if (incrementRaise <= 600) {
                                                incrementRaise = 600;
                                                newBasicPay =
                                                    incrementRaise + basicPay;
                                            } else {
                                                newBasicPay =
                                                    incrementRaise + basicPay;
                                            }
                                        } else if (basicPay > lastPayScale) {
                                            incrementRaise =
                                                increment * paySlab;
                                            if (incrementRaise <= 600) {
                                                incrementRaise = 600;
                                                newBasicPay =
                                                    incrementRaise + basicPay;
                                            } else {
                                                newBasicPay =
                                                    incrementRaise + basicPay;
                                            }
                                        }

                                        $("#BasicPay").val(newBasicPay.toFixed(0));
                                    }
                                });
                            },
                        });
                    }
                });
            }
        }

        // get process Id from pms finalize with change in action
        $("#PMSOutComeId").on("change", function () {
            pmsOutComeId = $("#PMSOutComeId").val();
            if (pmsOutComeId != "") {
                if (pmsOutComeId == 2) {
                    // calculating basic pay for generating office order
                    $(".pay-slab-id").on("change", function () {
                        paySlab = Number($(".pay-slab-id").val());
                        basicPay = Number($("#OldBasicPay").val());
                        basicpayscale = $("#BasicPayScale").val();

                        var payScaleSplit = basicpayscale.split("-");
                        var firstPayScale = Number(payScaleSplit[0]);
                        var increment = Number(payScaleSplit[1]);
                        var lastPayScale = Number(payScaleSplit[2]);

                        if (paySlab != "") {
                            var newBasicPay = 0;
                            var incrementRaise = 0;
                            if (
                                basicPay >= firstPayScale &&
                                basicPay <= lastPayScale
                            ) {
                                incrementRaise = increment * paySlab;
                                if (incrementRaise <= 600) {
                                    incrementRaise = 600;
                                    newBasicPay = incrementRaise + basicPay;
                                } else {
                                    newBasicPay = incrementRaise + basicPay;
                                }
                            } else if (basicPay > lastPayScale) {
                                incrementRaise = increment * paySlab;
                                if (incrementRaise <= 600) {
                                    incrementRaise = 600;
                                    newBasicPay = incrementRaise + basicPay;
                                } else {
                                    newBasicPay = incrementRaise + basicPay;
                                }
                            }

                            $("#BasicPay").val(newBasicPay.toFixed(0));
                        }
                    });
                } else if (pmsOutComeId == 4) {
                    $("#Grade").on("change", function () {
                        var stepId = $("#Grade").val();
                        if (stepId != "") {
                            var baseUrl = $("body").data("baseurl");
                            $.ajax({
                                url: baseUrl + "/getgradestep/" + stepId,
                                type: "GET",
                                dataType: "JSON",
                                success: function (data) {
                                    basicpayscale = data[0].PayScale;
                                    $("#BasicPayScale").val(basicpayscale);

                                    var payScaleSplit =
                                        basicpayscale.split("-");
                                    var firstPayScale = Number(
                                        payScaleSplit[0]
                                    );
                                    var increment = Number(payScaleSplit[1]);
                                    var lastPayScale = Number(payScaleSplit[2]);

                                    basicPay = Number($("#OldBasicPay").val());

                                    // calculating basic pay for generating office order
                                    $(".pay-slab-id").on("change", function () {
                                        paySlab = Number(
                                            $(".pay-slab-id").val()
                                        );
                                        if (paySlab != "") {
                                            var newBasicPay = 0;
                                            var incrementRaise = 0;
                                            if (
                                                basicPay >= firstPayScale &&
                                                basicPay <= lastPayScale
                                            ) {
                                                incrementRaise =
                                                    increment * paySlab;
                                                if (incrementRaise <= 600) {
                                                    incrementRaise = 600;
                                                    newBasicPay =
                                                        incrementRaise +
                                                        basicPay;
                                                } else {
                                                    newBasicPay =
                                                        incrementRaise +
                                                        basicPay;
                                                }
                                            } else if (
                                                basicPay > lastPayScale
                                            ) {
                                                incrementRaise =
                                                    increment * paySlab;
                                                if (incrementRaise <= 600) {
                                                    incrementRaise = 600;
                                                    newBasicPay =
                                                        incrementRaise +
                                                        basicPay;
                                                } else {
                                                    newBasicPay =
                                                        incrementRaise +
                                                        basicPay;
                                                }
                                            }

                                            $("#BasicPay").val(newBasicPay.toFixed(0));
                                        }
                                    });
                                },
                            });
                        }
                    });
                }
            }
        });

	$(".appraiseeconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to remove appraisee submission scores?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(".appraiserconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to remove appraiser submission scores?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // end
    }
    return {
        Initialize: initialize,
        ToggleSubmitButton: toggleSubmitButton,
    };
})();
$(document).ready(function () {
    script.Initialize();
});
