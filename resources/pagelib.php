<?php  //$Id: pagelib.php,v 1.4 2010/04/28 21:00:16 vf Exp $

require_once($CFG->libdir.'/pagelib.php');

class page_resources extends page_base {

    function get_type() {
        return PAGE_RESOURCES;
    }

    function user_allowed_editing() {
        page_id_and_class($id, $class);
        if ($id == PAGE_RESOURCES) {
            return true;
        } else if (has_capability('mod/sharedresource:manageblocks', get_context_instance(CONTEXT_SYSTEM)) && defined('ADMIN_STICKYBLOCKS')) {
            return true;
        }
        return false;
    }

    function user_is_editing() {
        global $USER;
        if (has_capability('mod/sharedresource:manageblocks', get_context_instance(CONTEXT_SYSTEM)) && defined('ADMIN_STICKYBLOCKS')) {
            return true;
        }
        return (!empty($USER->editing));
    }

    function print_header($title) {
        global $USER, $CFG;

        $replacements = array(
          '%fullname%' => get_string('resources','sharedresource')
        );
        foreach($replacements as $search => $replace) {
            $title = str_replace($search, $replace, $title);
        }

        $site = get_site();

        $mode = (!empty($USER->isediting)) ? get_string('normal') : get_string('normal') ;
        $button = update_resourcepage_icon();
        $nav = get_string('resources','sharedresource');
        $header = $site->shortname.': '.$nav;
        $navlinks = array(array('name' => $nav, 'link' => '', 'type' => 'misc'));
        $navigation = build_navigation($navlinks);
        
        $loggedinas = user_login_string($site);

        if (empty($CFG->langmenu)) {
            $langmenu = '';
        } else {
            $currlang = current_language();
            $langs = get_list_of_languages();
            $langlabel = get_accesshide(get_string('language'));
            $langmenu = popup_form($CFG->wwwroot .'/resources/index.php?lang=', $langs, 'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
        }

        print_header($title, $header, $navigation,'','',true, $button, $loggedinas.$langmenu);

    }
    
    function url_get_path() {
        global $CFG;
        page_id_and_class($id, $class);
        if ($id == PAGE_RESOURCES) {
            return $CFG->wwwroot.'/resources/index.php';
        } elseif (defined('ADMIN_STICKYBLOCKS')){
            return $CFG->wwwroot.'/'.$CFG->admin.'/stickyblocks.php';
        }
    }

    function url_get_parameters() {
        if (defined('ADMIN_STICKYBLOCKS')) {
            return array('pt' => ADMIN_STICKYBLOCKS);
        } else {
            return array();
        }
    }
       
    function blocks_default_position() {
        return BLOCK_POS_LEFT;
    }

    function blocks_get_positions() {
        return array(BLOCK_POS_LEFT);
    }

    function blocks_move_position(&$instance, $move) {
        if($instance->position == BLOCK_POS_LEFT && $move == BLOCK_MOVE_RIGHT) {
            return BLOCK_POS_RIGHT;
        } else if ($instance->position == BLOCK_POS_RIGHT && $move == BLOCK_MOVE_LEFT) {
            return BLOCK_POS_LEFT;
        }
        return $instance->position;
    }

    function get_format_name() {
        return RESOURCES_FORMAT;
    }
}

define('PAGE_RESOURCES', 'resources-index');
define('PUSHOUT_RESOURCES', 'resources-pushout');
define('RESOURCES_FORMAT', 'resources'); //doing this so we don't run into problems with applicable formats.

page_map_class(PAGE_RESOURCES, 'page_resources');
page_map_class(PUSHOUT_RESOURCES, 'page_resources');

?>