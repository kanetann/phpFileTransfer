<?php
// Change to match your server if necessary.
error_reporting(E_ALL);
const UPLOADS_DIRECTORY = "../../uploads/"; // RECOMMEND: Please specify outside DocumentRoot.
const UPLOADS_DIRECTORY_PERMISSION = 0700;
const BASIC_AUTH_ENABLED = false;
const BASIC_AUTH_USER = "admin";
const BASIC_AUTH_PASS = "<YOUR PASSWORD>";

function h($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// PHP: get_browser - Manual https://www.php.net/manual/ja/function.get-browser.php#123473
function valid_user_agent() {
  return preg_match('/opera|opr|edge|chrome|safari|firefox|msie|trident/i', $_SERVER['HTTP_USER_AGENT']);
}

function generate_upload_directory() {
  if (is_dir(UPLOADS_DIRECTORY) === false) mkdir(UPLOADS_DIRECTORY, UPLOADS_DIRECTORY_PERMISSION);
}

function basic_authentication() {
  if (BASIC_AUTH_ENABLED === false) return;
  switch (true) {
    case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
    case $_SERVER['PHP_AUTH_USER'] !== BASIC_AUTH_USER:
    case $_SERVER['PHP_AUTH_PW']   !== BASIC_AUTH_PASS:
      header('WWW-Authenticate: Basic realm="Enter username and password."');
      header('Content-Type: text/plain; charset=utf-8');
      die('Login Error');
  }
}

function file_upload_process(){
  $uniqid = uniqid('', true);
  $file = basename($_FILES['userfile']['name']);
  mkdir(sprintf("%s/%s", UPLOADS_DIRECTORY, $uniqid), UPLOADS_DIRECTORY_PERMISSION, true);
  if (move_uploaded_file($_FILES['userfile']['tmp_name'], sprintf("%s/%s/%s", UPLOADS_DIRECTORY, $uniqid, $file))) {
    return sprintf(
      "%s%s%s?uniqid=%s&file=%s",
      empty($_SERVER['HTTPS']) ? 'http://' : 'https://',
      $_SERVER['HTTP_HOST'],
      $_SERVER['REQUEST_URI'],
      $uniqid,
      $file,
    );
  } else {
    // PHP: Error Messages Explained - Manual https://www.php.net/manual/en/features.file-upload.errors.php
    $phpFileUploadErrors = array(
      0 => 'There is no error, the file uploaded with success',
      1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
      2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
      3 => 'The uploaded file was only partially uploaded',
      4 => 'No file was uploaded',
      6 => 'Missing a temporary folder',
      7 => 'Failed to write file to disk.',
      8 => 'A PHP extension stopped the file upload.',
    );
    return $phpFileUploadErrors[$_FILES['userfile']['error']];
  }
}

function file_download_process(){
  $path = sprintf("%s/%s/%s", UPLOADS_DIRECTORY, basename($_GET['uniqid']), basename($_GET['file']));
  if (file_exists($path) && valid_user_agent()) {
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($_GET['file']));
    readfile($path);
    unlink($path);
    exit;
  }
  else {
    header(sprintf(
      "Location: %s%s%s",
      empty($_SERVER['HTTPS']) ? 'http://' : 'https://',
      $_SERVER['HTTP_HOST'],
      strstr($_SERVER['REQUEST_URI'], '?', true)
    ));
    exit;
  }
}

function get_max_file_size(){
  return ini_get('upload_max_filesize') === '2M' ? 2097152 : ini_get('upload_max_filesize');
}

// main
// ------------------------------------------------------------
$response = '';
basic_authentication();
generate_upload_directory();
if ($_FILES) {
  $response = file_upload_process();
}
if (isset($_GET['uniqid']) && isset($_GET['file'])) {
  file_download_process();
}
?>
<!doctype html>
<html lang="en" class="h-100">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
  <title>phpFileTransfer</title>
</head>

<body class="d-flex h-100 text-center">

  <div class="container d-flex w-100 h-100 mx-auto flex-column">
    <header class="mb-auto">
      <h1 class="display-1 float-md-start">phpFileTransfer</h1>
    </header>

    <main>
      <form enctype="multipart/form-data" action="index.php" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= get_max_file_size() ?>" />
        <input name="userfile" type="file" class="form-control" />
        <input type="submit" value="Upload" class="btn btn-primary" />
        <div>
          <label for="response" class="form-label">Download URL</label>
          <input type="text" name="response" id="response" value="<?= h($response) ?>" class="form-control">
        </div>
      </form>
    </main>

    <div class="mt-auto">
      <p></p>
    </div>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>

</html>
