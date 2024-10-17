const status_array = { undetermined: '審核中', approved: '通過', unapproved: '未通過' }

function dataShow(num, category) {
    let date = new Date;
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let now_date = year + "-" + month;
    $('.pagination .page-link').removeClass("pageActive");
    $('.pagination .page-link').eq(num - 1).addClass("pageActive");
    $('.pagination .page-link').eq(num - 1).attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: 'api/memberdata.php',
        data: { type: 'member_data_show', category: category, page: num },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.attendanceForm tbody').html('');
            if (result.status === 'false') {
                alert("權限不足，無法查看!")
                window.location.href = "/"
            } else {
                result.forEach(function (item, value) {
                    $('.attendanceForm tbody').append(
                        `<tr class="rev-form-border">
                            <td>${item["category"]}</td>
                            <td>${item["name"]}</td> 
                            <td>${item["EntryDate"]}</td>
                            <td>
                            <i onclick="eachData('${item["name"]}' , '${now_date}');" class="fa-solid fa-eye"></i>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
};
function pageCount(category) {
    $.ajax({
        type: 'GET',
        url: 'api/pagechange.php',
        data: { type: 'member_count', category: category },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.pagination').html('');
            for (let i = 1; i <= result['COUNT']; i++) {
                $('.pagination').append(`<li class="page-item"><a class="page-link pagebtn">${i}</a></li>`);
                $('.page-link').eq(i - 1).click(function () {
                    $('.page-link').removeClass("pageActive");
                    $('.btmBtn .pagination .page-link').attr("disabled", false);
                    dataShow(i, category);
                })
            }
            $('.page-link').eq(0).addClass("pageActive");
        }
    });
};
// onclick 點擊部門分類
function getval(e) {
    $('.dflex .staff_btn-a').removeClass('staff-active');
    $('.dflex .staff_btn-a').attr("disabled", false);
    $(e).addClass('staff-active');
    $(e).attr("disabled", true);
    let category = $(e).val();
    dataShow(1, category);
    pageCount(category);
};
function eachData(member, now_date) {
    $("input[name='date']").val(now_date);
    let date_array = now_date.split("-"),
    year = date_array[0],
    month = date_array[1];
    $('.dataShowBg').css("display", 'flex');
    $('body').css({ "overflow": 'hidden' });
    $('.dataShowZone h2').html(member);
    $('.dataShowZone section h3').html(`${year}年${month}月出缺勤總覽`);
    $('.dataShowZone h3').html(`${month}月請假列表`);
    $.ajax({
        type: 'GET',
        url: 'api/search.php',
        data: { type: "absence", member: member, date: now_date },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            $('.dataShowZone p').html(
                `<span>總遲到：${result["latetime"]}</span>
                <span>未打卡次數：${result["unCheckIn"]}次</span>
                <span>病假天數：${result["sick_leave"]}天</span>
                <span>事假天數：${result["personal_leave"]}天</span>
                <span>特休天數：${result["annual_leave"]}天</span>
                <span>剩餘特休：${result["annual_total"]}天</span>
                `);
            $('.annualLeaveForm tbody').html('');
            if (result.timelist !== null) {
                result.timelist.forEach(function (item, value) {
                    $('.annualLeaveForm tbody').append(
                        `<tr class="rev-form-border">
                            <td>${item['start_date']}</td>
                            <td>${item['finish_date']}</td>
                            <td>${item['leave_category']}</td>
                            <td class="p13 status">通過</td>
                        </tr>
                    `);
                });
            } else {
                $('.annualLeaveForm tbody').html(`<span class="noneData">尚無申請紀錄</spa n>`);
            }
        }
    })
};

function closeBlock() {
    $('.dataShowBg').css("display", 'none');
    $('body').css({ "overflow": 'auto' });
};
function dateselect() {
    now_date = $("input[name='date']").val();
    member = $('.dataShowZone h2').text();
    eachData(member, now_date);
}
// 預設 顯示所有資料
dataShow(1, 'all');
pageCount('all');
