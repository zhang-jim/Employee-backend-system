  <?php
if(isset($_FILES['upload']['name'])){

    $file = $_FILES['upload']['tmp_name'];
    $file_name = $_FILES['upload']['name'];

    move_uploaded_file($file, './upload/' . $file_name);
    $function_number = $_GET['CKEditorFuncNum'];
    $url = './upload/' . $file_name;
    $message = '';
    echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";
}