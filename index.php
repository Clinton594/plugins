<?php
require_once("master/functions.php");

$root = $_SERVER["DOCUMENT_ROOT"];
$url = "";
if ($_SERVER["HTTP_HOST"] === "localhost") {
  $root = dirname($_SERVER["SCRIPT_FILENAME"]);
  $url = basename($root) . "/";
}
$files = _readDir($root);
$files = array_values(array_filter($files, function ($x) use ($root) {
  return is_dir($root . "/" . $x) && $x !== "master" && $x !== ".git";
}));

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plugins</title>
  <style>
    body {
      margin: 0;
      background-color: #0a0a64;
    }

    * {
      font-family: system-ui;
      color: white;
    }

    li {
      margin: 5px;
    }

    .container {
      width: 70%;
      max-width: 500px;
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      height: 60vh;
      flex-direction: row;
    }

    .row {
      width: 100%;
      display: flex;
      flex-direction: row;
      justify-content: space-evenly;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row">
      <ul>
        <?php foreach ($files as $key => $project) { ?>
          <li class="link">
            <a href="/<?= $url . $project ?>"><?= ucwords(str_replace("-", " ", $project)) ?></a>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</body>

</html>