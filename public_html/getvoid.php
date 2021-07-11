<?php

$id = $_GET['ID'];
$url = $_GET['url'];
echo ' {
  "messages": [
    {
      "attachment": {
        "type": "audio",
        "payload": {
          "url": "'.$url.'"
        }
      }
    }
  ]
}';
?>