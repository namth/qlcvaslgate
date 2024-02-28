jQuery(document).ready(function ($) {
  // xử lý ajax khi click vào nút đăng ký partner mới
  $('#create_partner input[type="submit"]').click(function () {
    // lấy dữ liệu từ form và mã hoá thành chuỗi
    var $data = $("#create_partner form").serialize();
    $.ajax({
      type: "POST",
      url: AJAX.ajax_url,
      data: {
        action: "add_user",
        data: $data,
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.status);
        console.log(xhr.responseText);
        console.log(thrownError);
      },
      success: function (resp) {
        // alert(resp);
        var obj = JSON.parse(resp);
        // console.log(obj);
        if (obj["status"] == "success") {
          $(obj["hide_form"]).html(obj["notification"]);
          $(obj["select_element"]).prepend(obj["content"]).show(200);
        } else {
          $(obj["div_notification"]).html(obj["notification"]);
          $("html, body").animate(
            {
              scrollTop: $("#create_new_job").offset().top,
            },
            1000
          );
        }
      },
    });
    return false;
  });

  // xử lý ajax khi click vào nút đăng ký partner nước ngoài mới
  $('#create_foreign_partner input[type="submit"]').click(function () {
    // lấy dữ liệu từ form và mã hoá thành chuỗi
    var $data = $("#create_foreign_partner form").serialize();
    $.ajax({
      type: "POST",
      url: AJAX.ajax_url,
      data: {
        action: "add_user",
        data: $data,
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.status);
        console.log(xhr.responseText);
        console.log(thrownError);
      },
      success: function (resp) {
        // alert(resp);
        var obj = JSON.parse(resp);
        // console.log(obj);
        if (obj["status"] == "success") {
          $(obj["hide_form"]).html(obj["notification"]);
          $(obj["select_element"]).prepend(obj["content"]).show(200);
        } else {
          $(obj["div_notification"]).html(obj["notification"]);
          $("html, body").animate(
            {
              scrollTop: $("#create_new_job").offset().top,
            },
            1000
          );
        }
      },
    });
    return false;
  });

  // xử lý ajax khi click vào nút đăng ký khách hàng (customer) mới
  $('#create_customer input[type="submit"]').click(function () {
    // lấy dữ liệu từ form và mã hoá thành chuỗi
    var $data = $("#create_customer form").serialize();
    $.ajax({
      type: "POST",
      url: AJAX.ajax_url,
      data: {
        action: "add_customer",
        data: $data,
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.status);
        console.log(xhr.responseText);
        console.log(thrownError);
      },
      success: function (resp) {
        // alert(resp);
        var obj = JSON.parse(resp);
        // console.log(obj);
        if (obj["status"] == "success") {
          $(obj["hide_form"]).html(obj["notification"]);
          $(obj["select_element"]).prepend(obj["content"]).show(200);
        } else {
          $(obj["div_notification"]).html(obj["notification"]);
          $("html, body").animate(
            {
              scrollTop: $("#create_new_job").offset().top,
            },
            1000
          );
        }
      },
    });
    return false;
  });

  // khi bấm nút copy từ đối tác
  $(".copy_customer").click(function () {
    $(this).hide();
    var partnerID = $('select[name="partner"]').val();

    if (partnerID) {
      $.ajax({
        type: "POST",
        url: AJAX.ajax_url,
        data: {
          action: "copy_customer",
          partnerID: partnerID,
        },
        error: function (xhr, ajaxOptions, thrownError) {
          console.log(xhr.status);
          console.log(xhr.responseText);
          console.log(thrownError);
        },
        success: function (resp) {
          var obj = JSON.parse(resp);
          console.log(obj);
          $(this)
            .show()
            .parent()
            .html(
              '<span class="text-success">Đã copy khách hàng thành công</span>'
            );
          if (obj["status"] == "success") {
            $("#customer_function").html(obj["notification"]);
            $(obj["select_element"]).prepend(obj["content"]).show(200);
          } else {
            $("#customer_function").append(obj["notification"]);
            $(".copy_customer").show();
            $("html, body").animate(
              {
                scrollTop: $("#create_new_job").offset().top,
              },
              1000
            );
          }
        },
      });
    } else {
      $(this)
        .show()
        .parent()
        .append(
          '<span class="text-danger">Hãy chọn đối tác trước khi copy</span>'
        );
    }
  });

  // xử lý khi bấm hoàn thành form tạo đầu việc mới
  $(".finish_newjob").click(function () {
    // get data from previous steps on smart wizard
    var $data_partner   = $('select[name="partner"]').val();
    var $data_foreign_partner = $('select[name="foreign_partner"]').val();
    var $data_customer  = $('select[name="customer"]').val();
    var $data_job       = $("form#new_job")[0];
    var $data_manager   = $('select[name="manager"]').val();
    var $data_member    = $('select[name="member"]').val();
    var $data_supervisor = $('select[name="supervisor"]').val();
    var $data_agency    = $('select[name="agency"]').val();
    var $currency       = $('form#finance input[name="currency"]:checked').val();
    var $total_value    = $('form#finance input[name="total_value"]').val();
    var $paid           = $('form#finance input[name="paid"]').val();

    console.log($data_supervisor.join("|"));
    //khởi tạo đối tượng form data
    var form_data = new FormData($data_job);

    form_data.append("action", "add_new_job");
    form_data.append("data_partner", $data_partner);
    form_data.append("data_foreign_partner", $data_foreign_partner);
    form_data.append("data_customer", $data_customer);
    form_data.append("data_manager", $data_manager);
    form_data.append("data_member", $data_member);
    form_data.append("data_supervisor", $data_supervisor.join("|"));
    form_data.append("data_agency", $data_agency);
    form_data.append("currency", $currency);
    form_data.append("total_value", $total_value);
    form_data.append("paid", $paid);


    console.log(form_data);

    $.ajax({
      type: "POST",
      url: AJAX.ajax_url,
      data: form_data,
      contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
      processData: false, // NEEDED, DON'T OMIT THIS
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.status);
        console.log(xhr.responseText);
        console.log(thrownError);
      },
      beforeSend: function () {
        $("#create_new_job").hide();
        $("#create_new_job")
          .parent()
          .append(
            '<img class="loading" src="https://mazdamotors.vn/images/loading.gif">'
          );
      },
      success: function (resp) {
        var obj = JSON.parse(resp);
        console.log(obj);
        $(".loading").remove();
        $("#create_new_job").show();
        if (obj["status"] == "success") {
          $(obj["div_notification"]).html(obj["notification"]);

          /* redirect toi trang chi tiet cong viec */
          window.location.replace(obj["redirect_link"]);
        } else {
          $(obj["div_notification"]).prepend(obj["notification"]);
          $("html, body").animate(
            {
              scrollTop: $("#create_new_job").offset().top,
            },
            1000
          );
        }
      },
    });

    return false;
  });

  /* khi click vào chọn danh mục trong phần tạo job mới thì set giá trị cho hidden form ['danh_muc'] */
  $("#choose_group li a").click(function () {
    var danh_muc = $(this).data("group");
    $('input[name="danh_muc"]').val(danh_muc);
  });

  // when range change, ajax sent
  $("#list_task_by_date").change(function () {
    //khởi tạo đối tượng form data
    var date_value = $(this).val();
    var form_data = new FormData();

    form_data.append("action", "list_task_by_date");
    form_data.append("date_value", date_value);
    // gọi ajax để lọc thông tin
    $.ajax({
      type: "POST",
      url: AJAX.ajax_url,
      data: form_data,
      contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
      processData: false, // NEEDED, DON'T OMIT THIS
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.status);
        console.log(xhr.responseText);
        console.log(thrownError);
      },
      success: function (resp) {
        console.log(resp);
        $("tbody").html(resp);
      },
    });

    return false;
  });

  $('input[name="nguon_dau_viec"]').change(function () {
    // bind a function to the change event
    var val = $(this).val();
    if (val == "Giới thiệu") {
      $("#select_partner_1").show(300);
    } else {
      $("#select_partner_1").hide(300);
    }
  });

  $('input[name="time_option"]').change(function () {
    // bind a function to the change event
    var val = $(this).val();
    if (val == 1) {
      $("#timestamp").show(300);
    } else {
      $("#timestamp").hide(300);
    }
  });

  /* addnew_partner.php
    Switch chuyển phân loại sang công ty thì sẽ hiện ô nhập danh sách thành viên
  */
  $('input[name="phan_loai"]').change(function() {
    var val = $(this).val();
    // alert(val);
    if (val == 1) {
      $("#phanloai").show(300);
    } else {
      $("#phanloai").hide(300);
    }
  });
  /* addnew_partner.php
    Switch chuyển phân loại sang công ty thì sẽ hiện ô nhập danh sách thành viên
  */
  $('input[name="fdi"]').change(function() {
    var val = $(this).val();
    // alert(val);
    if (val == 1) {
      $("#fdi").show(300);
    } else {
      $("#fdi").hide(300);
    }
  });
});
