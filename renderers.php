<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints the "like" stars
 */
function resources_print_stars($stars, $maxstars) {
    global $OUTPUT;

    $str = '';

    for ($i = 0 ; $i < $maxstars ; $i++) {
        $icon = ($i < $stars) ? 'star' : 'star_shadow';
        $str .= '<img src="'.$OUTPUT->pix_url($icon, 'local_sharedresources').'" />';
    }

    return $str;
}

/**
 * print widgets calling the adequate widget class instance
 * @param int $courseid
 * @param int $repo
 * @param int $offset the record count offset of the current page
 * @param object $context the current course or site context
 * @param array ref $visiblewidgets an array of widgets to print
 */
function resources_print_search_widgets_tableless($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {
    global $CFG, $OUTPUT;

    if (empty($visiblewidgets)) {
        echo $OUTPUT->box_start('block');
        echo $OUTPUT->box_start('content');
        echo '<br/><center>'.get_string('nowidget', 'sharedresource').'</center><br/>';
        echo $OUTPUT->box_end();
        echo $OUTPUT->box_end();
    } else {
        echo "<form name=\"cat\" action=\"{$CFG->wwwroot}/local/sharedresources/index.php\"style=\"display:inline\">";
        if ($courseid) {
            echo "<input type=\"hidden\" name=\"course\" value=\"{$courseid}\">";
        }
        echo "<input type=\"hidden\" name=\"repo\" value=\"{$repo}\">";
        echo "<input type=\"hidden\" name=\"offset\" value=\"{$offset}\">";
        echo "<fieldset>";
        $searchstr = get_string('searchinlibrary', 'sharedresource');
        echo "<legend>$searchstr</legend>";
        $n = 0;
        foreach ($visiblewidgets as $key => $widget) {
            echo $OUTPUT->box_start('block', 'widget-'.$key);
            echo $widget->print_search_widget('column', @$searchvalues[$widget->id]);
            echo $OUTPUT->box_end();
            $n++;
        }

        echo $OUTPUT->box_start('block', 'widget-'.$key);
        echo '<div id="sharedresource-search-button content"><center>';
        $search = get_string('search');
        echo "<input type=\"submit\" name=\"go\" value=\"$search\" />";
        echo "</center></div>";
        echo $OUTPUT->box_end();

        echo "</fieldset>";
        echo "</form>";
        echo '</div>';
    }
}

/**
 * print widgets calling the adequate widget class instance
 * @param int $courseid
 * @param int $repo
 * @param int $offset the record count offset of the current page
 * @param object $context the current course or site context
 * @param array ref $visiblewidgets an array of widgets to print
 */
function resources_print_search_widgets($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {
    global $CFG;

    if (empty($visiblewidgets)) {
        echo '<br/><center>'.get_string('nowidget', 'sharedresource').'</center><br/>';
    } else {
        echo "<form name=\"cat\" action=\"{$CFG->wwwroot}/local/sharedresources/index.php\"style=\"display:inline\">";
        if ($courseid) {
            echo "<input type=\"hidden\" name=\"course\" value=\"{$courseid}\">";
        }
        echo "<input type=\"hidden\" name=\"repo\" value=\"{$repo}\">";
        echo "<input type=\"hidden\" name=\"offset\" value=\"{$offset}\">";
        echo "<fieldset>";
        $searchstr = get_string('searchinlibrary', 'sharedresource');
        echo "<legend>$searchstr</legend>";
        echo '<table>';
        echo '<tr>';
        $n = 0;
        foreach ($visiblewidgets as $key => $widget) {
            echo '<td>';
            echo $widget->print_search_widget('column', @$searchvalues[$widget->id]);
            echo '</td>';
            $n++;
        }
        echo "</tr><tr><td colspan=\"{$n}\" align=\"center\">";
        $search = get_string('search');
        echo "<input type=\"submit\" name=\"go\" value=\"$search\" />";
        echo "</td></tr>";
        echo "</table>";
        echo "</fieldset>";
        echo "</form>";
    }
}

/**
 * prints a pager for resource pages
 * @param int $courseid the course context id. I null the library is browsed from non course area
 * @param int $repo the repository ID
 * @param int $nbrpages
 */
function resources_print_pager($courseid, $repo, $nbrpages, $page, $offset = 0, $isediting = false) {
    echo '<center><b>';
    if ($courseid) {
        for ($i = 1 ; $i <= $nbrpages ; $i++) {
            $pageoffset = ($i - 1)*$page;
            $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
            echo "<a style=\"{$pagestyle}\" name=\"page{$i}\" href=\"index.php?course={$courseid}&amp;repo={$repo}&amp;offset={$pageoffset}&amp;isediting={$isediting}\">$i</a>";
        }
    } else {
        for ($i = 1 ; $i <= $nbrpages ; $i++) {
            $pageoffset = ($i - 1)*$page;
            $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
            echo "<a style=\"{$pagestyle}\" name=\"page{$i}\" href=\"index.php?repo={$repo}&amp;offset={$pageoffset}&amp;isediting={$isediting}\">$i</a>";
        }
    }
    echo '</center>';
}

/**
 * print list of the selected resources
 */
function resources_browse_print_list(&$resources, &$course, $section, $isediting = false, $repo = 'local') {
    global $CFG, $USER, $OUTPUT, $DB;

    $isremote = ($repo != 'local');
    $consumers = get_consumers();
    
    $courseid = (empty($course->id)) ? '' : $course->id;

    if ($resources) {
        $i = 0;
        foreach ($resources as $resource) {
            
            if (!$isremote) {
                // get local once
                $resource->uses = sharedresource_get_usages($resource, $response, null);
                if (!empty($consumers)) {
                    // This cascading chain raises too many perforMance issues
                    // $resource->uses += sharedresource_get_usages($resource, $response, $consumers);
                }
                $reswwwroot = $CFG->wwwroot;
            } else {
                $resource_host = $DB->get_record('mnet_host',array('id' => $repo));
                $reswwwroot = $resource_host->wwwroot;
            }

            $commands = '';
            if ($isediting) {
                $editstr = get_string('update');
                $deletestr = get_string('delete');
                $exportstr = get_string('export', 'sharedresource');
                $forcedeletestr = get_string('forcedelete','local_sharedresources');
                $commands = "<a href=\"{$CFG->wwwroot}/mod/sharedresource/edit.php?course=1&type=file&add=sharedresource&return=0&mode=update&entry_id={$resource->id}\" title=\"$editstr\"><img src=\" ".$OUTPUT->pix_url('t/edit')."\" /></a>";
                if ($resource->uses == 0) {
                    $commands .= " <a href=\"index.php?what=delete&amp;course=$courseid&amp;id={$resource->id}\" title=\"$deletestr\"><img src=\"".$OUTPUT->pix_url('delete', 'sharedresource')."\" /></a>";
                } else {
                    $commands .= " <a href=\"index.php?what=forcedelete&amp;course=$courseid&amp;id={$resource->id}\" title=\"$forcedeletestr\"><img src=\"".$OUTPUT->pix_url('t/delete').
                    "\" /></a>";
                }
                $commands .= " <a href=\"pushout.php?course={$courseid}&amp;resourceid={$resource->id}\" title=\"$exportstr\"><img src=\"".$OUTPUT->pix_url('export', 'sharedresource')."\" /></a>";
            }

            $icon = ($isremote) ? 'remoteicon' : 'icon';
            echo "<div class='resourceitem'>"; //Resource item.
            echo "<h3><img src=\"".$OUTPUT->pix_url($icon)."\"/> <span class=\"title\">{$resource->title}</span> $commands</h3>";
            echo $OUTPUT->box_start('generalbox');
            echo "<a class=\"smalllink\" href=\"{$resource->url}\" target=\"_blank\">{$resource->url}</a><br/>";

            // Print notice access.
            $readnotice = get_string('readnotice', 'sharedresource');
            $url = "{$reswwwroot}/mod/sharedresource/metadatanotice.php?identifier={$resource->identifier}";
            $popupaction = new popup_action('click', $url, 'popup', array('width' => 800, 'height' => 600));
            echo $OUTPUT->action_link($url, $readnotice, $popupaction);
            echo '<br/>';
            if (!empty($resource->description)) {
                echo "<div class=\"resource-description\">$resource->description</div><br/>";
            }
            echo '<span class="smalltext">'.get_string('keywords', 'sharedresource'). ": $resource->keywords</span><br/>";
            echo get_string('used', 'local_sharedresources', $resource->uses).'</br>';
            echo get_string('viewed', 'local_sharedresources', $resource->scoreview).'<br/>';
            $spanid = 'sharedresource-liked-'.$resource->identifier;
            echo get_string('liked', 'local_sharedresources', '<span id="'.$spanid.'">'.resources_print_stars($resource->scorelike, 15).'</span>').'</p>';

            $markliked = get_string('markliked', 'local_sharedresources');

            if (!empty($course) && ($course->id > SITEID)) {

                $context = context_course::instance($course->id);

                if (has_capability('moodle/course:manageactivities', $context)) {

                    $isLTITool = sharedresource_is_lti($resource);
                    $isMoodleActivity = sharedresource_is_moodle_activity($resource);

                    $addtocourse = get_string('addtocourse', 'sharedresource');
                    $localizetocourse = get_string('localizetocourse', 'sharedresource');
                    $addfiletocourse = get_string('addfiletocourse', 'sharedresource');
                    if (!$isremote) {
                        // If is local or already proxied.
                        echo "<form name=\"add{$i}\" action=\"{$CFG->wwwroot}/mod/sharedresource/addlocaltocourse.php\" style=\"display:inline\">";
                    } else {
                        // If is a true remote.
                        echo "<form name=\"add{$i}\" action=\"{$CFG->wwwroot}/mod/sharedresource/addremotetocourse.php\" style=\"display:inline\" method=\"POST\" >";
                    }
                    echo "<input type=\"hidden\" name=\"id\" value=\"{$course->id}\" />";
                    echo "<input type=\"hidden\" name=\"mode\" value=\"shared\" />";
                    echo "<input type=\"hidden\" name=\"section\" value=\"{$section}\" />";
                    echo "<input type=\"hidden\" name=\"identifier\" value=\"{$resource->identifier}\" />";
                    $desc = htmlentities($resource->description, ENT_QUOTES, 'UTF-8');
                    echo "<input type=\"hidden\" name=\"description\" value=\"$desc\" />";
                    $title = $resource->title;
                    echo "<input type=\"hidden\" name=\"title\" value=\"$title\" />";
                    echo "<input type=\"hidden\" name=\"provider\" value=\"$repo\" />";
                    echo "<input type=\"hidden\" name=\"file\" value=\"{$resource->file}\" />";
                    echo "<input type=\"hidden\" name=\"url\" value=\"{$resource->url}\" />";
                    echo "</form>";

                    echo '<div style="text-align:right" class="commands">';
                    echo "<a href=\"javascript:ajax_mark_liked('{$CFG->wwwroot}', '{$repo}', '{$resource->identifier}')\">{$markliked}</a>";
                    if (!$isLTITool) {
                        echo " - <a href=\"javascript:document.forms['add{$i}'].submit();\">{$addtocourse}</a>";
                        if (!$isMoodleActivity) {
                            if (!empty($resource->file) || ($isremote && empty($resource->isurlproxy))) {
                                echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'local';document.forms['add{$i}'].submit();\">{$localizetocourse}</a>";
                                echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'file';document.forms['add{$i}'].submit();\">{$addfiletocourse}</a>";
                            }
                        }
                    }

                    if ($isMoodleActivity) {
                    // check deployable moodle activity
                        if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
                            include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');
                            $deployincourse = get_string('deployincourse', 'block_activity_publisher');
                            echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'deploy';document.forms['add{$i}'].submit();\">{$deployincourse}</a>";
                        }
                    }

                    // Check deployable LTI
                    if ($isLTITool) {
                        $installtool = get_string('installltitool', 'local_sharedresources');
                        echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'ltiinstall';document.forms['add{$i}'].submit();\">{$installtool}</a>";
                    }
    
                    echo "</div>";
                }
            } else {
                echo '<div style="text-align:right" class="commands">';
                echo "<a href=\"javascript:ajax_mark_liked('{$CFG->wwwroot}', '{$repo}', '{$resource->identifier}')\">{$markliked}</a>";
                echo "</div>";
            }
            echo "</div>";//resource item
            echo $OUTPUT->box_end();
            $i++;
        }
    } else {
        echo get_string('noresources', 'local_sharedresources');
    }
}

/**
 *
 */
function resources_print_tools($course) {
    global $CFG;
    
    if ($course->id == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($course->id);
    }

    $toollinks = array(); 

    echo '<center>';
    if ($course->id > SITEID) {
        $convertstr = get_string('resourceconversion', 'sharedresource');
        $toollinks[] = "<a href=\"{$CFG->wwwroot}/mod/sharedresource/admin_convertall.php?course={$course->id}\">$convertstr</a>";
    }

    if (has_capability('repository/sharedresources:manage', $context)) {
        $newresourcestr = get_string('newresource', 'local_sharedresources');
        $toollinks[] = "<a href=\"{$CFG->wwwroot}/mod/sharedresource/edit.php?course={$course->id}&amp;type=file&amp;add=sharedresource&amp;return=1&amp;mode=add\">$newresourcestr</a>";

        $massimportstr = get_string('massimport', 'local_sharedresources');
        $toollinks[] = "<a href=\"{$CFG->wwwroot}/local/sharedresources/admin/admin_mass_import.php?course={$course->id}\">$massimportstr</a>";
    }

    echo implode("&nbsp;-&nbsp;", $toollinks);
    echo '</center>';
}

/**
 * print tabs allowing selection of the current repository provider
 * note that provider is necessarily a mnet host identity.
 */
function resources_browse_print_tabs($repo, $course) {
    global $CFG;
    
    $repos['local'] = get_string('local', 'sharedresource');
    
    if ($providers = get_providers()) {

        foreach ($providers as $provider) {
            $repos["$provider->id"] = $provider->name;
        }
    }

    $repoids = array_keys($repos);
    if (!in_array($repo, $repoids)) $repo = $repoids[0];

    foreach ($repoids as $repoid) {
        if ($course) {
            $rows[0][] = new tabobject($repoid, $CFG->wwwroot."/local/sharedresources/index.php?course={$course->id}&amp;repo=$repoid", $repos[$repoid]);
        } else {
            $rows[0][] = new tabobject($repoid, $CFG->wwwroot."/local/sharedresources/index.php?repo=$repoid", $repos[$repoid]);
        }
    }
    
    print_tabs($rows, $repo);
}

/**
 * Print resource repository tabs
 * @param string $repo the current repo
 * @param int $course the course context
 */
function resources_search_print_tabs($repo, $course) {
    global $CFG;
    
    $repos = get_list_of_plugins('resources/plugins');

    if (!in_array($repo, $repos)) {
        $repo = $repos[0];
    }

    foreach ($repos as $arepo) {
        $rows[0][] = new tabobject($arepo, $CFG->wwwroot."/local/sharedresources/search.php?id={$course->id}&amp;repo=$arepo", get_string('reponame', $arepo, '', $CFG->dirroot."/resources/plugins/{$arepo}/lang/"));
    }

    print_tabs($rows, $repo);
}

function resources_print_top_keywords($courseid) {
    global $OUTPUT, $CFG;

    if ($topkws = sharedresource_get_top_keywords($courseid)) {

        $urlsearchbase = $CFG->wwwroot.'/local/sharedresources/index.php?course='.$courseid.'&repo=local&offset=0&Keyword_option=includes&go=Rechercher';

        echo $OUTPUT->box_start('block');
        echo $OUTPUT->box(get_string('topkeywords', 'local_sharedresources'), 'header');
        echo $OUTPUT->box_start('content');
        foreach ($topkws as $kw => $kwinfo) {
            $freq = sprintf('%02d', $kwinfo->rank);
            $keyword = urlencode($kwinfo->value);
            echo "<a style=\"font-size:1.{$freq}em\" href=\"$urlsearchbase&amp;Keyword=$keyword\" >{$kwinfo->value}</a> ";
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->box_end();
    }
}