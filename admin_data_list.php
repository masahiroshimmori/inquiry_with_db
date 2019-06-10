<?php

ini_set('display_errors', 1);
error_reporting(-1);

/*
 * (管理画面想定)情報の一覧
 */

// セッションの開始
ob_start();
session_start();

// 共通関数のinclude
require_once('common_function.php');

//設定値の設定：configなどに吐き出した方がよりよい場合が多い
$contents_per_page = 3; //1ページあたりの出力数

// XXX 管理画面であれば、本来はこのあたり(ないしもっと手前)で認証処理を行う

//ページの取得
if(false === isset($_GET['p'])){
    $page_num = 1;
}else{
    $page_num = intval($_GET['p']);//もし文字が入ってきたら０になる。
    //１より小さいページ数が指定されたら１に揃える
    if(1 > $page_num){
        $page_num = 1;
    }
}
//確認
//var_dump($page_num);

// ソートパラメタの取得
$sort = (string)@$_GET['sort'];
// デフォルトの設定
if ('' === $sort) {
    $sort = 'test_form_id';
}
// 確認
//var_dump($sort);


//検索パラメータの取得
//ホワイトリストの準備
$search_list = array(
    'search_name',
    'search_birthday_from',
    'search_birthday_to',
    'search_created',
    'search_like_name',
    'search_like_post'
);
//データの取得
$search = array();
foreach($search_list as $p){
    if((true === isset($_POST[$p])) && ('' !== $_POST[$p])){
        $search[$p] = $_POST[$p];
    }
}
/*
 *以下のコードはセキュリティホールを生む可能性が出てくるので基本的に避けるのが好ましい
 * これならホワイトリストをいちいち作らなくても楽だから・・・という理由から発案に至る可能性があるので
 * データ取得(search_というワードがついていたら・・・の文)
 * $search = array();
 * foreach($_POST as $k => $v){
 *  if ((0 === strncmp($k, 'search_', strlen('search_')))&&('' !== $_POST[$k])) {
 *      $search[$k] = $v;
 *  }
 * }
*/
//確認
//var_dump($search);


// DBハンドルの取得
$dbh = get_dbh();

// SELECT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
//countと通常用の２種類のsqlを発行する必要があるのでselectを一旦切り取る
$sql = 'FROM test_form';

//条件がある場合の検索条件の付与
$bind_array = array();
if(false === empty($search)){
    
    $where_list = array();
    
    //値を把握する
    
    if(true === isset($search['search_name'])){
        //where句に入れる文言を設定
        $where_list[] = 'name = :name';
        //bindする値を設定
        $bind_array[':name'] = $search['search_name'];
    }
    
    if(true === isset($search['search_birthday_from'])){
        //where句に入れる文言を設定
        $where_list[] = 'birthday >= :birthday_from';
        //日付を整える
        $search['search_birthday_from'] = date('Y-m-d',strtotime($search['search_birthday_from']));
        //bindする値を設定
        $bind_array[':birthday_from'] = $search['search_birthday_from'];
    }
    
    if(true === isset($search['search_birthday_to'])){
        //where句に入れる文言を設定
        $where_list[] = 'birthday <= :birthday_to';
        //日付を整える
        $search['search_birthday_to'] = date('Y-m-d',strtotime($search['search_birthday_to']));
        //bindoする値を設定
        $bind_array[':birthday_to'] = $search['search_birthday_to'];
    }
    
    if(true === isset($search['search_created'])){
        //where句に入れる文言を設定
        $where_list[] = 'created BETWEEN :created_from AND :created_to';
        //日付を整える
        $search['search_created'] = date('Y-m-d',strtotime($search['search_created']));
        //bindする値を設定
        $bind_array[':created_from'] = $search['search_created'].' 00:00:00';
        $bind_array[':created_to'] = $search['search_created'].' 23:59:59';
    }
    //like句
    if(true === isset($search['search_like_name'])){
        //where句に入れる文言を設定
        $where_list[] = 'name LIKE :like_name';
        //bindする値を設定する
        //$bind_array['like_name'] = $search['search_like_name'].'%';//前方一致の場合
        //$bind_array['like_name'] = '%'. $search['search_like_name'].'%';//部分一致の場合
        $bind_array['like_name'] = '%'. like_escape($search['search_like_name']).'%';//部分一致の場合、%や_はエスケープ
    }
    
    if(true === isset($search['search_like_post'])){
        //where句に入れる文言を設定
        $where_list[] = 'post LIKE :like_post';
        //bindする値を設定する
        //$bind_array['like_post'] = $search['search_like_post'].'%';//前方一致の場合
        //$bind_array['like_post'] = '%'. $search['search_like_post'].'%';//部分一致の場合
        $bind_array['like_post'] = '%'. like_escape($search['search_like_post']).'%';//部分一致の場合、%や_はエスケープ
    }
    
    //where句を合成してsqlへつなげる
    $sql = $sql.' WHERE '. implode(' AND ', $where_list);
    
    //sort条件は現在指定の値を持ち越し。何かデフォルトでリセットしたいような場合はここで$sort関数に適切な値を代入する
}

// ソート条件の付与
// (第一種)ホワイトリストによるチェック
$sql_sort = '';
$sort_list = array (
    'test_form_id' => 'test_form_id',
    'test_form_id_desc' => 'test_form_id DESC',
    'name' => 'name',
    'name_desc' => 'name DESC',
    'created' => 'created',
    'created_desc' => 'created DESC',
    'updated' => 'updated',
    'updated_desc' => 'updated DESC',
);
if (true === isset($sort_list[$sort])) {
    $sql_sort = ' ORDER BY ' . $sort_list[$sort];
    }else{
    //いつまでも無駄な条件を持っていても意味がないので消しておく
    $sort ='';
}

//検索がない場合はページング処理
//count側には付与しないので別変数に文字列を貯めておく
$sql_limit_string = '';
if(true === empty($search)){
    
    $sql_limit_string = ' LIMIT :start_page, :contents_per_page';
    $bind_array[':start_page'] = ($page_num -1) * $contents_per_page;//[ページ数 - 1] * 1pageあたりの出力数
    $bind_array['contents_per_page'] = $contents_per_page;
}

//count用と通常用の２つのsqlを作成し、sqlを閉じる
$sql_count = 'SELECT count(test_form_id) ' . $sql . ';';
$sql_main = 'SELECT * ' . $sql . $sql_sort . $sql_limit_string . ';';
//確認
//var_dump($sql_count);
//var_dump($sql_main);

//プリペアドステートメントを作成する
$pre_count = $dbh->prepare($sql_count);
$pre_main = $dbh->prepare($sql_main);

// 値のバインド
if(false === empty($bind_array)){
    foreach($bind_array as $k => $v){
        $pre_count->bindValue($k, $v);//デフォルトのstrとしておく：数値が入る可能性が出てきたらis_int関数を実装
        $pre_main->bindValue($k, $v);//デフォルトのstrとしておく：数値が入る可能性が出てきたらis_int関数を実装
    }
}
//値の確認
//var_dump($bind_array);

//count側のsql実行
$r = $pre_count->execute();
if (false === $r) {
        // XXX 本当はもう少し丁寧なエラーページを出力する
        echo 'システムでエラーが起きました';
        exit;
}
// データを取得
$data = $pre_count->fetch();
//確認
//var_dump($data);
//var_dump($data[0]);
//全件数取得
$total_contents_num = $data[0];
//var_dump($total_contents_num);

//最大ページ数を把握
//ceil(全体n件÷１ページあたりm件)
$max_page_num = (int) ceil($total_contents_num / $contents_per_page);
//var_dump($max_page_num);

//指定されたpageが最大ページを超える場合は最大ページとする
if($page_num > $max_page_num){
    $page_num = $max_page_num;
    //値をバインドし直す
    $pre_count->bindValue(':start_page', ($page_num -1) * $contents_per_page);
}
//var_dump($page_num);
//
//main側のsql実行
$r = $pre_main->execute();
if (false === $r) {
        // XXX 本当はもう少し丁寧なエラーページを出力する
        echo 'システムでエラーが起きました';
        exit;
}

//データをまとめて取得
$data = $pre_main->fetchAll(PDO::FETCH_ASSOC);
//var_dump($data);

// $_SESSION['output_buffer']にデータがある場合は、情報を取得する
if (true === isset($_SESSION['output_buffer'])) {
    $output_buffer = $_SESSION['output_buffer'];
} else {
    $output_buffer = array();
}
//var_dump($output_buffer);

// (二重に出力しないように)セッション内の「出力用情報」を削除する
unset($_SESSION['output_buffer']);

// CSRFトークンの取得
$csrf_token = create_csrf_token_admin();

// sortのAエレメント出力用関数
function a_tag_print($type, $out) {
    if ($type === $GLOBALS['sort']) {
        echo "<a class='bg-danger text-danger' href='./admin_data_list.php?sort={$type}'>{$out}</a>";
    } else {
        echo "<a class='text-muted' href='./admin_data_list.php?sort={$type}'>{$out}</a>";
    }
}

//URLパラメータを作成する共通関数
//sort条件は同一なのでglobal変数領域から、page数は状況によって異なるので引数から取得
function get_url_params($page_num){
    $params = array();
    $params['sort'] = $GLOBALS['sort'];
    $params['p'] = $page_num;
    
    return http_build_query($params);
}

?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>管理画面:登録者一覧</title>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body>

<div class="container">
  <h1>フォーム内容一覧</h1>

<?php if ( (isset($output_buffer['error_csrf']))&&(true === $output_buffer['error_csrf']) ) : ?>
    <span class="text-danger">CSRFトークンでエラーが起きました。正しい遷移を、５分以内に操作してください。<br></span>
<?php endif; ?>

  <div class="row">
      <form action="./admin_data_list.php" method="post">
    <div>
      <span class="col-md-6">検索する「名前（完全一致検索）」<input name="search_name" value="<?php echo h(@$search['search_name']); ?>"></span>
      <span class="col-md-6">検索する「誕生日(YYYY-MM-DD)」<input name="search_birthday_from" value="<?php echo h(@$search['search_birthday_from']); ?>">～<input name="search_birthday_to" value="<?php echo h(@$search['search_birthday_to']); ?>"></span>
    </div>
    <div>
      <span class="col-md-12">検索する「入力日(YYYY-MM-DD)」<input name="search_created" value="<?php echo h(@$search['search_created']); ?>"></span>
    </div>
    <div>
        <span class="col-md-6">検索する「名前（部分一致検索）」<input type="text" name="search_like_name" value="<?php echo h(@$search['search_like_name']); ?>"></span>
        <span class="col-md-6">検索する「郵便番号（部分一致検索）」<input type="text" name="search_like_post" value="<?php echo h(@$search['search_like_post']); ?>"></span>
    </div>
    <span class="col-md-12"><button class="btn btn-default">検索する</button></span>
  </form>
  </div>
    <?php if (false === empty($search)) : ?>
        現在、以下の項目で検索をかけています。<br>
        <?php
            foreach($search as $k => $v) {
                echo h($k), ': ', h($v), "<br>\n";
            }
        ?>
        <br>
        <a class="btn btn-default" href="./admin_data_list.php">検索項目をクリアする</a>
    <?php endif;?>
        
  <h2>一覧</h2>
  <table class="table table-hover">
  <tr>
    <th>フォームID
    <th>名前
    <th>郵便番号</th>
    <th>誕生日</th>
    <th>入力日</th>
    <th>修正日</th>
    <th></th>
    <th></th>
    <th></th>
  </tr>
  <tr>
    <td><?php a_tag_print('test_form_id', '▲'); ?>　<?php a_tag_print('test_form_id_desc', '▼'); ?></td>
    <td><?php a_tag_print('name', '▲'); ?>　<?php a_tag_print('name_desc', '▼'); ?></td>
    <td></td>
    <td></td>
    <td><?php a_tag_print('created', '▲'); ?>　<?php a_tag_print('created_desc', '▼'); ?></td>
    <td><?php a_tag_print('updated', '▲'); ?>　<?php a_tag_print('updated_desc', '▼'); ?></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <?php foreach($data as $datum): ?>
  <tr>
    <td><?php echo h($datum['test_form_id']); ?></td>
    <td><?php echo h($datum['name']); ?></td>
    <td><?php echo h($datum['post']); ?></td>
    <td><?php echo h($datum['birthday']); ?></td>
    <td><?php echo h($datum['created']); ?></td>
    <td><?php echo h($datum['updated']); ?></td>
    <td><a class="btn btn-default" href="./admin_data_detail.php?test_form_id=<?php echo rawurlencode($datum['test_form_id']); ?>">詳細へ</a></td>
    <td><a class="btn btn-default" href="./admin_data_update.php?test_form_id=<?php echo rawurlencode($datum['test_form_id']); ?>">修正へ</a></td>
    <td><form action="./admin_data_delete.php" method="post">
            <input type="hidden" name="test_form_id" value="<?php echo h($datum['test_form_id']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
            <button class="btn btn-danger" onClick="return confirm('本当に削除しますか？');">削除する</button>
        </form></td>
  </tr>
  <?php endforeach; ?>
  </table>
 全<?php echo $total_contents_num; ?>件
    <div class="row">
        <?php if (1 !== $page_num): ?>
        <a class="btn btn-default" href="./admin_data_list.php?<?php echo get_url_params($page_num - 1); ?>">＜＜前</a>
        <?php endif; ?>

        <?php if ($max_page_num > $page_num): ?>
        <a class="btn btn-default" href="./admin_data_list.php?<?php echo get_url_params($page_num + 1); ?>">次＞＞</a>
        <?php endif; ?>
      </div>

      <div class="row">
        <ul class="pagination">
        <?php for($i = 1; $i <= $max_page_num; ++$i): ?>
            <?php if($i === $page_num): ?>
                <li class="active"><a href="#" ><?php echo $i; ?></a></li>
            <?php else: ?>
                <li><a href="./admin_data_list.php?<?php echo get_url_params($i); ?>"><?php echo $i; ?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        </ul>
      </div>  
</div>


<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>