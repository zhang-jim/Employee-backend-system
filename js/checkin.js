// 上班按鈕
$(".ONballon").click(function () {
  $.ajax({
    type: 'GET',
    url: 'api/check.php',
    data: { type: "online" },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
      if (result["message"] === "True") {
        location.reload();
        alert(result["content"]);
      } else {
        alert(result["content"]);
        $('.ONballon').attr("disabled", true);
      }
    }
  })
})
// 下班按鈕
$(".OFFballon").click(function () {
  $.ajax({
    type: 'GET',
    url: 'api/check.php',
    data: { type: "off" },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
      if (result["message"] === "True") {
        location.reload();
        alert(result["content"]);
      } else {
        alert(result["content"]);
        $('.OFFballon').attr("disabled", true);
      }
    }
  })
})
// 補上班卡按鈕
$(".forgotballon").click(function () {
  $.ajax({
    type: 'GET',
    url: 'api/check.php',
    data: { type: "forgot" },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
      if (result["message"] === "True") {
        location.reload();
        alert(result["content"]);
      } else {
        alert(result["content"]);
        $('.forgotballon').attr("disabled", true);
      }
    }
  })
})


