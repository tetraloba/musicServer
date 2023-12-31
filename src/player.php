<?php
require "html_subr.php";
require "./lib/getid3/getid3.php";
$dir_playlists = './playlists';
$dir_music = './music';

open_html("Player");
?>

<?php
if (!isset($_GET['playlist'])) {
    html_exit("プレイリストが選択されていません");
}
$playlist = array();
$playlist_filename = $_GET['playlist'];
$playlist_name = pathinfo($playlist_filename, PATHINFO_FILENAME);
$playlist_file = fopen("{$dir_playlists}/{$playlist_filename}", "r"); // path.join()無いのか…
if (!$playlist_file) {
    html_exit("プレイリスト {$playlist_filename} を開けませんでした。");
}
while ($audio = trim(fgets($playlist_file))) {
    array_push($playlist, $audio);
}
?>
<br id="playlist_name"><?=$playlist_name?></br>
<form id="playlist_audio_file_list">
<table id="audio_table" border="1">
<?php
/* 楽曲リストを出力 */
tbl_line(['', 'title', 'artist', 'album', 'track_number']);
foreach ($playlist as $i => $audio) {
    /* 楽曲のメタデータを取得 */
    $getID3 = new getID3();
    $info = $getID3->analyze($dir_music.'/'.$audio);
    getid3_lib::CopyTagsToComments($info);
    /* 楽曲情報を出力 */
    tbl_line([
        '<input class="audio_selector" type="radio" name="playing" value="'.$i.'"><br>',
        // pathinfo($audio, PATHINFO_BASENAME), // ファイル名
        $info['comments']['title'][0],
        $info['comments']['artist'][0],
        $info['comments']['album'][0],
        $info['comments']['track_number'][0],
    ]);
}
?>
</table><br>
</form>

<!-- メディアプレイヤ実体 -->
<div id="audio_container">
    <audio id="player" src="path_to_audio_file" type="audio/flac" >title</audio><br>
    <div id="seekbar_container">
        <span id="current_time" class="time">00:00</span>
        <input type="range" id="seekbar" max="100" value="0" />
        <span id="duration" class="time">00:00</span>
    </div>
    <div id="audio_info">
        <span>title</span>
        <span>  /  </span>
        <span>artist</span>
    </div>
    <div id="player_buttons">
        <button id="prev">prev</button>
        <button id="stop">stop</button>
        <button id="play_pause">play</button>
        <button id="next">next</button><br>
    </div>
    <div id="volume_container">
        <input type="range" id="volumebar" max="100" value="100" /> <!-- 毎回デフォルト100で良いのかな？ -->
        <span id="volume">100%</span>
    </div>
</div>

<script>
/* プレイリストの再生とループ */
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
audio.addEventListener('ended', () => {
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

/* オーディオテーブル(楽曲一覧) */
audio_file_list = document.getElementById('playlist_audio_file_list');
audio_selectors = audio_file_list.getElementsByClassName('audio_selector');
/* 再生されている曲のラジオボタンをチェックする */
if (audio.readyState) {
    audio_selectors[index].checked = true;
} else {
    audio.addEventListener('loadedmetadata', () => {
        audio_selectors[index].checked = true;
    });
}
/* ラジオボタンが押された時その曲を再生する */
for (audio_selector of audio_selectors) {
    audio_selector.addEventListener('change', (e) => {
        index = e.target.value;
        audio.src = playlist[index];
        audio.play();
    })
}

/* プレイヤの楽曲情報表示 */
const audio_table = document.getElementById('audio_table');
const audio_info = document.getElementById('audio_info');
if (audio.readyState) { // この辺り繰り返しが多いのでイベント側に揃えるのもありかも？
    audio_info.children[0].textContent = audio_table.rows[index + 1].cells[1].innerHTML;
    audio_info.children[2].textContent = audio_table.rows[index + 1].cells[2].innerHTML;
} else {
    audio.addEventListener('loadedmetadata', () => {
        audio_info.children[0].textContent = audio_table.rows[index + 1].cells[1].innerHTML;
        audio_info.children[2].textContent = audio_table.rows[index + 1].cells[2].innerHTML;
    });
}

/* プレイヤの操作 */
/* play pause */
const playPauseButton = document.getElementById('play_pause');
let playing = false;
playPauseButton.addEventListener('click', () => {
    if (playing) {
        audio.pause();
        playPauseButton.textContent = "play";
        playing = false;
    } else {
        audio.play();
        playPauseButton.textContent = "pause";
        playing = true;
    }
});
/* stop */
const stopButton = document.getElementById('stop');
stopButton.addEventListener('click', () => {
    audio.pause();
    audio.currentTime = 0;
    playPauseButton.textContent = "play";
    playing = false;
});
/* prev */
const prevButton = document.getElementById('prev');
prevButton.addEventListener('click', () => {
    if (0 < index) {
        index--;
        audio.src = playlist[index];
    }
    audio.currentTime = 0;
    audio.play();
    playPauseButton.textContent = "pause";
    playing = true;
});
/* next */
const nextButton = document.getElementById('next');
nextButton.addEventListener('click', () => {
    index++;
    if (index < playlist.length) {
        audio.src = playlist[index];
        audio.play();
    } else {
        audio.src = playlist[0];
        audio.play();
        index = 0;
    }
    playPauseButton.textContent = "pause";
    playing = true;
});

/* プレイヤのシークバー等 */
/* seekbar */
const displayBufferedAmount = () => {
    let bufferedAmount = 0;
    if (audio.buffered.length) { // bufferedの要素数が0だと配列外アクセスでエラー吐くので
        /* 1. 常に「一番最後のバッファ」の終了位置 */
        // bufferedAmount = Math.floor(audio.buffered.end(audio.buffered.length - 1));
        /* 2. 常に「一番最初のバッファ」の終了位置 */
        // bufferedAmount = Math.floor(audio.buffered.end(0));
        /* 3. 再生位置以降で最も近い「バッファの終了位置」 */
        for (let i = 0; i < audio.buffered.length; i++) {
            bufferedAmount = Math.floor(audio.buffered.end(i));
            if (audio.currentTime <= bufferedAmount) {
                break;
            }
        }
        /* 4. 「再生位置を含むバッファ」の終了位置 */
        // for (let i = 0; i < audio.buffered.length; i++) {
        //     if (audio.currentTime < audio.buffered.start(i)) {
        //         break;
        //     }
        //     bufferedAmount = Math.floor(audio.buffered.end(i));
        // }
    }
    audio_container.style.setProperty('--buffered-width', `${(bufferedAmount / seekbar.max) * 100}%`);
}
const showRangeProgress = (rangeInput) => {
    if (rangeInput === seekbar) {
        audio_container.style.setProperty('--seek-before-width', rangeInput.value / rangeInput.max * 100 + '%');
    } else {
        audio_container.style.setProperty('--volume-before-width', rangeInput.value / rangeInput.max * 100 + '%');
    }
}
const seekbar = document.getElementById('seekbar');
if (audio.readyState) { // この辺り繰り返しが多いのでイベント側に揃えるのもありかも？
    seekbar.max = audio.duration;
    displayBufferedAmount();
} else {
    audio.addEventListener('loadedmetadata', () => {
        seekbar.max = audio.duration;
        displayBufferedAmount();
    });
}
seekbar.addEventListener('input', () => { // input を change にすると離したときに反映
    audio.currentTime = seekbar.value;
});
audio.addEventListener('timeupdate', () => {
    seekbar.value = audio.currentTime;
});
audio.addEventListener('progress', displayBufferedAmount);
seekbar.addEventListener('input', (e) => {
    showRangeProgress(e.target);
});
/* current_time */
const hhmmssTime = (secs) => {
    const hours = Math.floor(secs / 3600);
    const minutes = Math.floor(secs / 60 % 60);
    const seconds = Math.floor(secs % 60);
    const hh = 0 < hours ? (hours < 10 ? `0${hours}:` : `${hours}:`) : '';
    const mm = minutes < 10 ? `0${minutes}:` : `${minutes}:`;
    const ss = seconds < 10 ? `0${seconds}` : `${seconds}`;
    return `${hh}${mm}${ss}`;
}
const currentTime = document.getElementById('current_time');
seekbar.addEventListener('input', () => {
    currentTime.textContent = hhmmssTime(seekbar.value);
});
audio.addEventListener('timeupdate', () => {
    currentTime.textContent = hhmmssTime(audio.currentTime);
});
/* duration */
const duration = document.getElementById('duration');
if (audio.readyState) {
    duration.textContent = hhmmssTime(audio.duration);
} else {
    audio.addEventListener('loadedmetadata', () => {
        duration.textContent = hhmmssTime(audio.duration);
    });
}
/* volumebar */
const volumebar = document.getElementById('volumebar');
volumebar.addEventListener('input', () => {
    audio.volume = volumebar.value / 100; // 0 ~ 1
});
volumebar.addEventListener('input', (e) => {
    // showRangeProgress(e.target);
});
/* volume */
const volume = document.getElementById('volume');
volumebar.addEventListener('input', () => {
    volume.textContent = `${volumebar.value}%`;
});
</script>

<?php
close_html();
?>