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

defined('MOODLE_INTERNAL') || die();

/**
 * @package local_sharedresources
 * @category local
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 */

class local_sharedresources_renderer extends plugin_renderer_base {

    /**
     * Prints the "like" stars
     */
    function stars($stars, $maxstars) {
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
    function search_widgets_tableless($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {
        global $CFG, $OUTPUT;

        $str = '';

        if (empty($visiblewidgets)) {
            $str .= $OUTPUT->box_start('block');
            $str .= $OUTPUT->box_start('content');
            $str .= '<br/><center>'.get_string('nowidget', 'sharedresource').'</center><br/>';
            $str .= $OUTPUT->box_end();
            $str .= $OUTPUT->box_end();
        } else {
            $libraryurl = new moodle_url('/local/sharedresources/index.php');
            $str .= '<form name="cat" action="'.$libraryurl.'" style="display:inline">';
            if ($courseid) {
                $str .= '<input type="hidden" name="course" value="'.$courseid.'">';
            }
            $str .= '<input type="hidden" name="repo" value="'.$repo.'">';
            $str .= '<input type="hidden" name="offset" value="'.$offset.'">';

            $str .= '<fieldset>';
            $searchstr = get_string('searchinlibrary', 'sharedresource');
            $str .= '<legend>'.$searchstr.'</legend>';

            // Top button submit.
            $str .= $OUTPUT->box_start('block');
            $str .= '<div id="sharedresource-search-button content"><center>';
            $search = get_string('search');
            $str .= '<input type="submit" name="go" value="'.$search.'" />';
            $str .= '</center></div>';
            $str .= $OUTPUT->box_end();

            $n = 0;
            foreach ($visiblewidgets as $key => $widget) {
                $str .= $OUTPUT->box_start('block', 'widget-'.$key);
                $str .= $widget->print_search_widget('column', @$searchvalues[$widget->id]);
                $str .= $OUTPUT->box_end();
                $n++;
            }

            // Bottom button submit.
            $str .= $OUTPUT->box_start('block');
            $str .= '<div id="sharedresource-search-button content"><center>';
            $search = get_string('search');
            $str .= '<input type="submit" name="go" value="'.$search.'" />';
            $str .= '</center></div>';
            $str .= $OUTPUT->box_end();

            $str .= '</fieldset>';
            $str .= '</form>';
            $str .= '</div>';
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
    function search_widgets($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {
        global $CFG;

        $str = '';
        $libraryurl = new moodle_url('/local/sharedresources/index.php');

        if (empty($visiblewidgets)) {
            $str .= '<br/><center>'.get_string('nowidget', 'sharedresource').'</center><br/>';
        } else {
            $str .= '<form name="cat" action="'.$libraryurl.'" style="display:inline">';
            if ($courseid) {
                $str .= '<input type="hidden" name="course" value="'.$courseid.'">';
            }
            $str .= '<input type="hidden" name="repo" value="'.$repo.'">';
            $str .= '<input type="hidden" name="offset" value="'.$offset.'">';
            $str .= '<fieldset>';
            $searchstr = get_string('searchinlibrary', 'sharedresource');
            $str .= "<legend>$searchstr</legend>";
            $str .= '<table>';
            $str .= '<tr>';
            $n = 0;
            foreach ($visiblewidgets as $key => $widget) {
                $str .= '<td>';
                $str .= $widget->print_search_widget('column', @$searchvalues[$widget->id]);
                $str .= '</td>';
                $n++;
            }
            $str .= '</tr><tr><td colspan="'.$n.'" align="center">';
            $search = get_string('search');
            $str .= '<input type="submit" name="go" value="'.$search.'" />';
            $str .= '</td></tr>';
            $str .= '</table>';
            $str .= '</fieldset>';
            $str .= '</form>';
        }
    }

    /**
     * prints a pager for resource pages
     * @param int $courseid the course context id. I null the library is browsed from non course area
     * @param int $repo the repository ID
     * @param int $nbrpages
     */
    function pager($courseid, $repo, $nbrpages, $page, $offset = 0, $isediting = false) {
        $str = '';

        $str .= '<center><b>';
        if ($courseid) {
            for ($i = 1 ; $i <= $nbrpages ; $i++) {
                $pageoffset = ($i - 1) * $page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
                $libraryurl = new moodle_url('/local/sharedresources/index.php', array('course' => $courseid, 'repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting));
                $str .= '<a style="'.$pagestyle.'" name="page'.$i.'" href="'.$libraryurl.'">'.$i.'</a>';
            }
        } else {
            for ($i = 1 ; $i <= $nbrpages ; $i++) {
                $pageoffset = ($i - 1)*$page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
                $libraryurl = new moodle_url('/local/sharedresources/index.php', array('repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting));
                $str .= '<a style="'.$pagestyle.'" name="page'.$i.'" href="'.$libraryurl.'">'.$i.'</a>';
            }
        }
        $str .= '</center>';

        return $str;
    }

    /**
     * print list of the selected resources
     */
    function resources_list(&$resources, &$course, $section, $isediting = false, $repo = 'local') {
        global $CFG, $USER, $OUTPUT, $DB;

        $str = '';

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
                    $params = array('course' => 1, 'type' => 'file', 'add' => 'sharedresource', 'return' => 1, 'mode' => 'update', 'entry_id' => $resource->id);
                    $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
                    $commands = '<a href="'.$editurl.'" title="'.$editstr.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a>';
                    if ($resource->uses == 0) {
                        $params = array('what' => 'delete', 'course' => $courseid, 'id' => $resource->id);
                        $deleteurl = new moodle_url('/local/sharedresources/index.php', $params);
                        $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$deletestr.'"><img src="'.$OUTPUT->pix_url('delete', 'sharedresource').'" /></a>';
                    } else {
                        $params = array('what' => 'forcedelete', 'course' => $courseid, 'id' => $resource->id);
                        $deleteurl = new moodle_url('/local/sharedresources/index.php', $params);
                        $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$forcedeletestr.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
                    }
                    $params = array('course' => $courseid, 'resourceid' => $resource->id);
                    $pushurl = new moodle_url('/local/sharedresources/pushout.php', $params);
                    $commands .= '&nbsp;<a href="'.$pushurl.'" title="'.$exportstr.'"><img src="'.$OUTPUT->pix_url('export', 'sharedresource').'" /></a>';
                }
    
                $icon = ($isremote) ? 'remoteicon' : 'icon';
                $str .= "<div class='resourceitem'>"; //Resource item.

                // Resource heading.
                $str .= '<h3><img src="'.$OUTPUT->pix_url($icon, 'sharedresource').'" class="iconlarge" /> <span class="title">'.$resource->title.'</span> '.$commands.'</h3>';

                // Resource descriptors.
                $str .= $OUTPUT->box_start('generalbox');
                $str .= '<a class="smalllink" href="'.$resource->url.'" target="_blank">'.$resource->url.'</a><br/>';
    
                // Print notice access.
                $readnotice = get_string('readnotice', 'sharedresource');
                $url = "{$reswwwroot}/mod/sharedresource/metadatanotice.php?identifier={$resource->identifier}";
                $popupaction = new popup_action('click', $url, 'popup', array('width' => 800, 'height' => 600));
                $str .= $OUTPUT->action_link($url, $readnotice, $popupaction);
                $str .= '<br/>';
                if (!empty($resource->description)) {
                    $thumbnail = $this->thumbnail($resource);
                    $str .= '<div class="resource-description">'.$resource->description.$thumbnail.'</div><br/>';
                }
                $str .= '<span class="smalltext">'.get_string('keywords', 'sharedresource'). ": $resource->keywords</span><br/>";
                $str .= get_string('used', 'local_sharedresources', $resource->uses).'</br>';
                $str .= get_string('viewed', 'local_sharedresources', $resource->scoreview).'<br/>';
                $spanid = 'sharedresource-liked-'.$resource->identifier;
                $str .= get_string('liked', 'local_sharedresources', '<span id="'.$spanid.'">'.$this->stars($resource->scorelike, 15).'</span>').'</p>';
    
                $markliked = get_string('markliked', 'local_sharedresources');

                if (!empty($course) && ($course->id > SITEID)) {

                    $context = context_course::instance($course->id);

                    if (has_capability('moodle/course:manageactivities', $context)) {

                        $isLTITool = sharedresource_is_lti($resource);
                        $isMoodleActivity = sharedresource_is_moodle_activity($resource);
                        $isPlayableMedia = sharedresource_is_media($resource);

                        $addtocourse = get_string('addtocourse', 'sharedresource');
                        $localizetocourse = get_string('localizetocourse', 'sharedresource');
                        $addfiletocourse = get_string('addfiletocourse', 'sharedresource');
                        if (!$isremote) {
                            // If is local or already proxied.
                            $addtocourseurl = new moodle_url('/mod/sharedresource/addlocaltocourse.php');
                            $str .= '<form name="add'.$i.'" action="'.$addtocourseurl.'" style="display:inline">';
                        } else {
                            // If is a true remote.
                            $addremoteurl = new moodle_url('{$CFG->wwwroot}/mod/sharedresource/addremotetocourse.php');
                            $str .= '<form name="add'.$i.'" action="'.$addremoteurl.'" style="display:inline" method="POST" >';
                        }
                        $str .= '<input type="hidden" name="id" value="'.$course->id.'" />';
                        $str .= '<input type="hidden" name="mode" value="shared" />';
                        $str .= '<input type="hidden" name="section" value="'.$section.'" />';
                        $str .= '<input type="hidden" name="identifier" value="'.$resource->identifier.'" />';
                        $desc = htmlentities($resource->description, ENT_QUOTES, 'UTF-8');
                        $str .= '<input type="hidden" name="description" value="'.$desc.'" />';
                        $title = $resource->title;
                        $str .= '<input type="hidden" name="title" value="'.$title.'" />';
                        $str .= '<input type="hidden" name="provider" value="'.$repo.'" />';
                        $str .= '<input type="hidden" name="file" value="'.$resource->file.'" />';
                        $str .= '<input type="hidden" name="url" value="'.$resource->url.'" />';
                        $str .= '</form>';
    
                        $str .= '<div style="text-align:right" class="commands">';
                        $str .= '<a href="javascript:ajax_mark_liked(\''.$CFG->wwwroot.'\', \''.$repo.'\', \''.$resource->identifier.'\')">'.$markliked.'</a>';
                        if (!$isLTITool) {
                            $str .= ' - <a href="javascript:document.forms[\'add'.$i.'\'].submit();">'.$addtocourse.'</a>';
                            if (!$isMoodleActivity) {
                                if (!empty($resource->file) || ($isremote && empty($resource->isurlproxy))) {
                                    $str .= ' - <a href="javascript:document.forms[\'add'.$i.'\'].mode.value = \'local\';document.forms[\'add'.$i.'\'].submit();">'.$localizetocourse.'</a>';
                                }
                            }
                        }
    
                        if ($isMoodleActivity) {
                        // check deployable moodle activity
                            if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
                                include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');
                                $deployincourse = get_string('deployincourse', 'block_activity_publisher');
                                $str .= " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'deploy';document.forms['add{$i}'].submit();\">{$deployincourse}</a>";
                            }
                        }
    
                        // Check deployable LTI
                        if ($isLTITool) {
                            $installtool = get_string('installltitool', 'local_sharedresources');
                            $str .= " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'ltiinstall';document.forms['add{$i}'].submit();\">{$installtool}</a>";
                        }
        
                        $str .= '</div>';
                    }
                } else {
                    $str .= '<div style="text-align:right" class="commands">';
                    $str .= "<a href=\"javascript:ajax_mark_liked('{$CFG->wwwroot}', '{$repo}', '{$resource->identifier}')\">{$markliked}</a>";
                    $str .= '</div>';
                }
                $str .= "</div>";//resource item
                $str .= $OUTPUT->box_end();
                $i++;
            }
        } else {
            $str .= get_string('noresources', 'local_sharedresources');
        }

        return $str;
    }

    /**
     * A set of links visible from the Library administrator.
     * @param object $course the course the Library is browsed in the context of.
     */
    function tools($course) {
        global $CFG;

        $str = '';

        if ($course->id == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($course->id);
        }

        $toollinks = array(); 

        $str .= '<center>';
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

        $str .= implode("&nbsp;-&nbsp;", $toollinks);
        $str .= '</center>';

        return $str;
    }

    /**
     * print tabs allowing selection of the current repository provider
     * note that provider is necessarily a mnet host identity.
     */
    function browse_tabs($repo, $course) {
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
                $repourl = new moodle_url('/local/sharedresources/index.php', array('course' => $course->id, 'repo' => $repoid));
                $rows[0][] = new tabobject($repoid, $repourl, $repos[$repoid]);
            } else {
                $repourl = new moodle_url('/local/sharedresources/index.php', array('repo' => $repoid));
                $rows[0][] = new tabobject($repoid, $repourl, $repos[$repoid]);
            }
        }

        return print_tabs($rows, $repo, $repos[$repo], null, true);
    }

    /**
     * Print resource repository tabs
     * @param string $repo the current repo
     * @param int $course the course context
     */
    function search_tabs($repo, $course) {
        global $CFG;

        $str = '';

        $repos = get_list_of_plugins('/local/sharedresources/plugins');

        if (!in_array($repo, $repos)) {
            $repo = $repos[0];
        }

        foreach ($repos as $arepo) {
            $repourl = new moodle_url('/local/sharedresources/search.php', array('id' => $course->id, 'repo' => $arepo));
            $rows[0][] = new tabobject($arepo, $repourl, get_string($arepo.'_reponame', 'local_sharedresources'));
        }

        $str .= print_tabs($rows, $repo, $repos[$repo], null, true);

        return $str;
    }

    function top_keywords($courseid) {
        global $OUTPUT, $CFG;

        $str = '';

        if ($topkws = sharedresource_get_top_keywords($courseid)) {

            $params = array('course' => $courseid, 'repo' => 'local', 'offset' => 0, 'Keyword_option' => 'includes', 'go' => 'Rechercher');
            $urlsearchbase = new moodle_url('/local/sharedresources/index.php', $params);

            $str .= $OUTPUT->box_start('block');
            $str .= $OUTPUT->box(get_string('topkeywords', 'local_sharedresources'), 'header');
            $str .= $OUTPUT->box_start('content');
            foreach ($topkws as $kw => $kwinfo) {
                $freq = sprintf('%02d', $kwinfo->rank);
                $keyword = urlencode($kwinfo->value);
                $str .= "<a style=\"font-size:1.{$freq}em\" href=\"$urlsearchbase&amp;Keyword=$keyword\" >{$kwinfo->value}</a> ";
            }
            $str .= $OUTPUT->box_end();
            $str .= $OUTPUT->box_end();
        }

        return $str;
    }


    /**
    * // TODO : May be obsolete, possibly delete
    */
    function update_resourcepage_icon() {
        global $CFG, $USER;

        if (!isloggedin()) return '';

        if (!empty($USER->editing)) {
            $string = get_string('updateresourcepageoff', 'sharedresource');
            $edit = '0';
        } else {
            $string = get_string('updateresourcepageon', 'sharedresource');
            $edit = '1';
        }

        $updateurl = new moodle_url('/local/sharedresources/index.php');
        $str = '<form '.$CFG->frametarget.' method="get" action="'.$updateurl.'">';
        $str .= '<div>';
        $str .= '<input type="hidden" name="edit" value="'.$edit.'" />';
        $str .= '<input type="submit" value="'.$string.'" />';
        $str .= '</div></form>';

        return $str;
    }

    function thumbnail($resource) {
        static $fs;
        static $context;

        if (!$fs) $fs = get_file_storage();
        if (!$context) $context = context_system::instance();

        $files = $fs->get_area_files($context->id, 'mod_sharedresource', 'thumbnail', $resource->id, true);
        
        if ($file = array_pop($files)) {
            $thumbfileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            return '<img style="float:right;padding-left:30px" src="'.$thumbfileurl.'" />';
        }

        return '';
    }
}