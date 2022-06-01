<?php 
require('vendor/autoload.php');
session_start();

class GenratePdf {

  private $password = "1234567";
  private $previewPdfFile = "./preview-page.pdf";
  private $orignalPdfFile = "./cat-m2wl.pdf";
  private $uploadPath = "./upload";

  public function __construct() {
    $this->debug = false;
    $this->time = date("Y-m-d-H-i-s");
    $this->msg = "Please enter password to process!!";
  }

  function login($password) {
    $return = false;
    if(!empty($password)) {
      if($password === $this->password) {
        $_SESSION['password'] = $password;
        $return = true;
      } else {
        unset($_SESSION['password']);
      }
    }
    return $return;
  }

  function uploadImage($request) {
    $uploadPath = $this->uploadPath;
    if(!empty($request['myFileUrl'])) {
      $myFileUrl = $request['myFileUrl'];
      $fname = explode("/", $myFileUrl);
      $fileName = "file.jpg";
      if(!empty($fname[1])) {
        $fileName = $fname;
      }
      $file = explode(".", $fileName);
      $fileploadName = $uploadPath.'/'.$fileName;
      $this->downloadFile($myFileUrl, $fileploadName);
      return ["folder" => $uploadPath, "filepath" => $_SESSION['filepath'], "ext" => $file[1], "filename" => $file[0]];
    }
    if(!empty($request['myFile']['name'])) {
        $fileName = $request['myFile']['name'];
        $file = explode(".", $fileName);
        if(in_array($file[1],["png", "jpg", "jpeg"])) {
            $fileploadName = $uploadPath.'/'.$fileName;
            move_uploaded_file($request['myFile']["tmp_name"], $fileploadName);
            $_SESSION['filepath'] = $fileName;
        }
        return ["folder" => $uploadPath, "filepath" => $_SESSION['filepath'], "ext" => $file[1], "filename" => $file[0]];
    } else {
      $file = explode(".", $_SESSION['filepath']);
      return ["folder" => $uploadPath, "filepath" => $_SESSION['filepath'], "ext" => $file[1], "filename" => $file[0]];
    }
  }

  function downloadFile($url, $path) {
    $newfname = $path;
    $file = fopen ($url, 'rb');
    if ($file) {
        $newf = fopen ($newfname, 'wb');
        if ($newf) {
            while(!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
            }
        }
    }
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
    }
}

  function genratePreview($request) {
    if($request['preview']) {
      $request['file'] = $this->previewPdfFile;
    } else {
      $request['file'] = $this->orignalPdfFile;
    }
    //
    $logo = $this->uploadImage($request);
    $thumb1 = $logo["folder"]."/".$logo["filename"]."_1_thumb.".$logo["ext"];
    $this->generateThumbnail($logo["folder"]."/".$logo["filepath"], $thumb1, 300, 180);
    $_SESSION['filepath_1_thumb'] = $thumb1;
    $thumb = $logo["folder"]."/".$logo["filename"]."_thumb.".$logo["ext"];
    $this->generateThumbnail($logo["folder"]."/".$logo["filepath"], $thumb, 180, 120);
    $_SESSION['filepath_thumb'] = $thumb;
    $tagLine = (!empty($request['tagLine'])) ? $request['tagLine']: "";
    $tagLineFont = (!empty($request['tagLineFont'])) ? $request['tagLineFont']: "";
    $tagLine2 = (!empty($request['tagLine2'])) ? $request['tagLine2']: "";
    $tagLine2Font = (!empty($request['tagLine2Font'])) ? $request['tagLine2Font']: "";
    $tagBottom = (!empty($request['tagBottom'])) ? $request['tagBottom']: "";
    $skipPage = (!empty($request['skipPage'])) ? $request['skipPage']: "";
    $footerLine = (!empty($request['footerLine'])) ? $request['footerLine']: "";
    $tagLineFooterFont = (!empty($request['tagLineFooterFont'])) ? $request['tagLineFooterFont']: "";
    $file = (!empty($request['file'])) ? $request['file']: 'preview-page.pdf';
    $preview = (!empty($request['preview'])) ? $request['preview']: false;
    $previewRequest = ["tagLine" => $tagLine, "tagLine2" => $tagLine2, "tagBottom" => $tagBottom, "footerLine" => $footerLine, "file" => $file, "skipPage" => $skipPage, "preview" => $preview, "tagLine2Font" => $tagLine2Font, "tagLineFont" => $tagLineFont, "tagLineFooterFont" => $tagLineFooterFont];
    $this->genratePdf($previewRequest);
  }

  function genratePdf($request) {
    $tagLine = $request['tagLine'];
    $tagLine2 = $request['tagLine2'];
    $tagBottom = $request['tagBottom'];

    $tagLineFont = $request['tagLineFont'] ?? 26;
    $tagLine2Font = $request['tagLine2Font'] ?? 26;
    // Set source PDF file 
    $pdf = new \Mpdf\Mpdf(); 
    if(file_exists("./".$request['file'])){ 
        $pagecount = $pdf->setSourceFile($request['file']); 
    } else {
        die('Source PDF not found!'); 
    }
    $skipPgae = [];
    if(!empty($request['skipPage']) && !$request['preview']) {
      $skipPgae = explode(",", $request['skipPage']);
    }
    $nowTagLine = [];
    // Add watermark image to PDF pages 
    for($i=1;$i<=$pagecount;$i++) {
        $tpl = $pdf->importPage($i); 
        $size = $pdf->getTemplateSize($tpl);
        $pdf->addPage(); 
        $pdf->useTemplate($tpl, 1, 1, $size['width'], $size['height'], TRUE); 
        if(in_array($i, $skipPgae)) {
          continue;
        }

        //Top Title
        if($i == 1 && !empty($tagLine)) {
          $pdf->SetFont('Trebuchet MS', 'R', $tagLineFont);
          $pdf->WriteText(20, 26, $tagLine);

          $pdf->SetFont('Trebuchet MS', 'R', $tagLine2Font);
          $pdf->WriteText(20, (35 + ($tagLine2Font/8)), $tagLine2);
        }

        //Bottom tag
        $nowTagLine = [];
        if($i == 1 && !empty($tagBottom)) {
          $tagBottoms = explode(" ", $tagBottom);

          $j = $x = 1;
          foreach($tagBottoms as $tag) {
            @$nowTagLine[$j] .= $tag." ";
            if($x == 3) {
                $x = 0;
              $j++;
            }
            $x++;
          }
          
          $k = $size['height'] - 28;
          $height = 0;
          $p = 1;
          $fontSize = $request['tagLineFooterFont'] ?? 14;
          foreach($nowTagLine as $line) {
            if($p == 1) {
              $height = $k;
            } else {
              $height = $height+($fontSize/2);
            }
            $pdf->SetFont('Trebuchet MS', 'R', $fontSize);
            $pdf->WriteText($size['width'] - 80, $height, $line);
            $p++;
          }
        }

        //Set Logo on all page
        $this->addLogo($pdf, $size, $i);

        //Footer
        $this->footerLine($pdf, $size, $request, $i);
    } 
    // Output PDF with watermark 
    if($request['preview']) {
      $pdf->Output('./my_filename.pdf','F'); 
      $_SESSION['previewPdf'] = "my_filename.pdf";
    } else {
      unlink($_SESSION['previewPdf']);
      unlink($this->uploadPath."/".$_SESSION['filepath']);
      unlink($_SESSION['filepath_thumb']);
      unlink($_SESSION['filepath_1_thumb']);
      unset($_SESSION['previewPdf']);
      unset($_SESSION['filepath']);
      unset($_SESSION['filepath_thumb']);
      unset($_SESSION['filepath_1_thumb']);
      unset($_SESSION['password']);
      $pdf->Output();
    }
  }

  function addLogo(&$pdf, $size, $i) {
    if($i == 1) {
      $fileploadName = $_SESSION['filepath_1_thumb'];
      if(!empty($fileploadName)) {
        $ext = @end(explode(".", $fileploadName));
        $xxx_final = ($size['width']-90); 
        $pdf->Image($fileploadName, $xxx_final, 8, 0, 0, $ext, '', true, false);
      }
    } else {
      $fileploadName = $_SESSION['filepath_thumb'];
      $ext = @end(explode(".", $fileploadName));
      $xxx_final = ($size['width']-55); 
      $pdf->Image($fileploadName, $xxx_final, 2, 0, 0, $ext, '', true, false);
    }
  }

  function footerLine(&$pdf, $size, $request, $i) {
    if($request['preview']) {
      if($i > 1 && !empty($request['footerLine'])) {
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial', 'R', 14);
        $pdf->WriteText(15, $size['height'] - 11, $request['footerLine']);
      }
    } else {
      if($i > 3 && !empty($request['footerLine'])) {
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial', 'R', 14);
        $pdf->WriteText(15, $size['height'] - 11, $request['footerLine']);
      }
    }
  }

  function generateThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height, $background=[255,255,255]) {
    list($original_width, $original_height, $original_type) = getimagesize($filepath);
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width)/ 2);
    $dest_y = intval(($thumbnail_height - $new_height)/2);

    if ($original_type === 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    } else if ($original_type === 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    } else if ($original_type === 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    } else {
        return false;
    }

    $old_image = $imgcreatefrom($filepath);
    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background

    // figuring out the color for the background
    if(is_array($background) && count($background) === 3) {
      list($red, $green, $blue) = $background;
      $color = imagecolorallocate($new_image, $red, $green, $blue);
      imagefill($new_image, 0, 0, $color);
    // apply transparent background only if is a png image
    } else if($background === 'transparent' && $original_type === 3) {
      imagesavealpha($new_image, TRUE);
      $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
      imagefill($new_image, 0, 0, $color);
    }

    imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
    $imgt($new_image, $thumbpath);
    return file_exists($thumbpath);
  }

}