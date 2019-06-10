<?php
ob_start();
session_start();

require_once('common_function.php');

//var_dump($_SESSION);

//セッション内にエラー情報のフラグが入っていたら取り出す
$view_data = array();
if(true === isset($_SESSION['output_buffer'])){
    $view_data = $_SESSION['output_buffer'];
}

//var_dump($view_data);
//二重に出力しないようにセッション内の出力情報を削除
unset($_SESSION['output_buffer']);

//CSFRトークンの取得
$csrf_token = create_csrf_token();

?>
<!DICTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ユーザー情報登録フォーム</title>
        <style type="text/css">
            .error{color:red;}
        </style>
    </head>
    <body>
        <?php if( (isset($view_data['error_csrf'])) && (true === $view_data['error_csrf']) ) :?>
        <span class="error">CSRFトークンでエラーが起きました。正しい転移を５分以内操作してください。</span>
        <?php endif ;?>
        <form action="./form_insert_fin.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token) ;?>">
            
            <?php if( (isset($view_data['error_must_name'])) && (true === $view_data['error_must_name']) ) : ?>
            <span class="error">名前が未入力です。<br></span>
            <?php endif ;?>
            名前：<input type="text" name="name" value="<?php echo h(@$view_data['name']); ?>"><br>
            
            <?php if( (isset($view_data['error_must_post'])) && (true === $view_data['error_must_post']) ) : ?>
            <span class="error">郵便番号が未入力です。<br></span>
            <?php endif ;?>
            <?php if( (isset($view_data['error_format_post'])) && (true === $view_data['error_format_post']) ) : ?>
            <span class="error">郵便番号の書式に誤りがあります。<br></span>
            <?php endif ;?>
            郵便番号(例：999-9999)：<input type="text" name="post" value="<?php echo h(@$view_data['post']); ?>"><br>
            
            <?php if( (isset($view_data['error_must_address'])) && (true === $view_data['error_must_address']) ) : ?>
            <span class="error">住所が未入力です。<br></span>
            <?php endif ;?>         
            住所：<input type="text" name="address" value="<?php echo h(@$view_data['address']); ?>"><br>
            
            <?php if( (isset($view_data['error_must_birthday_yy'])) && (true === $view_data['error_must_birthday_yy']) ) : ?>
            <span class="error">誕生日（年）が未入力です。<br></span>
            <?php endif ;?>
            <?php if( (isset($view_data['error_must_birthday_mm'])) && (true === $view_data['error_must_birthday_mm']) ) : ?>
            <span class="error">誕生日（月）が未入力です。<br></span>
            <?php endif ;?>
            <?php if( (isset($view_data['error_must_birthday_dd'])) && (true === $view_data['error_must_birthday_dd']) ) : ?>
            <span class="error">誕生日（日）が未入力です。<br></span>
            <?php endif ;?>
            <?php if( (isset($view_data['error_format_birthday'])) && (true === $view_data['error_format_birthday']) ) : ?>
            <span class="error">誕生日の書式に誤りがあります。<br></span>
            <?php endif ;?>            
            誕生日：西暦
            <input type="text" name="birthday_yy" value="<?php echo h_digit(@$view_data['birthday_yy']); ?>">年
            <input type="text" name="birthday_mm" value="<?php echo h_digit(@$view_data['birthday_mm']); ?>">月
            <input type="text" name="birthday_dd" value="<?php echo h_digit(@$view_data['birthday_dd']); ?>">日
            <br>
            <br>
            <input type="submit" value="データ送信">
        </form>
    </body>
</html>