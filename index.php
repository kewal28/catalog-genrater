<?php 

require('functions.php');
if(empty($_SESSION['password'])) {
  header('location: login.php');
}
if(!empty($_POST['preview'])) {
  // print_r($_POST); die;
  $request = ["myFile" => $_FILES['MyFile'],
  'skipPage' => $_POST['SkipPage'],
  'tagLine' => $_POST['TagLine'],
  'tagLineFont' => $_POST['TagLineFont'],
  'tagLine2' => $_POST['TagLine2'],
  'tagLine2Font' => $_POST['TagLine2Font'],
  'tagBottom' => $_POST['TagBottom'],
  'tagLineFooterFont' => $_POST['TagLineFooterFont'],
  'preview' => true,
  'footerLine' => $_POST['FooterLine']];
  $genratePdf = new GenratePdf();
  $output = $genratePdf->genratePreview($request);
}
if(!empty($_POST['generate'])) {
  $request = ["myFile" => $_FILES['MyFile'],
  'skipPage' => $_POST['SkipPage'],
  'tagLine' => $_POST['TagLine'],
  'tagLineFont' => $_POST['TagLineFont'],
  'tagLine2' => $_POST['TagLine2'],
  'tagLine2Font' => $_POST['TagLine2Font'],
  'preview' => false,
  'tagBottom' => $_POST['TagBottom'],
  'tagLineFooterFont' => $_POST['TagLineFooterFont'],
  'footerLine' => $_POST['FooterLine']];
  $genratePdf = new GenratePdf();
  $output = $genratePdf->genratePreview($request);
}
$pdf = "./preview-page.pdf";
if(!empty($_SESSION['previewPdf']) && file_exists($_SESSION['previewPdf'])) {
  $pdf = $_SESSION['previewPdf']."?time=".time();
}
?>

<!DOCTYPE html>
  <html>
    <head>
  <title>Upload Logo File</title>
  <style>
    .body {
      max-width: 1600px;
      width: 100%;
      /* background-color: #3B240B; */
      margin: auto;
    }
    .left {
      max-width: 400px;
      height: 100%;
      width: 35%;
      /* background-color: yellow; */
      text-align: center; 
      position: fixed; 
      left:0px; 
      padding: 5px; 
    }
    .right {
      text-align: center;
      background-color: white;
      width: 800px;
      margin:0px 400px;
      height: 800px;
      background:#000;
      padding: 5px;
      box-sizing: border-box;
      box-shadow: 0px 0px 10px 0px #3B240B;
    }
  </style>
  </head>
  <body>
    <div class="body">
    <div class="left">
    <h2>Generate Product Catalog for clients</h2>
    <form action="" method="post" enctype="multipart/form-data">
    <table style="width: 100%; text-align: left; padding: 10px">
      <tr>
        <td style="padding: 10px">Logo</td>
        <td><input type="file" id="MyFile" name="MyFile" style="padding: 10px; height: 20px; width: 80%"> </td>
      </tr> 
      <tr> 
        <td style="padding: 10px">Skip Page</td>
        <td><input type="text" name="SkipPage" style="padding: 10px; height: 20px; width: 80%" placeholder="SkipPage" value="2,3,16,24,28,44,60,64,76" /></td>
      </tr>
      <tr >
        <td style="padding: 10px">First Page Tag Line 1</td>
        <td><input type="text" name="TagLine" style="padding: 10px; height: 20px; width: 80%" value="<?php echo @$_POST['TagLine'] ?>"></td>
      </tr> 
      <tr >
        <td style="padding: 10px">Font Size First Page Tag Line 1</td>
        <td><input type="range" min="20" max="60" name="TagLineFont" value="<?php echo @$_POST['TagLineFont'] ?? 26 ?>" class="slider" id="myRange1"> <span id="myRange1-value"></span></td>
      </tr> 
      <tr >
        <td style="padding: 10px">First Page Tag Line 2</td>
        <td><input type="text" name="TagLine2" style="padding: 10px; height: 20px; width: 80%" value="<?php echo @$_POST['TagLine2'] ?>"></td>
      </tr> 
      <tr >
        <td style="padding: 10px">Font Size First Page Tag Line 2</td>
        <td><input type="range" min="10" max="60" name="TagLine2Font" value="<?php echo @$_POST['TagLine2Font'] ?? 26 ?>" class="slider" id="myRange2"> <span id="myRange2-value"></span></td>
      </tr> 
      <tr>  
        <td style="padding: 10px">First Page Bottom Tag Line</td>
        <td><textarea name="TagBottom" style="padding: 10px; height: 30px; width: 80%"><?php echo @$_POST['TagBottom'] ?></textarea></td>
      </tr>
      <tr>
        <td>Footer Line</td>
        <td><input type="text" name="FooterLine" value="<?php echo @$_POST['FooterLine'] ?>" style="padding: 10px; height: 20px; width: 80%" placeholder="Footer line" /></td>
      </tr> 
      <tr >
        <td style="padding: 10px">Font Size Footer Line</td>
        <td><input type="range" min="5" max="30" name="TagLineFooterFont" value="<?php echo @$_POST['TagLineFooterFont'] ?? 14 ?>" class="slider" id="myRange3"> <span id="myRange3-value"></span></td>
      </tr> 
      <tr> 
        <td></td>
        <td>
          <input type="submit" name="preview" value="Preview">&nbsp; &nbsp;
        <input type="submit" name="generate" value="Generate"></td>
      </tr>
      </table>
    </form>
    </div>
    <div class="right">
    <iframe src="<?php echo $pdf; ?>" style="width: 100%; height: 800px;"></iframe>
    </div>
  </div>
  </body>
  <script>
  var slider1 = document.getElementById("myRange1");
  var output1 = document.getElementById("myRange1-value");
  output1.innerHTML = slider1.value;

  slider1.oninput = function() {
    document.getElementById("myRange1").value = this.value;
    output1.innerHTML = this.value;
  }

  var slider2 = document.getElementById("myRange2");
  var output2 = document.getElementById("myRange2-value");
  output2.innerHTML = slider2.value;

  slider2.oninput = function() {
    document.getElementById("myRange2").value = this.value;
    output2.innerHTML = this.value;
  }

  var slider3 = document.getElementById("myRange3");
  var output3 = document.getElementById("myRange3-value");
  output3.innerHTML = slider3.value;

  slider3.oninput = function() {
    document.getElementById("myRange3").value = this.value;
    output3.innerHTML = this.value;
  }
  </script>
</html>