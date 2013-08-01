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

    require "../../config.php";
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_once($CFG->dirroot.'/mod/sharedresource/rpclib.php');
    require_once($CFG->dirroot.'/mod/sharedresource/search_widget.class.php');
    $PAGE->requires->js('/local/sharedresources/js/jquery.js', true);
    $PAGE->requires->js('/local/sharedresources/js/library.js', true);
    $PAGE->requires->js('/local/sharedresources/js/search.js', true);

    // require_once('pagelib.php'); // obsolete
    require_once('lib.php');

    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $blockaction = optional_param('blockaction', '', PARAM_ALPHA);
    $courseid 	 = optional_param('course', '', PARAM_INT); // optional course if we are comming from a course
    $section 	 = optional_param('section', '', PARAM_INT); // optional course section if we are searhcing for feeding a section
    $repo        = optional_param('repo', 'local', PARAM_TEXT);
    $offset      = optional_param('offset', 0, PARAM_INT);
    $action      = optional_param('what', '', PARAM_TEXT);

    if ($courseid){
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    require_capability('mod/sharedresource:browsecatalog', $context);

    //prepare the page.

    $PAGE->set_context($context);
    $PAGE->navbar->add(get_string('sharedresources_library', 'local_sharedresources'));
    $PAGE->set_title(get_string('sharedresources_library', 'local_sharedresources'));
    $PAGE->set_heading(get_string('sharedresources_library', 'local_sharedresources'));

    $PAGE->set_url('/local/sharedresources/index.php',array('edit' => $edit,'blockaction' => $blockaction,'course' => $courseid,'repo' => $repo,'offset' => $offset,'what' => $action));

    echo $OUTPUT->header();

    $page = 20;

    if ($action){
        include 'index.controller.php';
    }

    if ($courseid){
        $course = $DB->get_record('course', array('id'=> $courseid));
    } else {
        $course = null;
    }

    $resourcesmoodlestr = get_string('resources', 'sharedresource');

    if (empty($CFG->pluginchoice)){
        print_error('nometadataplugin', 'sharedresource');
        die;
    }

    resources_browse_print_tabs($repo, $course);
    resources_print_tools($course);
	
    echo "<table width=\"100%\" cellspacing=\"10\" ><tr valign=\"top\"><td id=\"libsearch\" width=\"200\">";

    $visiblewidgets = array();
    resources_setup_widgets($visiblewidgets, $context);
    $searchfields = array();
    if (resources_process_search_widgets($visiblewidgets, $searchfields)){
        // if something has changed in filtering conditions, we might not have same resultset. Keep offset to 0.
        $offset = 0;
    }

    resources_print_search_widgets_tableless($courseid, $repo, $offset, $context, $visiblewidgets, $searchfields);

    echo "</td><td>";

    $isediting = has_capability('mod/sharedresource:editcatalog', $context, $USER->id) && $repo == 'local';

    $fullresults = array();
							
	$metadatafilters = array();
	if (!empty($searchfields)){
		foreach($searchfields as $element => $search){
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
	$SESSION->resourceresult = $resources;
					
	echo '<div id="resources">';
        if ($fullresults['maxobjects'] <= $page){ //si on a assez de ressources pour tout afficher sur une page

            resources_browse_print_list($resources, $course, $section, $isediting, $repo);

        } else {	//sinon, on doit afficher les ressources de la première page donc on exécute la fonction javascript

            $nbrpages = ceil($fullresults['maxobjects']/$page);

            resources_print_pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
            resources_browse_print_list($resources, $course, $section, $isediting, $repo, $page, $offset);
            resources_print_pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
        }
	echo '</div>';

    if ($course){
        $options['id'] = $course->id;
        echo '<center><p>';
        $url = new moodle_url($CFG->wwwroot.'/course/view.php', $options);
        print($OUTPUT->single_button($url, get_string('backtocourse', 'local_sharedresources')));
        echo '</p></center>';
    }

    echo '</td></tr></table>';

    echo $OUTPUT->footer();
