function loginStrify($status = 'statuslook' ){
    $.ajax({
        type: 'GET',
        url: 'api/login.php',
        data: { type: $status },
        contentType: "application/json; charset=utf-8",
        success: (result) => {
            if (result['status'] == 'false') {
                alert(result['message'])
                window.location.href = "login.html";
            }
        }
    });
}
loginStrify();
$('#loginout').click(function () {
    loginStrify('loginout')
})