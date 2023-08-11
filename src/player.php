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

<div id="audio_container">
    <audio id="player" controls src="path_to_audio_file" type="audio/flac" >title</audio>
</div>

<script>
    let playlist = [
<?php
$playlist = fopen("./playlists/{$_GET['playlist']}", "r");
if (!$playlist) {
    html_exit("プレイリスト {$_GET['playlist']} を開けませんでした。");
}
while ($audio_file = trim(fgets($playlist))) {
    echo '"./music/'.$audio_file.'",'."\n";
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