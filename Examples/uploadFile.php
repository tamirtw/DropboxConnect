<?php

require '../DropboxConnect.php';

$dp = new DropboxConnect();

if($dp->putFile(basename(__FILE__), __FILE__)) 
    echo "File was uploaded";
else 
    echo "Error: upload failed";