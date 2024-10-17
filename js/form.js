const status_array = { undetermined: '審核中', approved: '通過', unapproved: '未通過' }
function inputCheckCss(input, inputVal) {
    if (inputVal.length == 0) {
        $(input).css('color', 'red');
    } else {
        $(input).css('color', '#ffffff');
    }
}
function dataShow(num = 1) {
    $('.pagination .page-link').removeClass("pageActive");
    $('.pagination .page-link').eq(num - 1).addClass("pageActive");
    $('.pagination .page-link').eq(num - 1).attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: 'api/search.php',
        data: { type: 'leave_record', num: num },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('#tab_1 tbody').html('');
            if (result === null) {
                $('#tab_1 tbody').html("<span>無申請紀錄</span>");
            } else {
                result.forEach(function (item, value) {
                    let status = item["status"];
                    for (let key in status_array) {
                        if (status == key) {
                            status = status_array[key];
                            break;
                        }
                    }
                    $('#tab_1 tbody').append(
                        `<tr class="rev-form-border">
                            <td>${item['date_of_filing']}</td>
                            <td>${item['name']}</td>
                            <td>${item['start_date']} ~ ${item['finish_date']}</td>
                            <td class="p13">${item['leave_category']}</td>
                            <td class="p13 status">${status}</td>
                        </tr>`
                    )
                    if (item["status"] == 'unapproved') {
                        $('#tab_1 tbody tr td.status').eq(value).html(`<span class="unapprovedBtn" onclick="viewReason('${item['illustrate']}');">未通過</span>`)
                    }
                });
            }
        }
    });
}
function pageCount() {
    $.ajax({
        type: 'GET',
        url: 'api/pagechange.php',
        data: { type: 'leave_count', class: 'form' },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.pagination').html('');
            for (let i = 1; i <= result['COUNT']; i++) {
                $('.pagination').append(`<li class="page-item"><a class="page-link pagebtn">${i}</a></li>`);
                $('.page-link').eq(i - 1).click(function () {
                    $('.page-link').removeClass("pageActive");
                    $('.btmBtn .pagination .page-link').attr("disabled", false);
                    dataShow(i);
                })
            }
            $('.page-link').eq(0).addClass("pageActive");
        }
    });
};
function viewReason(reason) {
    $('.reasonZone').css('display', 'flex');
    $('.reasonZone div p').text(reason);
}
$(".chkgohome").click(function () {
    let start_date_time = $("#datetimepicker1").val(),
        finish_date_time = $("#datetimepicker2").val(),
        category = $(".selectCategory").val(),
        reason = $("#reason").val()

    inputCheckCss(".startTime", start_date_time);
    inputCheckCss(".endTime", finish_date_time);
    inputCheckCss(".reason", reason);

    if (start_date_time && finish_date_time && reason !== "") {
        let FromData = {
            type: 'apply',
            start_date_time: start_date_time,
            finish_date_time: finish_date_time,
            category: category,
            reason: reason
        }
        if (category === "病假") {
            let imgProofData = $('.reviewcard #imgProof');
            if (imgProofData[0].files[0] !== undefined) {
                let imgFormData = new FormData();
                imgFormData.append('file', imgProofData[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: 'api/leave_application.php',
                    data: imgFormData,
                    contentType: false,
                    processData: false,
                    success: (result) => {
                        FromData.img = result['img_path']
                        $.ajax({
                            type: 'POST',
                            url: 'api/leave_application.php',
                            data: JSON.stringify(FromData),
                            contentType: "application/json; charset=utf-8",
                            success: (result) => {
                                alert(result["message"]);
                                if (result["status"] === 'true') {
                                    location.reload(true);
                                }
                            }
                        });
                    }
                });
            }else{
                alert("資料未填寫完畢!")
            }
        } else {
            $.ajax({
                type: 'POST',
                url: 'api/leave_application.php',
                data: JSON.stringify(FromData),
                contentType: "application/json; charset=utf-8",
                success: (result) => {
                    alert(result["message"]);
                    if (result["status"] === 'true') {
                        location.reload(true);
                    }
                }
            });
        }
    } else {
        alert("資料未填寫完畢!")
    }
});
pageCount()
dataShow()

$('.spanClose').on('click', function () {
    $('.reasonZone').hide();
})