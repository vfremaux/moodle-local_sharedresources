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
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');

class local_sharedresources_renderer extends plugin_renderer_base {

    private $navigator;

    public function __construct() {
        global $SESSION, $OUTPUT;

        if (!isset($this->output)) {
            $this->output = $OUTPUT;
        }

        if (!empty($SESSION->sharedresource->taxonomy)) {
            $taxonomy = $SESSION->sharedresource->taxonomy;
        } else {
            $taxonomies = \local_sharedresources\browser\navigation::get_taxonomies(true);
            if (!empty($taxonomies)) {
                // Take first available.
                $taxonomy = array_shift($taxonomies);
                if (!isset($SESSION->sharedresource)) {
                    $SESSION->sharedresource = new StdClass();
                }
                $SESSION->sharedresource->taxonomy = $taxonomy;
            }
        }

        if (!empty($taxonomy)) {
            // Some metadata schema may not have.
            $this->navigator = new \local_sharedresources\browser\navigation($taxonomy);
        }
    }

    /**
     * Prints the "like" stars
     */
    public function stars($stars, $maxstars) {
        global $OUTPUT;

        $str = '';

        for ($i = 0; $i < $maxstars; $i++) {
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
    public function search_widgets($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues, $layout = '') {

        $template = new StdClass;
        $template->strnowidgets = get_string('nowidget', 'sharedresource');

        if (empty($visiblewidgets)) {
            return;
        }

        $template->haswidgets = true;

        $template->formurl = new moodle_url('/local/sharedresources/explore.php');
        $template->courseid = $courseid;
        $template->repo = $repo;
        $template->offset = $offset;
        $template->searchstr = get_string('search');

        $template->widgets = array();
        $n = 0;
        foreach ($visiblewidgets as $key => $searchwidget) {
            $widget = new StdClass;
            $widget->key = $key;
            $widget->widget = $searchwidget->print_search_widget('column', @$searchvalues[$searchwidget->id]);
            $template->widgets[] = $widget;
            $n++;
        }
        $template->n = $n;

        if ($layout == 'tableless') {
            return $this->output->render_from_template('local_sharedresources/searchform_tableless', $template);
        } else {
            return $this->output->render_from_template('local_sharedresources/searchform', $template);
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
    public function search_widgets_tableless($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {
        return $this->search_widgets($courseid, $repo, $offset, $context, $visiblewidgets, $searchvalues, 'tableless');
    }

    /**
     * prints a pager for resource pages
     * @param int $courseid the course context id. I null the library is browsed from non course area
     * @param int $repo the repository ID
     * @param int $nbrpages
     */
    public function pager($courseid, $repo, $nbrpages, $page, $offset = 0, $isediting = false) {
        $str = '';

        $str .= '<center><b>';
        if ($courseid) {
            for ($i = 1; $i <= $nbrpages; $i++) {
                $pageoffset = ($i - 1) * $page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
                $params = array('course' => $courseid, 'repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting);
                $libraryurl = new moodle_url('/local/sharedresources/index.php', $params);
                $str .= '<a style="'.$pagestyle.'" name="page'.$i.'" href="'.$libraryurl.'">'.$i.'</a>';
            }
        } else {
            for ($i = 1; $i <= $nbrpages; $i++) {
                $pageoffset = ($i - 1)*$page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
                $params = array('repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting);
                $libraryurl = new moodle_url('/local/sharedresources/index.php', $params);
                $str .= '<a style="'.$pagestyle.'" name="page'.$i.'" href="'.$libraryurl.'">'.$i.'</a>';
            }
        }
        $str .= '</center>';

        return $str;
    }

    /**
     * print list of the selected resources
     */
    public function resources_list(&$resources, &$course, $section, $isediting = false, $repo = 'local') {
        global $CFG, $USER, $OUTPUT, $DB;

        $shrconfig = get_config('sharedresource');
        $config = get_config('local_sharedresources');

        $str = '';

        $isremote = ($repo != 'local');
        $consumers = sharedresources_get_consumers();

        $courseid = (empty($course->id)) ? '' : $course->id;

        $editstr = get_string('update');
        $deletestr = get_string('delete');
        $exportstr = get_string('export', 'sharedresource');
        $forcedeletestr = get_string('forcedelete','local_sharedresources');
        $aclsstr = get_string('accesscontrol', 'local_sharedresources');

        $aclspix = '<img src="'.$this->output->pix_url('i/permissions').'" alt="'.$aclsstr.'"/>';
        $deletepix = '<img src="'.$this->output->pix_url('delete', 'sharedresource').'" alt="'.$deletestr.'" />';
        $forcedeletepix = '<img src="'.$this->output->pix_url('t/delete').'" alt="'.$forcedeletestr.'" />';
        $exportpix = '<img src="'.$this->output->pix_url('export', 'sharedresource').'" alt="'.$exportstr.'" />';
        $defaultresourcepixurl = $this->output->pix_url('defaultdocument', 'sharedresource');

        $bodytplname = 'resourcebody';
        if (!empty($config->listviewthreshold) && count($resources) < $config->listviewthreshold) {
            $bodytplname = 'boxresourcebody';
        }

        if (!empty($CFG->resourcebodytplname)) {
            $bodytplname = $CFG->resourcebodytplname;
        };

        $fs = get_file_storage();

        if ($resources) {
            $i = 0;

            $str .= $this->output->render_from_template('local_sharedresources/'.$bodytplname.'start', null);

            foreach ($resources as $resource) {

                if (!$isremote) {
                    // Get local once.
                    $resource->uses = sharedresource_get_usages($resource, $response, null);
                    $reswwwroot = $CFG->wwwroot;
                } else {
                    $resourcehost = $DB->get_record('mnet_host', array('id' => $repo));
                    $reswwwroot = $resourcehost->wwwroot;
                }

                // Librarian controls.
                $commands = '';
                if ($isediting) {
                    $params = array('course' => 1,
                                    'type' => 'file',
                                    'add' => 'sharedresource',
                                    'return' => 1,
                                    'mode' => 'update',
                                    'entryid' => $resource->id);
                    $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
                    $commands = '<a href="'.$editurl.'" title="'.$editstr.'"><img src="'.$this->output->pix_url('t/edit').'" /></a>';

                    if (mod_sharedresource_supports_feature('entry/accessctl') && $shrconfig->accesscontrol) {
                        $params = array('course' => $courseid, 'resourceid' => $resource->id, 'return' => 'localindex');
                        $aclsurl = new moodle_url('/mod/sharedresource/pro/classificationacls.php', $params);
                        $commands .= '&nbsp;<a href="'.$aclsurl.'" title="'.$aclsstr.'">'.$aclspix.'</a>';
                    }

                    if ($resource->uses == 0) {
                        $params = array('what' => 'delete', 'course' => $courseid, 'id' => $resource->id);
                        $deleteurl = new moodle_url('/local/sharedresources/index.php', $params);
                        $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$deletestr.'">'.$deletepix.'</a>';
                    } else {
                        $params = array('what' => 'forcedelete', 'course' => $courseid, 'id' => $resource->id);
                        $deleteurl = new moodle_url('/local/sharedresources/index.php', $params);
                        $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$forcedeletestr.'">'.$forcedeletepix.'</a>';
                    }
                    $params = array('course' => $courseid, 'resourceid' => $resource->id);
                    $pushurl = new moodle_url('/local/sharedresources/pushout.php', $params);
                    $commands .= '&nbsp;<a href="'.$pushurl.'" title="'.$exportstr.'">'.$exportpix.'</a>';
                }

                $template = new StdClass;

                // Resource heading.
                $icon = ($isremote) ? 'remoteicon' : 'icon';
                $template->ishiddenbyrule = (!empty($resource->hidden)) ? "is-hidden-by-rule" : '';
                $template->pixurl = $this->output->pix_url($icon, 'sharedresource');

                if (!empty($resource->file)) {
                    $mainfile = $fs->get_file_by_id($resource->file);
                    $template->largepixurl = $this->output->pix_url(file_file_icon($mainfile, 128));
                } else {
                    $template->largepixurl = $defaultresourcepixurl;
                }

                $template->downloadpixurl = $this->output->pix_url('download', 'local_sharedresources');

                $template->url = $resource->url;
                $template->title = $resource->title;
                $template->editioncommands = $commands;
                $template->identifier = $resource->identifier;

                // Print notice access.
                $readnotice = get_string('readnotice', 'sharedresource');
                $url = "{$reswwwroot}/mod/sharedresource/metadatanotice.php?identifier={$resource->identifier}";
                $popupaction = new popup_action('click', $url, 'popup', array('width' => 800, 'height' => 600));
                $template->noticepopupactionlink = $this->output->action_link($url, $readnotice, $popupaction);

                // Content toggler.
                $template->handlepixurl = $this->output->pix_url('rightarrow', 'local_sharedresources');

                $template->uses = $resource->uses;
                if (empty($resource->uses)) {
                    $template->strnotused = get_string('notused', 'local_sharedresources');
                } else {
                    $params = array('courseid' => @$course->id, 'entryid' => $resource->id);
                    $template->courselisturl = new moodle_url('/local/sharedresources/courses.php', $params);
                    $template->usedstr = get_string('used', 'local_sharedresources', $resource->uses);
                }

                // Views.
                $template->viewedstr = get_string('viewed', 'local_sharedresources', $resource->scoreview);

                // Likes.
                $template->marklikedstr = get_string('markliked', 'local_sharedresources');
                // $jshandler = 'javascript:ajax_mark_liked(\''.$repo.'\', \''.$resource->identifier.'\')';

                $template->stars = $this->stars($resource->scorelike, 15);
                $template->likedstr = get_string('liked', 'local_sharedresources');

                // Resource descriptors.
                if (!empty($resource->description)) {
                    $template->thumbnail = $this->thumbnail($resource);
                    $template->description = $resource->description;

                    // Keywords.
                    $template->keywordsstr = get_string('keywords', 'sharedresource');
                    $template->keywords = $resource->keywords;
                }

                // Ressource commands.
                if (!empty($course) && ($course->id > SITEID)) {

                    $context = context_course::instance($course->id);

                    if (has_capability('moodle/course:manageactivities', $context)) {

                        $cmdtemplate = new StdClass;

                        $cmdtemplate->installtoolstr = get_string('installltitool', 'local_sharedresources');
                        $cmdtemplate->addtocoursestr = get_string('addtocourse', 'sharedresource');
                        $cmdtemplate->localizetocoursestr = get_string('localizetocourse', 'sharedresource');
                        $cmdtemplate->addfiletocoursestr = get_string('addfiletocourse', 'sharedresource');

                        $cmdtemplate->i = $i;
                        $cmdtemplate->isltitool = sharedresource_is_lti($resource);
                        $ismoodleactivity = sharedresource_is_moodle_activity($resource);
                        $isplayablemedia = sharedresource_is_media($resource);

                        $cmdtemplate->isremote = $isremote;
                        if (!$isremote) {
                            // If is local or already proxied.
                            $cmdtemplate->formurl = new moodle_url('/mod/sharedresource/addlocaltocourse.php');
                        } else {
                            // If is a true remote.
                            $cmdtemplate->formurl = new moodle_url('/mod/sharedresource/addremotetocourse.php');
                        }
                        $cmdtemplate->courseid = $courseid;
                        $cmdtemplate->section = $section;
                        $cmdtemplate->identifier = $resource->identifier;
                        $cmdtemplate->quoteddesc = htmlentities($resource->description, ENT_QUOTES, 'UTF-8');
                        $cmdtemplate->quotedtitle = htmlentities($resource->title, ENT_QUOTES, 'UTF-8');
                        $cmdtemplate->repo = $repo;
                        $cmdtemplate->file = $resource->file;
                        $cmdtemplate->url = $resource->url;

                        if (!$cmdtemplate->isltitool && !$ismoodleactivity) {
                            /*
                            $str .= '<a href="javascript:document.forms[\'add'.$i.'\'].submit();">'.$addtocourse.'</a>';
                            if (!$ismoodleactivity) {
                                if (!empty($resource->file) || ($isremote && empty($resource->isurlproxy))) {
                                    $jshandler = 'javascript:document.forms[\'add'.$i.'\'].mode.value = \'local\';';
                                    $jshandler .= 'document.forms[\'add'.$i.'\'].submit();';
                                    $str .= ' - <a href="'.$jshandler.'">'.$localizetocourse.'</a>';
                                }
                            }
                            */
                            $cmdtemplate->islocalizable = true;
                        }

                        /**
                            $jshandler = 'javascript:document.forms[\'add'.$i.'\'].mode.value = \'ltiinstall\';';
                            $jshandler .= 'document.forms[\'add'.$i.'\'].submit();';
                            $str .= ' - <a href="'.$jshandler.'">'.$installtool.'</a>';
                        */

                        if ($ismoodleactivity) {
                        // Check deployable moodle activity.
                            if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
                                include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');
                                $cmdtemplate->deployincoursestr = get_string('deployincourse', 'block_activity_publisher');
                                $cmdtemplate->isdeployable = true;
                            }
                        }

                        if ($isplayablemedia) {
                            if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
                                $cmdtemplate->deployinmplayerstr = get_string('deployinmplayer', 'mediaplayer');
                                $cmdtemplate->isvideo = true;
                                $cmdtemplate->formurl = new moodle_url('/mod/mplayer/deployincourse.php');
                            }
                        }
                        $template->rescommands = $this->output->render_from_template('local_sharedresources/resourcecommands', $cmdtemplate);
                    }
                }

                $str .= $this->output->render_from_template('local_sharedresources/'.$bodytplname, $template);

                $i++;
            }

            $str .= $this->output->render_from_template('local_sharedresources/'.$bodytplname.'end', null);
        } else {
            $str .= $OUTPUT->notification(get_string('noresources', 'local_sharedresources'));
        }

        return $str;
    }

    /**
     * A set of links visible from the Library administrator.
     * @param object $course the course the Library is browsed in the context of.
     */
    public function tools($course) {

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
            $converturl = new moodle_url('/mod/sharedresource/admin_convertall.php', array('course' => $course->id));
            $toollinks[] = '<a href="'.$converturl.'">'.$convertstr.'</a>';
        }

        if (has_capability('repository/sharedresources:manage', $context)) {
            $newresourcestr = get_string('newresource', 'local_sharedresources');
            $params = array('course' => $course->id, 'type' => 'file', 'add' => 'sharedresource', 'return' => 1, 'mode' => 'add');
            $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
            $toollinks[] = '<a href="'.$editurl.'">'.$newresourcestr.'</a>';

            if (local_sharedresources_supports_feature('import/mass')) {
                $massimportstr = get_string('massimport', 'local_sharedresources');
                $importurl = new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php', array('course' => $course->id));
                $toollinks[] = '<a href="'.$importurl.'">'.$massimportstr.'</a>';
            }
        }

        $str .= implode("&nbsp;-&nbsp;", $toollinks);
        $str .= '</center>';

        return $str;
    }

    /**
     * print tabs allowing selection of the current repository provider
     * note that provider is necessarily a mnet host identity.
     * Only available in "pro" version.
     *
     * @see local/sharedresources/pro/lib.php.
     *
     * @param string $repo
     * @param object $course
     */
    public function browse_tabs($repo, $course) {
        return sharedresources_get_provider_tabs($repo, $course);
    }

    /**
     * Print resource repository tabs
     * @param string $repo the current repo
     * @param int $course the course context
     */
    public function search_tabs($repo, $course) {

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

    public function top_keywords($courseid) {
        global $OUTPUT;

        $str = '';

        if ($topkws = sharedresource_get_top_keywords($courseid)) {

            $params = array('course' => $courseid,
                            'repo' => 'local',
                            'offset' => 0,
                            'Keyword_option' => 'includes',
                            'go' => get_string('search'));
            $urlsearchbase = new moodle_url('/local/sharedresources/index.php', $params);

            foreach ($topkws as $kw => $kwinfo) {
                $freq = sprintf('%02d', $kwinfo->rank);
                $keyword = urlencode($kwinfo->value);
                $str .= "<a style=\"font-size:1.{$freq}em\" href=\"$urlsearchbase&amp;Keyword=$keyword\" >{$kwinfo->value}</a> ";
            }
        }

        return $str;
    }

    public function thumbnail($resource) {
        static $fs;
        static $context;

        if (!$fs) {
            $fs = get_file_storage();
        }
        if (!$context) {
            $context = context_system::instance();
        }

        $files = $fs->get_area_files($context->id, 'mod_sharedresource', 'thumbnail', $resource->id, 'filepath,filename', true);

        if ($file = array_pop($files)) {
            $thumbfileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                                            $file->get_filearea(), $file->get_itemid(),
                                                            $file->get_filepath(), $file->get_filename());
            return '<img src="'.$thumbfileurl.'" />';
        }

        return '';
    }

    /**
     * Prints a course reference line for a used resource.
     */
    public function resource_course($c) {

        $str = '';
        $str .= '<li>';
        $courseurl = new moodle_url('/course/view.php', array('id' => $c->id));
        $str .= '<a href="'.$courseurl.'">'.format_string($c->fullname).'</a>';
        $str .= '</li>';

        return $str;
    }

    function category(&$cat, &$catpath, $resourcecount, $current = 'current', $up = false) {

        $template = new StdClass;

        $template->current = $current;

        $nextpath = (empty($catpath)) ? $cat->id.'/' : $catpath.$cat->id.'/';

        if (strpos($catpath, '/') === false) {
            $prevpath =  '';
        } else {
            $prevpath = preg_replace('#'.$cat->id.'/$#', '', $catpath);
        }

        if ($up) {
            $template->hasup = true;
            if (!is_null($cat->parent)) {
                $params = array('catid' => $cat->parent, 'catpath' => $prevpath);
                $template->parentcaturl = new moodle_url('/local/sharedresources/browse.php', $params);
                $template->upiconurl = $this->output->pix_url('up', 'local_courseindex');
                $template->catspan = 9;
            } else {
                $params = array('catid' => 0, 'catpath' => '');
                $template->parentcaturl = new moodle_url('/local/sharedresources/browse.php', $params);
                $template->upiconurl = $this->output->pix_url('up', 'local_courseindex');
                $template->catspan = 9;
            }
        } else {
            $template->catspan = 11;
        }

        if ($current == 'sub') {
            $params = array('catid' => $cat->id, 'catpath' => $nextpath);
            $template->caturl = new moodle_url('/local/sharedresources/browse.php', $params);
            $template->hassubs = $cat->hassubs;
        }
        $template->catname = format_string($cat->name);
        $template->catid = $cat->id;
        $template->resourcecount = $resourcecount;

        return $this->output->render_from_template('local_sharedresources/resourcecategory', $template);
    }

    /**
     * Print all current children of the current category.
     * @param object $cat
     * @param string $catpath
     */
    public function children(&$cat, $catpath) {
        global $OUTPUT;

        $str = '';

        if (!empty($cat->cats)) {

            $str .= $OUTPUT->heading(get_string('subcategories'));

            foreach ($cat->cats as $child) {
                $str .= $this->child($child, $catpath);
            }
        }

        return $str;
    }

    protected function child(&$cat, $catpath) {
        return $this->category($cat, $catpath, $this->navigator->count_entries_rec($catpath.$cat->id.'/'), 'sub', false);
    }

    public function filters() {
        return '';
    }

    public function searchlink() {

        $template = new StdClass;
        $template->buttonstr = get_string('searchinlibrary', 'local_sharedresources');
        $template->buttonurl = new moodle_url('/local/sharedresources/index.php');
        $template->class = 'sharedresources-link-to-search';

        return $this->output->render_from_template('local_sharedresources/modebutton', $template);
    }

    public function browserlink() {
        global $COURSE;

        $template = new StdClass;

        $template->buttonstr = get_string('browse', 'local_sharedresources');
        if ($COURSE->id > SITEID) {
            $template->buttonurl = new moodle_url('/local/sharedresources/browse.php');
        } else {
            $template->buttonurl = new moodle_url('/local/sharedresources/browse.php', array('course' => $COURSE->id));
        }
        $template->class = 'sharedresources-link-to-browser';

        return $this->output->render_from_template('local_sharedresources/modebutton', $template);
    }

    /**
     * Prints a taxonomy selector if more than one activated.
     */
    public function taxonomy_select() {
        global $DB, $SESSION;

        if (!isset($SESSION->sharedresources)) {
            $SESSION->sharedresources = new StdClass;
        }

        $SESSION->sharedresources->taxonomy = optional_param('taxonomy', @$SESSION->sharedresources->taxonomy, PARAM_INT);

        $enabledtaxonomies = \local_sharedresources\browser\navigation::get_taxonomies_menu(true);

        if (empty($enabledtaxonomies)) {
            print_error('notaxonomiesenabled', 'local_sharedresources');
        }

        $taxonomyids = array_keys($enabledtaxonomies);

        if ((count($enabledtaxonomies) < 2) || empty($SESSION->sharedresources->taxonomy)) {
            if (is_null($SESSION->sharedresources)) {
                $SESSION->sharedresources = new StdClass;
            }
            $SESSION->sharedresources->taxonomy = array_shift($taxonomyids);
            if (count($enabledtaxonomies) < 2) {
                return '';
            }
        }

        $template = new StdClass;
        $selected = @$SESSION->sharedresources->taxonomy;
        $meurl = new moodle_url('/local/sharedresources/browse.php');
        $template->taxonomychooser = $this->output->single_select($meurl, 'taxonomy', $enabledtaxonomies, $selected);

        return $this->output->render_from_template('local_sharedresources/taxonomychooser', $template);
    }

    public function confirm_import_form($data) {

        $template = new Stdclass;

        $template->importpath = $data->importpath;
        $template->context = $data->context;
        $template->importexclusionpattern = @$data->importexclusionpattern;
        $template->deducetaxonomyfrompath = @$data->deducetaxonomyfrompath;
        $template->simulate = @$data->simulate;
        $template->taxonomy = $data->taxonomy;
        $template->encoding = $data->encoding;
        $template->localize = @$data->localize;
        $template->deployzips = @$data->deployzips;
        $template->makelabelsfromguidance = @$data->makelabelsfromguidance;
        if (!empty($data->simulate)) {
            $template->confirmstr = get_string('confirmsimulate', 'local_sharedresources');
        } else {
            $template->confirmstr = get_string('confirm', 'local_sharedresources');
        }

        // Used when using an uploded archive deployed by internal moodle zip packer.
        $template->nativeutf8 = @$data->nativeutf8;

        return $this->output->render_from_template('local_sharedresources/confirm_import_form', $template);
    }
}