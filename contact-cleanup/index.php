<?php
$title = 'Phone Numbers Extractor';
$output = $link = $donate = $hide = $reload = ""; $outputs = [];
$allowed_files = ["txt", "vcf", "doc", "docx"];
if (isset($_POST['submit'])) {
  clearstatcache();
  $uploadOk = 1;
  $target_dir = "uploads/";
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
  }
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $FileType = strtolower(trim(pathinfo($target_file,PATHINFO_EXTENSION)));
  // Check if image file is a actual image or fake image

  if (!in_array($FileType, $allowed_files)) {
    $output = "'".implode("', '", $allowed_files). "' files are only allowed.";
    $uploadOk = 0;
    die();
  }

    // Cleanup Old Files
  $old_files = scandir($target_dir);
  $old_files = array_filter($old_files, function ($value='') {
    return $value != "." && $value != "..";
  });
  $today = strtotime(date('Y-m-d h:i:s'));
  foreach($old_files as $this_file){
    $this_file = __DIR__."/{$target_dir}{$this_file}";
    if(is_file($this_file) && (round(($today - filectime($this_file))/(3600*24)) > 7)){
      unlink($this_file);
    }
  }
  // Upload the file
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      $f = $target_file;
      if($FileType == "docx" || $FileType == "doc"){
        $zip = new ZipArchive;
        if ($zip->open($target_file, ZipArchive::CREATE)!==TRUE) {
          echo "Cannot open $target_file "; die;
        }
        $xml = $zip->getFromName('word/document.xml');
        $v2=$xml;
        $zip->close();
        $v2=str_replace('w:p',"p",$v2);
        $v2=strip_tags($v2, "<p>");
        $v2=clean_tag($v2, "p");

        // Continue as html document
        $dom = new DOMDocument;
        $dom->loadHTML($v2);
        $nodes = $dom->getElementsByTagName("p");
        $c = [];
      	foreach ($nodes as $node) {
          foreach ($node->childNodes as $key => $value) {
            $c[] = $value->textContent;
          }
      	}
        $c = implode("\r", $c);
      }else{
        $h = fopen($f, "r");
        if(!$h){die("An error occurred");}
        $c = fread($h, filesize($f));
      }

      if ($c) {
          $ex = explode("\r", $c);
          foreach ($ex as $k => $v) {
              $v1 = trim($v);
              // Extract all integers
              $v1 = str_replace(" ", "", preg_replace('/[^0-9]/', '', $v1));

              // Skip incomplete numbers
              if (is_numeric($v1) === false || strlen($v1) < 8) continue;
              $v1 = "0".substr($v1, -10);
              $outputs[] = $v1;
          }
          $concatinators = [" ", ",", ";"];
          $all = count($outputs);
          if(!empty($_POST['groupby'])){
            $outputs = implode($concatinators[$_POST['concatinator']]."\r", array_map(function ($value='') use ($concatinators){
              return(implode($concatinators[$_POST['concatinator']], $value));
            }, array_chunk($outputs, $_POST['groupby'])));
          }else{
            $outputs = implode($concatinators[$_POST['concatinator']], $outputs);
          }
          $hide = "hide";
          $reload = "<a class='donate float-right' href=''>Close</a>";
          $donate = "<a class='donate' href='https://paystack.com/pay/ij7d-w2f5x'>Donate to the Developer ? </a>";
          $output = "<textarea class='form-control w-40' readonly id='numbers' style='height:60vh'>$outputs</textarea>";
          $myfile = fopen("uploads/cleancontacts.doc", "w") or die("Unable to open file!");
          fwrite($myfile, $outputs, strlen($outputs));
          fclose($myfile);
          $path = "cleancontacts.doc";
          $link = "<div class='row'><div class='col-7'><button class='btn btn-outline-dark btn-block' onclick='copyText()'>Copy {$all} Contacts</button></div>
               <div class='col-5'><a href='uploads/cleancontacts.doc' class='btn btn-outline-primary btn-block'>Download</a></div></div>";
        }
    } else {
        echo "Sorry, there was an error reading your file.";
    }
}
?>
<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='UTF-8'>
  <title>Phone Numbers Extractor and Cleanup</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Extract phone numbers from a .txt file and choose how you format them for easier bulk SMS">
  <meta name="author" content="Onuigbo Clinton">
  <meta name="generator" content="Hugo 0.80.0">
  <link rel="icon" href="http://ugoson.com/favicon.png">
  <link rel="apple-touch-icon" href="http://ugoson.com/favicon.png">
  <link rel="apple-touch-icon" sizes="72x72" href="http://ugoson.com/favicon.png">
  <link rel="apple-touch-icon" sizes="114x114" href="http://ugoson.com/favicon.png">

  <!--Bootstrap cdn-->
  <link rel='stylesheet' type='text/css' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
  <style>
    input, textarea, select{
        height: 100%;
        width: 100%;
    }
    h1{
      font-weight: bold;
    }
    select, .custom-file {    margin: 0 0 15px 0;}
    label{margin: 0}
    label:not(.custom-file-label) {    margin: 0 0 0 14px;    font-size: 14px;}
    a.donate {    background-color: dimgray;    color: white;    padding: 3px 15px;    margin: 5px 0;    float: left;    border-radius: 3px;    border: none;}
    .hide{display: none;}
  </style>

</head>
<body>
<div style='height: 2px; background: white'></div>
    <div class="jumbotron bg-primary text-center font-weight-bold text-white">
        <a href="">
          <h1 class="text-white">Extract Phone Numbers</h1>
        </a>
       <small class="text-white">Extract phone numbers from a <?="'".implode("', '", $allowed_files). "'"?> file and choose how you format them for easier bulk SMS</small>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
              <form action="" method="post" enctype="multipart/form-data" class="<?=$hide?>">
                  <div class="row">
                    <div class="col-sm-12 col-lg-6">
                      <label for=""> <small>Upload only <?="'.".implode(", .", $allowed_files). "' files are only allowed."?> files</small> </label>
                      <div class="custom-file">
                        <input placeholder="" type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload" required accept="<?=".".implode(", .", $allowed_files)?>">
                        <label class="custom-file-label" for="fileToUpload" >Upload a document file</label>
                      </div>
                    </div>
                    <div class="col-lg-3 col-sm-12 ">
                      <div class="w-100">
                        <label for=""> <small>How do you want numbers joined</small> </label>
                        <select class="custom-select ml-0" id="concatinator" name="concatinator">
                          <option selected value="0">Space ( )</option>
                          <option value="1">Comma (,)</option>
                          <option value="2">Semi-colon (;)</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-3 col-sm-12 ">
                      <div class="w-100">
                        <label for=""> <small>How many numbers per line</small> </label>
                        <select class="custom-select ml-0" id="inltineFormCustomSelect" name="groupby">
                          <option selected disabled value="0">Select Grouping</option>
                          <option value="1">One Per Line</option>
                          <option value="2">Two Per Line</option>
                          <option value="5">Five Per Line</option>
                          <option value="10">Ten Per Line</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <input type="submit" value="EXTRACT" class="btn btn-primary btn-block btn-large ml-0" name="submit" >
                    </div>
                  </div>
                </form>
                <p><?=$donate.$reload?></p>
                <div> <?= $output?></textarea>
                </div>
                <div class="pb-2"><?= $link?></div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
function copyText() {
  /* Get the text field */
  var copyText = document.getElementById("numbers");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  alert("Copied !");
}
$(document).ready(function () {
  $("#fileToUpload").change(function (e) {
    e.preventDefault();
    let file = $(this).val();
    let filename = file.replace(/^.*[\\\/]/, '');
    $(this).next().text(filename);
  })
})
</script>
</html>
<?php
function clean_tag($a,$tag){
	$a=str_replace("`","#%*)",$a);
	$a=str_replace("~","(*%#",$a);
	$a= preg_replace("/<{$tag}[^>]*>/","`",$a);
	$a= preg_replace("/<\/{$tag}>/","~",$a);
	$a=str_replace("`","<{$tag}>",$a);
	$a=str_replace("~","</"."{$tag}>",$a);
	$a=str_replace("#%*)","`",$a);
	$a=str_replace("(*%#","~",$a);
	return $a;
}
 ?>
