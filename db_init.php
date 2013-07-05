<?php

global $smcFunc, $db_prefix, $context, $modSettings;

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


db_extend('packages');
db_extend('extra');

if (empty($context['uninstalling']))
{
  $likes_table_columns = array(
    array(
      'name' => 'id_member',
      'type' => 'int',
      'size' => '11',
      'default' => '0',
      'null' => false
    ),
    array(
      'name' => 'id_msg',
      'type' => 'int',
      'size' => '11',
      'default' => '0',
      'null' => false
    )
  );
  $smcFunc['db_create_table']('{db_prefix}likes', $likes_table_columns, array());
  
  $messages_new_column = array(
    'name' => 'likes',
    'type' => 'int',
    'size' => '11',
    'default' => 0,
    'null' => false
  );
  $smcFunc['db_add_column']('{db_prefix}messages', $messages_new_column);
  
  $call = 'add_integration_function';
} else $call = 'remove_integration_function';

  $hooks = array(
    'integrate_pre_include' => '$sourcedir/Subs-Likes.php',
    'integrate_actions' => 'likes_add_actions',
  );
  
  foreach ($hooks as $hook => $function)
    $call($hook, $function);

?>