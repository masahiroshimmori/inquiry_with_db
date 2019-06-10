<?php
/*
 * (管理画面想定)１件のform情報の削除
 */
// セッションの開始
ob_start();
session_start();
// 共通関数のinclude
require_once('common_function.php');
// XXX 管理画面であれば、本来はこのあたり(ないしもっと手前)で認証処理を行う
// パラメタを受け取る
// XXX エラーチェックは get_test_form() 関数側でやっているのでここではオミット
$test_form_id = (string)@$_POST['test_form_id'];
// 確認
//var_dump($test_form_id);
// CSRFチェック
if (false === is_csrf_token_admin()) {
    // 「CSRFトークンエラー」であることをセッションに格納しておく
    $_SESSION['output_buffer']["error_csrf"]  = true;
    // 編集ページに遷移する
    header('Location: ./admin_data_list.php');
    exit;
}

//dbハンドルの取得
$dbh = get_dbh();

//削除分の発行
$sql = 'DELETE FROM test_form WHERE test_form_id = :test_form_id;';
$pre = $dbh->prepare($sql);

$pre->bindValue(':test_form_id', $test_form_id, PDO::PARAM_INT);

//sqlの実行
$r = $pre->execute();
if(false === $r){
    echo 'システムでエラーが起きました。';
    exit();
}

unset($_SESSION['output_buffer']);
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>削除完了</title>
        <style type="text/css">
            .error{color: red;}
        </style>
    </head>
    <body>
        削除しました<br>
        <br>
        <a href="./admin_data_list.php">一覧に戻る</a>
    </body>
</html>