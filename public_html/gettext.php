<?php
$id = $_GET['ID'];
$text = $_GET['text'];

echo ' {
	"messages": [
    {
      "text": "'.$text.'"
    }
  ]
}';
?>