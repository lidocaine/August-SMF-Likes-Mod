<?php
if (!defined('SMF'))
  die('Hacking attempt...');
  
loadLanguage('Likes');

function likes_add_action(&$actionArray)
{
  $actionArray['like'] = array('Likes.php', 'Like');
}

?>