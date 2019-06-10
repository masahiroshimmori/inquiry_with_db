<?php

//一件のform情報の編集完了処理



//セッションの開始
ob_start();
session_start();

//共通関数の読み込み
require_once ('test_form_data.php');

//日付関数を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

//ユーザー入力情報を保持する配列を準備
$user_edit_data = array();


$params = array('name','post','address','birthday');

foreach ($params as $p) {
    $user_edit_data[$p] = (string)@$_POST[$p];
}

//var_dump($user_edit_data);

//test_form_idを取得しておく
$test_form_id = (int)@$_POST['test_form_id'];

//ユーザー入力のvalidate

//基本のエラーチェック
$error_detail = validate_test_form($user_edit_data);

//編集用、追加のエラーチェック
//必須チェックを実装
//空文字なら
if('' === $user_edit_data['birthday']){
    $error_detail['error_must_birthday'] = true;
}


//誕生日
//フォーマットを整える
$t = strtotime($user_edit_data['birthday']);
if(false === $t){
    
    $error_detail['error_format_birthday'] = true;
    
}else{
    $s = date('Y-m-d', $t);
    //年月日に分解
    list($yy, $mm, $dd) = explode('-', $s);
    
    if(false === checkdate($mm, $dd, $yy)){
        $error_detail['error_format_birthday'] = true;
    }
}

//CSRFチェック
if(false === is_csrf_token_admin()){
    //CSRKトークンエラーであることを格納
    $error_detail['error_csrf'] = true;
}

//var_dump($error_detail);

//エラーが出たら入力ページへ転移
if(false === empty($error_detail)){
    //エラー情報をセッションに入れて持ち回る
    $_SESSION['output_buffer'] = $error_detail;
    //入力もセッションに入れて持ち回る
    $_SESSION['output_buffer'] += $user_edit_data;
    
    //編集ページへ転移
    header('Location: ./admin_data_update.php?test_form_id='. rawurlencode($test_form_id));
    exit();
}







//DBハンドルの取得
$dbh = get_dbh();


//UPDATE文の作成と発行

$sql = 'UPDATE test_form SET name=:name, post=:post, address=:address, birthday=:birthday, updated=:updated where test_form_id = :test_form_id;';
$pre = $dbh->prepare($sql);

//値のバインド
$pre->bindValue(':test_form_id', $test_form_id, PDO::PARAM_INT);
$pre->bindValue(':name', $user_edit_data['name'], PDO::PARAM_STR);
$pre->bindValue(':post', format_post($user_edit_data['post']), PDO::PARAM_STR);
$pre->bindValue(':address', $user_edit_data['address'], PDO::PARAM_STR);
$pre->bindValue(':birthday', $user_edit_data['birthday'], PDO::PARAM_STR);
$pre->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

//sqlの実行
$r = $pre->execute();
if(false === $r){
    
    echo 'システムでエラーが起きました。';
    exit();
}

//正常に終了したのでセッション内の出力情報を削除する
unset($_SESSION['output_buffer']);

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>編集完了</title>
        <style type="tex/css">
            .error{color: red;}
        </style>
    </head>
    <body>
        修正が完了しました。<br>
        <br>
        <a href="./admin_data_list.php">戻る</a>
    </body>
</html>