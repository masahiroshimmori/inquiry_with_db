<?php

//header使うのでバッファリングしておく
ob_start();

require_once ('common_function.php');
require_once ('test_form_data.php');

//パラメータを受け取る
$test_form_id = (string)@$_GET['test_form_id'];

//var_dump($test_form_id);

$datum = get_test_form($test_form_id);
if(true === empty($datum)){
    header('Location: ./admin_data_list.php');
    exit();
}

//var_dump($datum);

?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>登録内容詳細画面</title>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body>

<div class="container">
  <h1>フォーム内容詳細</h1>
  <table class="table table-hover">
  <tr>
    <td>form ID
    <td><?php echo h($datum['test_form_id']); ?>
  <tr>
    <td>名前
    <td><?php echo h($datum['name']); ?>
  <tr>
    <td>郵便番号
    <td><?php echo h($datum['post']); ?>
  <tr>
    <td>住所
    <td><?php echo h($datum['address']); ?>
  <tr>
    <td>誕生日
    <td><?php echo h($datum['birthday']); ?>
  <tr>
    <td>作成日時
    <td><?php echo h($datum['created']); ?>
  <tr>
    <td>修正日時
    <td><?php echo h($datum['updated']); ?>
  </table>
  <a href="admin_data_list.php">戻る</a>
</div>
    

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>