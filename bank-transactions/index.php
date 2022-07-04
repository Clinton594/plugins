<?php

use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

require_once '../master/SimpleXLSX.php';
require_once '../master/SimpleXLSXGen.php';
require_once '../master/functions.php';


$outputs = [];
$allowed_files = ["xlsx"];
if (isset($_POST['submit'])) {
  clearstatcache();

  $target_dir = "uploads/";
  $col = $_POST['colnum'];

  // Cleanup Old Files
  clear_files_longer_than(7, $target_dir);
  // see($_FILES);

  // Upload the file
  $response = uploadFile($_FILES["fileToUpload"], $target_dir, ['xlsx']);
  if (!empty($response->status)) {
    if ($xlsx = SimpleXLSX::parse($response->data)) {
      $rows = $xlsx->rows();
      unset($rows[17]);
      foreach ($rows as $key => $row) {
        $selectedCol = strtolower($row[$col]);
        if (!empty($selectedCol)) {
          if (strpos($selectedCol, " from ") !== false && strpos($selectedCol, " to ") !== false) {
            preg_match('/ from (.*?) to/', $selectedCol, $result);
            if (!empty($result[1])) {
              $rows[$key][$col + 1] = ucwords($result[1]);
            }
          } else if (strpos($selectedCol, "frm ") !== false && strpos($selectedCol, " to ") !== false) {
            preg_match('/frm (.*?) to/', $selectedCol, $result);
            if (!empty($result[1])) {
              $rows[$key][$col + 1] = ucwords($result[1]);
            }
          } else {
            $rows[$key][$col + 1] = ucwords(getBetween($selectedCol));
          }
        }
      }

      // see($rows);
      $xlsx = SimpleXLSXGen::fromArray($rows);
      $downloadable = (str_replace(".xlsx", "-reformatted.xlsx", $response->data));
      $xlsx->saveAs($downloadable);
    } else echo SimpleXLSX::parseError();
  } else die($response->message);
}

function getBetween($string)
{
  $str = "";
  if (stripos($string, "|")) $splitted = explode("|", $string);
  else $splitted = explode("-", $string);
  $found = array_filter($splitted, function ($x) {
    return stripos($x, "ref:") !== false;
  });
  if (count($found)) {
    $str = strstr(reset($found), "ref:", true);
  }
  return $str;
}
?>
<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='UTF-8'>
  <title>XLSX Formatter</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Formatting bank transaction log">

  <!--Bootstrap cdn-->
  <link rel='stylesheet' type='text/css' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
  <style>
    input,
    textarea,
    select {
      height: 100%;
      width: 100%;
    }

    h1 {
      font-weight: bold;
    }

    select,
    .custom-file {
      margin: 0 0 15px 0;
    }

    label {
      margin: 0
    }

    label:not(.custom-file-label) {
      margin: 0 0 0 14px;
      font-size: 14px;
    }
  </style>

</head>

<body>
  <div style='height: 2px; background: white'></div>
  <div class="jumbotron bg-success text-center font-weight-bold text-white">
    <a href="">
      <h1 class="text-white">Transaction Logs</h1>
    </a>
    <small class="text-white">Reformatting Transaction Logs - Efficiency up to 98%</small>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-lg-6 offset-lg-3">
        <?php if (empty($downloadable)) { ?>
          <form action="" method="post" enctype="multipart/form-data" class="<?= $hide ?>">
            <div class="row">
              <div class="col-sm-12 col-lg-12">
                <label for=""> <small>Upload only <?= "'." . implode(", .", $allowed_files) ?> files</small> </label>
                <div class="custom-file">
                  <input placeholder="" type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload" required accept="<?= "." . implode(", .", $allowed_files) ?>">
                  <label class="custom-file-label" for="fileToUpload">Upload an Excel document file</label>
                </div>
              </div>
              <div class="col-sm-12 col-lg-12 mt-3">
                <label for=""> <small>Column number to work on</small> </label>
                <div class="form-group">
                  <select name="colnum" required class="form-control">
                    <option value="" selected disabled>Choose the column number</option>
                    <?php for ($i = 0; $i < 10; $i++) { ?>
                      <option value="<?= $i ?>"><?= $i + 1 ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12">
                <input type="submit" value="Reformat" class="btn btn-success btn-block btn-large ml-0" name="submit">
              </div>
            </div>
          </form>
        <?php } else { ?>
          <div class="row">
            <div class="col-lg-6">
              <a href="<?= $downloadable ?>" download class="btn btn-info">Download file</a>
            </div>
            <div class="col-lg-6">
              <a class="btn btn-warning" href="">Reload page</a>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</body>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $("#fileToUpload").change(function(e) {
      e.preventDefault();
      let file = $(this).val();
      let filename = file.replace(/^.*[\\\/]/, '');
      $(this).next().text(filename);
    })
  })
</script>

</html>