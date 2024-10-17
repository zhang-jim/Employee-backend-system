CKEDITOR.replace( 'editor', {

  format_tags: 'p;h1;h2;h3;h4;h5;h6',

  filebrowserUploadMethod:"form",
  filebrowserUploadUrl:"./api/upload.php",

  removeButtons: 'PasteFromWord'
  
} );
