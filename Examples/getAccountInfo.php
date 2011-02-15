<?php

require '../DropboxConnect.php';

$dp = new DropboxConnect();

echo '<pre>';
print_r($dp->getAccountInfo());
echo '</pre>';
