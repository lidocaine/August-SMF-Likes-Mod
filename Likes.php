<?php
if (!defined('SMF'))
  die('Hacking attempt...');

function Like()
{
  global $smcFunc, $user_info, $board_info, $contect, $modSettings, $txt;
  
  if (empty($board_info) || empty($_REQUEST['msg']) || !is_numeric($_REQUEST['msg']))
    fatal_lang_error('likes_error', false);
    
  $db_msg_request = $smcFunc['db_query']('', '
    SELECT id_member, id_msg
    FROM {db_prefix}messages
    WHERE id_msg = {int:id_msg}
    LIMIT 1',
      array(
        'id_msg' => (int) $_GET['msg'],
      )
  );
  
  // Creat variables and free result
  list($author, $id_message) = $smcFunc['db_fetch_row']($db_msg_request);
  $smcFunc['db_free_result']($db_msg_request);
  
  // You can't like your own shit!
  if ($author == $user_info['id'])
    fatal_lang_error('likes_not_on_yours', false);
  
  // Get likes from database
  $db_likes_request = $smcFunc['db_query']('','
    SELECT id_member
    FROM {db_prefix}likes
    WHERE id_member = {int:id_member}
      AND id_msg = {int:id_msg}
    LIMIT 1',
      array(
        'id_member' => $user_info['id'],
        'id_msg' => $id_message,
      )
  );
  
  // You can't like something again!
  // If you've already liked this post...
  if ($smcFunc['db_num_rows']($db_likes_request) != 0)
  {
    // Remove your like from the likes table
    $smcFunc['db_query']('','
      DELETE FROM {db_prefix}likes
      WHERE id_member = {int:id_member}
        AND id_msg = {int:id_msg}',
      array(
        'id_member' => $user_info['id'],
        'id_msg' => $id_message,
      )
    );
    
    // Decrease the message's like count by 1
    $smcFunc['db_query']('','
      UPDATE {db_prefix}messages
      SET likes = likes - 1
      WHERE id_msg = {int:id_msg}
      LIMIT 1',
      array(
        'id_msg' => $id_message,
      )
    );
    
  // Otherwise...
  } else {
  
  $smcFunc['db_free_result']($db_likes_request);
  
  // Add the like to the likes table
  $smcFunc['db_insert']('',
    '{db_prefix}likes',
    array(
      'id_member' => 'int', 'id_msg' => 'int',
    ),
    array(
      $user_info['id'], $id_message,
    ),
    array('')
  );
  
  // Increase the message's like count by 1
  $smcFunc['db_query']('','
    UPDATE {db_prefix}messages
    SET likes = likes + 1
    WHERE id_msg = {int:id_msg}
    LIMIT 1',
    array(
      'id_msg' => $id_message,
    )
  );
  
  }
 
  cache_put_data('likes_msg_' . $id_message, array(), 1);
  redirectexit('topic=' . $_GET['t'] . '.msg' . $id_message . '#msg' . $id_message);
}

function LikesAdmin()
{
  if (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'settings')
    likes_global_enable();
}

function likes_global_enable($return_config = false)
{
  global $user_info, $txt, $scsripturl, $context, $sourcedir, $modSettings, $db_prefix;
  
  $context[$context['admin_menu_name']]['tab_data']['title'] = $txt['likes_settings'];
  $context[$context['admin_menu_name']]['tab_data']['description'] = $txt['likes_settings_description'];
  
  if(!$user_info[is_admin])
    fatal_lang_error('likes_no_permission', false);
    
  $context['sub_template'] = 'show_settings';
  $context['page_title'] = $txt['likes_settings'];
  
  require_once($sourcedir.'/ManageServer.php');
  
  $config_vars = array(
    array('check', 'likes_show', 'subtext' => $txt['likes_global_enable']),
  );
  
  if (isset($_GET['save']))
  {
    checkSession();
    saveDBSettings($config_vars);
    redirectexit('action=admin;area=likes;sa=settings');
  }
  
  $context['post_url'] = $scripturl . '?action=admin;area=likes;sa=settings;save';
  prepareDBSettingContext($config_vars);
}

?>