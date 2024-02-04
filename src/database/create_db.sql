CREATE DATABASE music_server CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE music_server;

CREATE TABLE albums (
    name VARCHAR(128),
    artist VARCHAR(64),
    track INTEGER,
    year DATE,
    length TIME,
    type VARCHAR(8), /* EP, Single, Albumなど */
    PRIMARY KEY(name, artist)
);

CREATE TABLE audio_meta (
    title VARCHAR(128),
    artist VARCHAR(64),
    tracknum INTEGER,
    album VARCHAR(128),
    length TIME,
    format VARCHAR(4),
    path VARCHAR(256),
    PRIMARY KEY(path),
    FOREIGN KEY (album) REFERENCES albums(name) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE playlists (
    name VARCHAR(128),
    track INTEGER,
    audio_path VARCHAR(256),
    PRIMARY KEY(name, track),
    FOREIGN KEY (audio_path) REFERENCES audio_meta(path)
);
