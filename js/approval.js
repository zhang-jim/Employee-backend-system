const status_array = { undetermined: '審核中', approved: '通過', unapproved: '未通過' }
function dataShow(num = 1, review_status = 'undetermined') {
    $('.pagination .page-link').removeClass("pageActive");
    $('.pagination .page-link').eq(num - 1).addClass("pageActive");
    $('.pagination .page-link').eq(num - 1).attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: 'api/search.php',
        data: { type: 'pending_leave_requests', review_status: review_status, num: num },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.rev-form-tab tbody').html('');
            if (result.status === 'false') {
                alert("權限不足，無法查看!")
                window.location.href = "/"
            } else {
                if (result.leave_list !== null) {
                    result.leave_list.forEach(function (item, value) {
                        let status = item["status"]
                        for (let key in status_array) {
                            if (status == key) {
                                status = status_array[key]
                                break;
                            }
                        }
                        $('.rev-form-tab tbody').append(
                            `<tr class="rev-form-border">
                                <td>${item["name"]}</td>
                                <td>${item["category"]}</td> 
                                <td>${item["leave_category"]}</td>
                                <td>${item["start_date"]} ~ ${item["finish_date"]}</td>
                                <td>${status}</td>
                                <td class="w13"><button class="staff_btn-a chk-monyform" onclick="showDetail(${item["leave_id"]});">審核</button></td>
                            </tr>
                        `);
                        if (review_status == 'undetermined') {
                            $('.rev-form-tab tbody tr td').last().show();
                            $('.rev-form-tab thead tr td').last().show();
                        } else {
                            $('.rev-form-tab tbody tr td').last().remove();
                            $('.rev-form-tab thead tr td').last().hide();
                            $('.reviewcard .revtext-w').hide();
                        }
                    });
                } else {
                    $('.rev-form-tab tbody').html("<span>尚無假單申請</span>");
                }
            }

        }
    });
};
function pageCount(review_status = 'undetermined') {
    $.ajax({
        type: 'GET',
        url: 'api/pagechange.php',
        data: { type: 'leave_count', class: 'approval', review_status: review_status },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.pagination').html('');
            for (let i = 1; i <= result['COUNT']; i++) {
                $('.pagination').append(`<li class="page-item"><a class="page-link pagebtn">${i}</a></li>`);
                $('.page-link').eq(i - 1).click(function () {
                    $('.page-link').removeClass("pageActive");
                    $('.btmBtn .pagination .page-link').attr("disabled", false);
                    dataShow(i, review_status);
                })
            }
            $('.page-link').eq(0).addClass("pageActive");
            
        }
    });
};
function approvalFilter() {
    let review_status = $('#approvalFilter').val();
    dataShow(1, review_status);
    pageCount(review_status);
}
function showDetail(id) {
    $('.revtext-w').show();
    let windowY = $('.rebtitle-col').offset().top;
    $(window).scrollTop(windowY);
    $.ajax({
        type: 'GET',
        url: 'api/search.php',
        data: { type: 'approve_leave_application', leave_id: id },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.reviewcard .revtext-w').html(
                `<span onclick="closeApproval();" class="spanClose">x</span>
                <li class="revtext-l">申請人：${result["name"]}</li>
                <li class="revtext-l">填表日期：${result["date_of_filing"]}</li>
                <li class="revtext-l">假別：${result["leave_category"]}</li>
                <li class="revtext-l">請假時間：${result["start_date"]} ~ ${result["finish_date"]}</li>
                <li class="revtext-l">事由：${result["reason"]}</li>
                <li class="revtext-l">審核狀態：
                    <select id="approvalSelect" name="approvalSelect" id="approvalSelect" onchange="approvalSelect()">
                        <option value="approved">通過</option>
                        <option value="unapproved">未通過</option>
                    </select>
                </li>
                <li class="revtext-l">  
                    <button class="approvalBtn" onclick="approval(${id})">審核送出</button>
                </li>
            `);
            if (result['sick_img'] !== null) {
                $('.reviewcard .revtext-w .revtext-l').eq(4).after
                    (`<li class="revtext-l">病假證明：
                    <div class="imgProof">
                        <img src="/${result['sick_img']}" alt="病假證明">
                    </div>
                </li>`);
            }
        }
    });
}

function approval(id) {
    const formData = {
        type: 'reviewing',
        reviewing_id: id,
        illustrate: $("#unapprovedReason").val(),
        approval: $("#approvalSelect").val()
    };

    $.ajax({
        type: 'POST',
        url: 'api/leave_application.php',
        data: JSON.stringify(formData),
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            alert(result['message']);
            if (result['status'] === 'true') {
                location.reload(true);
            }
        }
    });
}

function approvalSelect() {
    if ($('#approvalSelect').val() == 'unapproved') {
        $('.revtext-w .revtext-l:last-child').before(
            `<li class="revtext-l unapprovedReason">
            未通過說明：
            <textarea name="textarea" id="unapprovedReason" rows="5"></textarea>
          </li>
        `);
    } else {
        $('.unapprovedReason').remove();
    }
}
function closeApproval() {
    $('.reviewcard .revtext-w').hide();
}
// 預設 顯示所有資料
dataShow();
pageCount();