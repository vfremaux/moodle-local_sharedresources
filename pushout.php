<?php

/**
* This file provides access to a master shared resources index, intending
* to allow a public browsing of resources.
* The catalog is considered as multi-provider, and can federate all resources into
* browsing results, or provide them as separate catalogs for each resource provider.
*
* The index admits browsing remote linked catalogues, and will aggregate the found
* entries in the current view, after a contextual query has been fired to remote connected
* resource sets.
*
* The index will provide a "top viewed" resources side tray, and a "top used" side tray, 
* that will count local AND remote inttegration of the resource. The remote query to 
* bound catalogs will also get information about local catalog resource used by remote courses. 
*
* The index is public access. Browsing the catalog should although be done through a Guest identity,
* having as a default the mod/sharedresource:browsecatalog capability.
*/

    require "../config.php";
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_once('pagelib.php');
    require_once('lib.php');
    require_once('pushout_form.php');

    $context = get_context_instance(CONTEXT_SYSTEM);
    // require_capability('mod/sharedresource:browsecatalog', $context);

     // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);

    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $blockaction = optional_param('blockaction', '', PARAM_ALPHA);
    $course = optional_param('course', '', PARAM_INT);
    $resourceid = required_param('resourceid', PARAM_INT);
    $repo = optional_param('repo', 'local', PARAM_TEXT);

    $PAGE = page_create_object(PAGE_RESOURCES, $USER->id);

    $pageblocks = blocks_setup($PAGE, BLOCKS_PINNED_BOTH);

    if (($edit != -1) && $PAGE->user_allowed_editing() && isloggedin()) {
        $USER->editing = $edit;
    }

    $resourcesmoodlestr = get_string('resources', 'sharedresource');
    $PAGE->print_header($resourcesmoodlestr);

    echo '<table id="layout-table">';
    echo '<tr valign="top">';

    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':

                $blocks_preferred_width = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), BLOCK_L_MAX_WIDTH);

                if(blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing()) {
                    echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="left-column">';
                    print_container_start();
                    blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
                    print_container_end();
                    echo '</td>';
                }
    
            break;
            case 'middle':
    
                echo '<td valign="top" id="middle-column">';
                print_container_start(true);
            
                // setup the dialog for pushing out
                $form = new PushOut_Form($resourceid);
                
                if ($form->is_cancelled()){
                    redirect($CFG->wwwroot."/resources/index.php?course=$course");
                }
                
                $data = $form->get_data();
                
                if ($data){
                    // do the real thing !!
                    $resourceentry = get_record('sharedresource_entry', 'id', $resourceid);
                    sharedresource_submit($data->provider, $resourceentry);
                    // redirect($CFG->wwwroot."/resources/index.php?course=$course");
                } else {
                    $form->display();
                }
                
                print_container_end();
                echo '</td>';
    
            break;
            case 'right':
                /*
                        
                $blocks_preferred_width = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), BLOCK_R_MAX_WIDTH);
            
                No right column
                
                if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing()) {
                    echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="right-column">';
                    print_container_start();
                    blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
                    print_container_end();
                    echo '</td>';
                }
            break;
                */
            }
        }

    /// Finish the page
    echo '</tr></table>';

    print_footer();

?>