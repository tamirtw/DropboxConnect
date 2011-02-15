<?php

require '../DropboxConnect.php';

$dp = new DropboxConnect();

header('Content-Type: image/jpeg');
echo $dp->getFile('Photos/Sample%20Album/Pensive%20Parakeet.jpg');

