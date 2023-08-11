<?php
require("html_subr.php");

// $playlist = "To Kill a living Book -for Library Of Ruina-.m3u";
open_html("muiscServer");

$playlists = glob('./playlists/*');
?>

プレイリストを選択して開くボタンを押してください。<br><br>
<form action="./player.php" method="GET">
<table border="1">
<?php
tbl_line(['', 'プレイリスト']);
foreach ($playlists as $key => $playlist) {
    tbl_line([
        '<input type="radio" name="playlist" value="'.pathinfo($playlist, PATHINFO_BASENAME).'"><br>',
        pathinfo($playlist, PATHINFO_FILENAME),
    ]);
}
?>
</table><br>
選択したプレイリストを開きます。
<input type="submit" name="button" value="play"><br><br>
</form>

<?php
close_html();
?>