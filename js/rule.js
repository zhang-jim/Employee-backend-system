function error() {
    alert("此功能未開發!")
}

$.ajax({
    type: 'GET',
    url: 'api/memberdata.php',
    data: {
        type: 'rule'
    },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
        $('.username p').html(`Hi ~ ${result['name']} 您好 !`);
        // 基本功能
        $(".content").html(
            `<a href="checkin.html" class="shape-ex11 indexcard">打卡</a>
                <a href="form.html" class="shape-ex11 indexcard" id="myBtn">表單申請</div>`);
        if (result["rule"] == 1) {
            $(".content.indexcardset").append(`<a href="approval.html" class="shape-ex11 indexcard phoneshape">表單審核</a>`)
        }
        if (result["rule"] == 'admin' || result["rule"] == 1) {
            $(".content.indexcardset").append(`<div class="shape-ex11 content indexcard indexcardset2 dflex phoneshape">主管查閱</div>`);
            $('.indexcardset2').on('click', function () {
                $('.indexcardset2').html("");
                $('.indexcardset2').html(`
                        <a href="attendance-status.html" class="w1458 phoneshape">出缺勤狀況</a>
                        <a href="staff-checkin-list.html" class="w1458 phoneshape">員工打卡列表</a>
                        <a href="staff-leave-list.html" class="w1458 phoneshape">員工假單列表</a>
                    `);
            });
        }
    }
});

