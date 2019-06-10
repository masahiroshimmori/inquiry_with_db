<?php

/*
testフォーム用共通関数置き場
  */

 require_once ('common_function.php');
 
 function get_test_form($test_form_id){
     //存在しない場合は空配列を返す
     if('' === $test_form_id){
         return array();
     }
     
     //else
     $dbh = get_dbh();
     
     $sql = 'select * from test_form where test_form_id = :test_form_id;';
     $pre= $dbh->prepare($sql);
     
     $pre->bindValue(':test_form_id', $test_form_id, PDO::PARAM_INT);
     $r = $pre->execute();
     
     if(false === $r){
         echo "システムエラーがおきました。";
         exit();
     }
     //データの取得
     $data = $pre->fetchAll(PDO::FETCH_ASSOC);
     //var_dump($data);
     
     if(true === empty($data)){
         return array();
     }
     //else
     $datum = $data[0];
     //var_dump($datum);
     
     return $datum;
     
 }
 //validate
 //validateが全てokなら空配列、NG項目がある場合はerror_detailに値が入った配列を返す
 
 function validate_test_form($datum){
     $error_detail = array();
     $validate_params = array('name','post','address');
     
     foreach($validate_params as $p){
         //空入力なら
         if('' === $datum[$p]){
             $error_detail["error_must_{$p}"] = true;
         }//endif
     }//endforeach   
           
        // 型チェックを実装
        // 郵便番号
        /*
            \A: 行頭
            [0-9]{3}： [0から9までのいずれかの文字]を３回繰り返す
            [- ]?： [ハイフン、スペースのいずれかの文字]を０回ないし１回繰り返す
            [0-9]{4}： [0から9までのいずれかの文字]を４回繰り返す
            \z: 行末
        */
        if (1 !== preg_match('/\A[0-9]{3}[- ]?[0-9]{4}\z/', $datum['post'])) {
            // 「郵便番号のフォーマットエラー」であることを配列に格納しておく
            $error_detail["error_format_post"] = true;
        }//endif
        
        return $error_detail;
}

//郵便番号を一律のフォーマットに整形
//入ってくる郵便番号はvalidate後を想定
function format_post($post){
    return str_replace(array('-', ' '), array('',''),$post);
}