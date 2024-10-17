$(function() {
  $('.tab-area li').click(function() {
    var index = $('.tab-area li').index(this);
    $('.tab-content .main-area').css('display','none');
    $('.tab-content .main-area').eq(index).css('display','flex');
  });
});

//time
var show2 = document.querySelector('#showtime');
var myTimer = setInterval(changeEvery5 );

function changeEvery5() {
  var today = new Date();
  show2.innerHTML = today.toLocaleTimeString();
}
document.getElementById('demo').innerHTML = (new Date(new Date().toString().split('GMT')[0]+' UTC').toISOString().split('T')[0]);

//Fileimg-
var loadFile = function(event) {
  var output = document.getElementById('output3');
  output.src = URL.createObjectURL(event.target.files[0]);
};