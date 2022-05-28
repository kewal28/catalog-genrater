<?php 

require('functions.php');
if(!empty($_SESSION['password'])) {
  header('location: index.php');
}
$message = "";
if(isset($_POST['password']) && !empty($_POST['password'])) {
  $genratePdf = new GenratePdf();
  $output = $genratePdf->login($_POST['password']);
  if(!$output) {
    $message = "Password is incorrect!!";
  }
  header('location: index.php');
}
?>

<!DOCTYPE html>
<html>
<title>Password Please</title>
<body><div style="margin-top: 200px; text-align: center; background-color: white; width: 350px; padding: 20px; box-sizing: border-box; box-shadow: 0px 0px 10px 0px #3B240B; position: absolute; left: 40%">
<form method="post" action="" id="login_form">
<h2>Please Entry Password.</h2>
<input type="password" name="password" style="width: 95%;" placeholder="*******" require>
<br><br>
<input type="submit" name="submit_pass" value="SUBMIT">
<?php if(!empty($message)) {?><p style="color:red;"><?php echo $message; ?></p><?php } ?>
</form>
</div></body>
</html>