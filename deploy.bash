cd ~/projects/musicServer

# rm -r /mnt/c/xampp/htdocs/musicServer/*
# cp -r ./src/* /mnt/c/xampp/htdocs/musicServer

cp ./src/*.php /mnt/c/xampp/htdocs/musicServer
cp -r ./src/playlists/* /mnt/c/xampp/htdocs/musicServer/playlists/
cp -r ./src/css/* /mnt/c/xampp/htdocs/musicServer/css/
exit;
