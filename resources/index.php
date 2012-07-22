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
    require_once($CFG->dirroot.'/mod/sharedresource/rpclib.php');
	require_once($CFG->dirroot.'/mod/sharedresource/search_widget.class.php');
    require_once('pagelib.php');
    require_once('lib.php');

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
    $courseid = optional_param('course', '', PARAM_INT);
    $repo     = optional_param('repo', 'local', PARAM_TEXT);
    $offset   = optional_param('offset', 0, PARAM_INT);
    $action   = optional_param('what', '', PARAM_TEXT);

	if ($courseid){
		$context = get_context_instance(CONTEXT_COURSE, $courseid);
    } else {
		$context = get_context_instance(CONTEXT_SYSTEM);
    }
    require_capability('mod/sharedresource:browsecatalog', $context);

    $page = 20;
    
    if ($action){
        include 'index.controller.php';
    }

    $PAGE = page_create_instance($USER->id);

    $pageblocks = blocks_setup($PAGE, BLOCKS_PINNED_BOTH);

    if (($edit != -1) && $PAGE->user_allowed_editing() && isloggedin()) {
        $USER->editing = $edit;
    }
    
    if ($courseid){
        $course = get_record('course', 'id', $courseid);
    } else {
        $course = null;
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
                
                if (empty($CFG->pluginchoice)){
                	print_error('nometadataplugin', 'sharedresource');
					die;
                }
            
                resources_browse_print_tabs($repo, $course);
                resources_print_tools($course);
				
				echo "<table width=\"100%\" cellspacing=\"10\" ><tr valign=\"top\"><td id=\"libsearch\">";
				
				$visiblewidgets = array();
				resources_setup_widgets($visiblewidgets, $context);
				$searchfields = array();
				if (resources_process_search_widgets($visiblewidgets, $searchfields)){
					// if something has changed in filtering conditions, we might not have same resultset. Keep offset to 0.
					$offset = 0;
				}
				resources_print_search_widgets($courseid, $repo, $offset, $context, $visiblewidgets, $searchfields);

				echo "</td></tr><tr><td>";

                $isediting = has_capability('mod/sharedresource:editcatalog', $context, $USER->id) && $repo == 'local';
                
                $fullresults = array();
										
				$metadatafilters = array();
				if (!empty($searchfields)){
					foreach($searchfields as $element => $search){
						if ($search == 'defaultvalue') continue;
						if (!empty($search)){
							$metadatafilters[$element] = $search;
						}
					}
				}
                if ($repo == 'local'){
                    $resources = get_local_resources($repo, $fullresults, $metadatafilters, $offset, $page);
                } else {
                    $resources = get_remote_repo_resources($repo, $fullresults, $metadatafilters, $offset, $page);
                }
				$SESSION -> resourceresult = $resources;
								
				echo '<div id="resources">';
				if ($fullresults['maxobjects'] <= $page){ //si on a assez de ressources pour tout afficher sur une page
					resources_browse_print_list($resources, $course, $isediting, $repo);
				} else {	//sinon, on doit afficher les ressources de la première page donc on exécute la fonction javascript

					$nbrpages = ceil($fullresults['maxobjects']/$page);

					resources_print_pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
					resources_browse_print_list($resources, $course, $isediting, $repo, $page, $offset);
					resources_print_pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
				}
				echo '</div>';

                if ($course){
                    $options['id'] = $course->id;
                    echo '<center><p>';
                    print_single_button($CFG->wwwroot.'/course/view.php', $options, get_string('backtocourse', 'resources', '', $CFG->dirroot.'/resources/lang/'));
                    echo '</p></center>';
                }
                
                echo '</td></tr></table>';

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