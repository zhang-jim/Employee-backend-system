const status_array = { undetermined: '審核中', approved: '通過', unapproved: '未通過' }
let category = 'all'
let date = new Date;
let year = date.getFullYear();
let month = (date.getMonth() + 1).toString().padStart(2, '0');
let now_date = year + "-" + month
$("input[name='date']").val(now_date);

function dataShow(num, category, date) {
  $('.pagination .page-link').removeClass("pageActive");
  $('.pagination .page-link').eq(num - 1).addClass("pageActive");
  $('.pagination .page-link').eq(num - 1).attr("disabled", true);
  $.ajax({
    type: 'GET',
    url: 'api/search.php',
    data: { type: 'show_leave_list', category: category, page: num, date: date },
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
                <td>${item["date_of_filing"]}</td>
                <td>${item["name"]}</td>
                <td>${item['start_date']} ~ ${item['finish_date']}</td>
                <td>${item["leave_category"]}</td>
                <td>${status}</td>
              </tr>`);
          });
        } else {
          $('.rev-form-tab tbody').html("<span>尚無員工請假紀錄</span>");
        }
      }
    }
  });
}
function pageCount(category, date) {
  $.ajax({
    type: 'GET',
    url: 'api/pagechange.php',
    data: { type: 'all_leave_count', category: category, date: date },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
      $('.pagination').html('');
      for (let i = 1; i <= result['COUNT']; i++) {
        $('.pagination').append(`<li class="page-item"><a class="page-link pagebtn">${i}</a></li>`);
        $('.page-link').eq(i - 1).click(function () {
          $('.page-link').removeClass("pageActive");
          $('.btmBtn .pagination .page-link').attr("disabled", false);
          dataShow(i, category, date);
        })
      }
      $('.page-link').eq(0).addClass("pageActive");

      // 頁數>7的話,部分隱藏
      if (result['COUNT'] > 7) {
        let pageNum = result['COUNT'];
        let pageNumMid = Math.ceil(pageNum / 2);

        $('.page-item').hide();
        for (let i = 0; i < 3; i++) {
          $('.page-item').eq(i).show();
        }
        $('.page-item').eq(2).addClass('pageAfter');
        $('.page-item').eq(pageNumMid - 1).show();
        $('.page-item').eq(pageNum - 1).show();
        $('.page-item').eq(pageNum - 1).addClass('pageBefore');

        function pageHideShow(pageMid) {
          $('.page-item').hide();
          $('.page-item').removeClass('pageAfter');
          $('.page-item').removeClass('pageBefore');

          $('.page-item').eq(0).show();
          $('.page-item').eq(0).addClass('pageAfter');
          $('.page-item').eq(pageMid - 2).show();
          $('.page-item').eq(pageMid - 1).show();
          $('.page-item').eq(pageMid).show();
          $('.page-item').eq(pageNum - 1).show();
          $('.page-item').eq(pageNum - 1).addClass('pageBefore');

          if (pageMid == 1 || pageMid == 2 || pageMid == 3) {
            $('.page-item').eq(0).removeClass('pageAfter');
            $('.page-item').eq(2).addClass('pageAfter');
            $('.page-item').eq(pageNumMid - 1).show();
          }
          if (pageMid == 3) {
            $('.page-item').eq(2).removeClass('pageAfter');
            $('.page-item').eq(3).addClass('pageAfter');
          }
          if (pageNumMid - 1 > pageMid > 3) {
            $('.page-item').eq(pageNumMid - 1).hide();
          }
          if (pageMid == pageNum - 1 || pageMid == pageNum - 2) {
            $('.page-item').eq(pageNum - 1).removeClass('pageBefore');
            $('.page-item').eq(pageNum - 3).addClass('pageBefore');
            $('.page-item').eq(pageNumMid - 1).show();
          }
          if (pageMid == pageNum - 2) {
            $('.page-item').removeClass('pageBefore');
            $('.page-item').eq(pageNum - 4).addClass('pageBefore');
          }
          if (pageNumMid + 1 > pageMid > pageNum - 2) {
            $('.page-item').eq(pageNumMid - 1).hide();
          }
        }
        for (let n = 0; n < pageNum; n++) {
          $('.page-link').eq(n).click(function () {
            pageHideShow(n + 1);
            if (n == 0) {
              pageHideShow(2);
            }
            if (n + 1 == pageNum) {
              pageHideShow(n);
            }
          })
        }
      }
    }
  });
}
// onclick 點擊部門分類
function getval(e) {
  $('.dflex .staff_btn-a').removeClass('staff-active');
  $('.dflex .staff_btn-a').attr("disabled", false);
  $(e).addClass('staff-active');
  $(e).attr("disabled", true);
  category = $(e).val()
  dataShow(1, category, now_date)
  pageCount(category, now_date)
}
function dateSelect() {
  now_date = $("input[name='date']").val();
  dataShow(1, category, now_date)
  pageCount(category, now_date)
}
// 預設 顯示所有資料
dataShow(1, category, now_date)
pageCount(category, now_date)

