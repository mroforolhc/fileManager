<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  setlocale(LC_ALL, 'ru_RU.utf8');

  $rootPath = '/var/www/root';
  $message = [
    'type' => '',
    'text' => '',
  ];

  $dirs = [];

  if (isset($_GET['dirs'])) $dirs = $_GET['dirs'];
  
  $currentPath = '/';
  if (!empty($dirs)) {
    foreach ($dirs as $dir) {
      $currentPath .= $dir . '/';
    }
  }

  $path = $rootPath . $currentPath;
  
  if (isset($_POST['createFolder'])) {
    $folderName = $_POST['createFolder'];

    if (mkdir($path . $folderName)) $message = ['type' => 'success', 'text' => 'Каталог создан!'];
    else $message = ['type' => 'danger', 'text' => 'ОШИБКА! о, нет! Мы без каталога :('];
  }

  if (isset($_POST['downloadFile'])) {
    if (!file_exists($path . $_POST['fileName'])) { 
      $message = ['type' => 'danger', 'text' => 'Нет файла, к сожалению'];
    } else {
      header("Cache-Control: public");
      header("Content-Description: File Transfer");
      header("Content-Disposition: attachment; filename=" . $_POST['fileName']);
      header("Content-Type: application/zip");
      header("Content-Transfer-Encoding: binary");

      readfile($path . $_POST['fileName']);
    }
  }

  if (isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];

    if (move_uploaded_file($fileTmpPath, $rootPath . $currentPath . $fileName)) $message = ['type' => 'success', 'text' => 'Файл успешно загружен.'];
    else $message = ['type' => 'danger', 'text' => 'Произошла ошибка, скорее всего на моей стороне, сорян.'];
  }

  if (isset($_POST['renameFile'])) {
    if (rename($path . $_POST['oldName'], $path . $_POST['newName'])) $message = ['type' => 'success', 'text' => 'Файл переименован.'];
    else $message = ['type' => 'danger', 'text' => 'Кто не ошибается, тот ничего не делает. Произошла ошибка, попробуйте ещё раз переименовать файл.'];
  }

  if (isset($_POST['deleteFile'])) {
    if (unlink($path . $_POST['fileName'])) $message = ['type' => 'success', 'text' => 'Файл уничтожен.'];
    else $message = ['type' => 'danger', 'text' => 'Файл до сих пор существует, повторите попытку.'];
  }

  function humanFilesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$sz[$factor];
  }

  function getListOfFiles($dir) {
    $folders = $files = [];
    $contents = scandir($dir);

    foreach ($contents as $content) {
      if (($content != '.') && ($content != '..') && (!is_link($content))) {
        if (is_dir($dir . $content)) {
          array_push($folders, $content);
        } else {
          array_push($files, $content);
        }
      }
    }

    return array_merge($folders, $files);
  }
?>

<html>
<head>
  <title>File Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<nav class="navbar mb-5 navbar-light" style="background-color: #e3f2fd;">
  <div class="container-fluid">
    <a class="navbar-brand mb-0 h1" href="/">File Manager</a>
    <span class="navbar-text"><?=$currentPath ?></span>
  </div>
</nav>
<div class="container">
  <?php if (!empty($message['text'])): ?>
    <div class="alert alert-<?=$message['type'] ?>" role="alert">
      <?=$message['text'] ?>
    </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-12 col-md-6">
      <h3>Создать папку</h3>
      <form method="post" class="input-group">
        <input name="createFolder" type="text" class="form-control" required>
        <button class="btn btn-outline-secondary" type="submit">Создать</button>
      </form>
    </div>
    <div class="col-12 col-md-6">
      <h3>Загрузить файл</h3>
      <form class="input-group" enctype="multipart/form-data" method="post">
        <input name="file" type="file" class="form-control">
        <button class="btn btn-outline-secondary" type="submit">Загрузить</button>
      </form>
    </div>
  </div>
  <div class="file-manager shadow p-3 bg-white rounded">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th scope="col">Имя файла</th>
          <th scope="col">Тип</th>
          <th scope="col">Размер</th>
          <th scope="col">Действия</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($dirs)): ?>
        <tr>
          <td><a href="<?='?'. http_build_query(array("dirs" => array_slice($dirs, 0, -1))) ?>">../</a></td>
          <td>На уровень выше</td>
          <td></td>
          <td></td>
        </tr>
      <?php endif; ?>

      <?php foreach (getListOfFiles($path) as $file): ?>
        <?php 
          $fileInfo = pathinfo($path . $file); 
          $fileName = (empty($fileInfo['filename'])) ? $fileInfo['basename'] : $fileInfo['filename'] ;
        ?>
        <?php if (is_dir($fileInfo['dirname'] . '/' . $fileInfo['basename'])): ?>
          <?php $link = http_build_query(array("dirs" => array_merge($dirs, [$fileName]))); ?>
          <tr>
            <td><a href="<?='?'. $link ?>"><?=$fileName ?></a></td>
            <td>Папка</td>
            <td></td>
            <td>
              <button 
                type="button" 
                class="btn btn-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#renameModal" 
                data-bs-filename="<?=$fileInfo['basename'] ?>"
              >
                <i class="bi bi-pencil-square"></i>
              </button>
            </td>
          </tr>
        <?php else: ?>
          <tr>
            <td><?=$fileName ?></td>
            <td><?=(empty($fileInfo['extension'])) ? 'Файл' : $fileInfo['extension'] ?></td>
            <td><?=humanFilesize(filesize($rootPath . $currentPath . $fileInfo['basename'])) ?></td>
            <td>
              <div class="d-flex">
                <button 
                  type="button" 
                  class="btn btn-primary me-2" 
                  data-bs-toggle="modal" 
                  data-bs-target="#renameModal" 
                  data-bs-filename="<?=$fileInfo['basename'] ?>"
                >
                  <i class="bi bi-pencil-square"></i>
                </button>
                <form action="" method="post" class="mb-0">
                  <input type="hidden" name="fileName" value="<?=$fileInfo['basename'] ?>">
                  <button 
                    type="submit" 
                    name="downloadFile"
                    class="btn btn-success me-2"
                  >
                    <i class="bi bi-file-earmark-arrow-down"></i>
                  </button>
                </form>
                <form action="" method="post" class="mb-0">
                  <input type="hidden" name="fileName" value="<?=$fileInfo['basename'] ?>">
                  <button 
                    type="submit" 
                    name="deleteFile"
                    class="btn btn-danger"
                  >
                    <i class="bi bi-x-circle"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      <?php endforeach; ?>
       
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="">
      <div class="modal-header">
        <h5 class="modal-title">Переименовать файл</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="old-name">Старое имя: <span class="fw-bold"></span></p>
        <input type="hidden" name="oldName" class="form-control">
        <input type="text" name="newName" class="form-control mb-3">
        <div class="alert alert-warning" role="alert">
          Вводите имя файла вместе с расширением, я не стал обрабатывать старое расширение :(
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="submit" name="renameFile" class="btn btn-primary">Переименовать</button>
      </div>
    </form>
  </div>
</div>
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
  integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
  crossorigin="anonymous">
</script>

<script>
  const modal = document.getElementById('renameModal');
  modal.addEventListener('show.bs.modal', (e) => {
    const button = event.relatedTarget;
    const fileName = button.getAttribute('data-bs-filename');

    const modalOldName = modal.querySelector('.old-name span');
    const modalInputHidden = modal.querySelector('.modal-body input');

    modalOldName.innerHTML = fileName;
    modalInputHidden.value = fileName;
  });
</script>
</body>
</html>