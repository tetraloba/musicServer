<?php
// html ヘッダ出力
function open_html($title){
?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="./css/style.css">
<title><?=$title?></title>
</head>
<body>
<?php
}
// 引数のメッセージを出力し，htmlを閉じ，プログラムを終了する
function html_exit($msg){
    echo $msg,"<br>\n";
    close_html();
    exit(0);
}
// html の終了
function close_html(){
?>
<input type="button" onclick="location.href='./index.php'" value="トップに戻る"><br>
</body>
</html>
<?php
}

// 表の1行を出力
// 入力：出力する行の要素 (配列で複数個)
function tbl_line($itemlist){
  echo "<tr>";
  foreach ($itemlist as $item) {
    echo "<td>{$item}</td>";
  }
  echo "</tr>";
}

?>
