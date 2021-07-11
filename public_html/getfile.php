<?php

$id = $_GET['ID'];
$url = $_GET['url'];
echo ' {
	"messages": [
    {
      "attachment": {
        "type": "file",
        "payload": {
          "url": "'.$url.'"
        }
      }
    }
  ]
}';
?>