$('#edit').click(function(){
  $('#edit').hide();
  $('#add ,#del ,#add2 ,#del2 ,#add3 ,#del3').show(); 
  $('.oneadd >li').each(function(){
    var content = $(this).html();
    $(this).html('<textarea>' + content + '</textarea>');
  });  
  $('.twoadd >li').each(function(){
    var content = $(this).html();
    $(this).html('<textarea>' + content + '</textarea>');
  });  
  $('.threeadd >li').each(function(){
    var content = $(this).html();
    $(this).html('<textarea>' + content + '</textarea>');
  }); 
  
  $('#save').show();
  $("#save").addClass("w-100");
  $('.info').fadeIn('fast');


});

$('#save').click(function(){
  $('#save, .info').hide();
  $('#add ,#del ,#add2 ,#del2 ,#add3 ,#del3').hide();
  $('textarea').each(function(){
    var content = $(this).val();//.replace(/\n/g,"<br>");
    $(this).html(content);
    $(this).contents().unwrap();    
  }); 

  $('#edit').show(); 
  $("#edit").addClass("w-100");
});