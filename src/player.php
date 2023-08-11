<?php
require "html_subr.php";
open_html("Player");
?>
<style>
    body {
        /* background-color: #000000; */
    }
    #player {
        width: 100%;
    }
</style>

<?php
$playlist = array();
$playlist_filename = $_GET['playlist'];
$playlist_name = pathinfo($playlist_filename, PATHINFO_FILENAME);
$playlist_file = fopen("./playlists/{$playlist_filename}", "r"); // プレイリストディレクトリのハードコーディングは気になるところ
if (!$playlist_file) {
    html_exit("プレイリスト {$playlist_filename} を開けませんでした。");
}
while ($audio = trim(fgets($playlist_file))) {
    array_push($playlist, $audio);
}
?>
<br id="playlist_name"><?=$playlist_name?></br>
<form id="playlist_audio_file_list">
<table border="1">
<?php
tbl_line(['', 'プレイリスト']);
foreach ($playlist as $audio) {
    tbl_line([
        '<input type="radio" name="playing" value="'.$audio.'"><br>',
        $audio,
    ]);
}
?>
</table><br>
</form>

<!-- メディアプレイヤー実体 -->
<div id="audio_container">
    <audio id="player" controls src="path_to_audio_file" type="audio/flac" >title</audio>
</div>

<script>
    let playlist = [
<?php
/* プレイリストを出力 */
foreach ($playlist as $audio) {
    echo '"./music/'.$audio.'",'."\n";
}
?>
    ]
    
    let audio_container = document.getElementById("audio_container");
    let audio = document.getElementById('player');

    audio.src = playlist[0];
    // audio.play(); // 最初はユーザの操作によってしか再生できない。
    let index = 0;
    audio.addEventListener('ended', function(){
        index++;
        if (index < playlist.length) {
            audio.src = playlist[index];
            audio.play();
        } else {
            audio.src = playlist[0];
            audio.play();
            index = 0;
        }
    });
</script>

<?php
close_html();
?>