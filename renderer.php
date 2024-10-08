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
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');

class local_sharedresources_renderer extends plugin_renderer_base {

    private $navigator;

    public function __construct() {
        global $SESSION, $OUTPUT;

        if (!isset($this->output)) {
            $this->output = $OUTPUT;
        }

        $requiredtaxonomy = optional_param('taxonomy', false, PARAM_INT);
        if (!empty($SESSION->sharedresource->taxonomy) || $requiredtaxonomy) {
            if ($requiredtaxonomy) {
                $SESSION->sharedresource->taxonomy = $requiredtaxonomy;
            }
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
            $str .= $OUTPUT->pix_icon($icon, '', 'local_sharedresources');
        }

        return $str;
    }

    /**
     * generic entry point to render the search block.
     * @param int $courseid
     * @param string $repo
     * @param int $offset
     * @param object $context
     * @param array $visiblewidgets
     * @param array $searchvalues
     * @param string $layout
     */
    public function search_block($courseid, $repo, $offset, $context, $visiblewidgets, $searchvalues, $layout = 'tableless') {

        switch ($layout) {
            case 'tableless': {
                return $this->search_widgets($courseid, $repo, $offset, $context, $visiblewidgets, $searchvalues, 'tableless');
            }
            case 'singlefield' : {
                /* One single text field that searches in all metainformation. */
                return $this->search_single_widget($courseid, $repo, $offset, $context, $visiblewidgets, $searchvalues);
            }
            case 'routedfield': {
                /* One single text field that searches in a selected routed field. */
                return $this->search_routed_widget($courseid, $repo, $offset, $context, $visiblewidgets, $searchvalues);
            }
            default : {
                throw new coding_exception('Unknown search layout '.$layout);
            }
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
    public function search_widgets($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues, $layout = '') {

        $template = new StdClass;
        $template->strnowidgets = get_string('nowidget', 'sharedresource');

        if (empty($visiblewidgets)) {
            return;
        }

        $section = optional_param('section', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_INT);

        $template->haswidgets = true;
        $params = ['course' => $courseid, 'section' => $section, 'return' => $return, 'mode' => 'simple'];
        $template->simplemodeurl = new moodle_url('/local/sharedresources/explore.php', $params);
        $params = ['course' => $courseid, 'section' => $section, 'return' => $return, 'mode' => 'full'];
        $template->fullmodeurl = new moodle_url('/local/sharedresources/explore.php', $params);
        $template->formurl = new moodle_url('/local/sharedresources/explore.php');
        $template->courseid = $courseid;
        $template->section = $section;
        $template->return = $return;
        $template->repo = $repo;
        $template->offset = $offset;
        $template->searchstr = get_string('search');
        $template->resetstr = get_string('reset', 'local_sharedresources');
        $template->usemodes = true; // Switch to configu boolean
        $template->mode = 'full';
        $template->islongform = true;

        $template->widgets = array();
        $n = 0;
        foreach ($visiblewidgets as $key => $searchwidget) {
            $widgettpl = new StdClass;
            $widgettpl->key = $key;
            $widgettpl->widget = $searchwidget->print_search_widget('column', @$searchvalues[$searchwidget->id]);
            $template->widgets[] = $widgettpl;
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
     * Prints a unique textual widget that will search into all metadata consistant textual values.
     * @param int $courseid
     * @param int $repo
     * @param int $offset
     * @param object $context
     * @param object &$visiblewidgets widget list for the form.
     * @param object &$searchvalues list of actual values.
     */
    public function search_single_widget($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues) {

        $widget = new \local_sharedresources\search\freetext_widget('simple-0', 'simplesearch');

        $template = new StdClass;
        $template->haswidgets = true;

        $section = optional_param('section', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_INT);

        $params = ['course' => $courseid, 'section' => $section, 'return' => $return, 'mode' => 'simple'];
        $template->simplemodeurl = new moodle_url('/local/sharedresources/explore.php', $params);
        $params = ['course' => $courseid, 'section' => $section, 'return' => $return, 'mode' => 'full'];
        $template->fullmodeurl = new moodle_url('/local/sharedresources/explore.php', $params);
        $template->courseid = $courseid;
        $template->section = $section;
        $template->return = $return;
        $template->usemodes = true; // Switch to configu boolean
        $template->repo = $repo;
        $template->offset = $offset;
        $template->searchstr = get_string('search');
        $template->mode = 'simple';
        $template->islongform = false;

        $widgettpl = new StdClass;
        $widgettpl->key = 'simplesearch';
        $widgetvalue = array_pop($searchvalues); // All search fields have the same value comming from 'simplesearch'.
        $widgettpl->widget = $widget->print_search_widget('column', $widgetvalue);
        $template->widgets[] = $widgettpl;

        return $this->output->render_from_template('local_sharedresources/searchform_tableless', $template);

    }

    /**
     * print widgets calling the adequate widget class instance
     * @param int $courseid
     * @param int $repo
     * @param int $offset the record count offset of the current page
     * @param object $context the current course or site context
     * @param array ref $visiblewidgets an array of widgets to print
     * @param array ref $searchvalues an array of actual values
     * DEPRECATED
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
        global $FULLME;

        $template = new StdClass();

        if ($courseid) {
            for ($i = 1; $i <= $nbrpages; $i++) {
                $pageoffset = ($i - 1) * $page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt';
                $params = array('course' => $courseid, 'repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting);
                $libraryurl = new moodle_url($FULLME, $params);
                $pagetpl = new StdClass;
                $pagetpl->i = $i;
                $pagetpl->pagestyle = $pagestyle;
                $pagetpl->libraryurl = $libraryurl;
                $template->pages[] = $pagetpl;
            }
        } else {
            for ($i = 1; $i <= $nbrpages; $i++) {
                $pageoffset = ($i - 1) * $page;
                $pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt';
                $params = array('repo' => $repo, 'offset' => $pageoffset, 'isediting' => $isediting);
                $libraryurl = new moodle_url('/local/sharedresources/index.php', $params);
                $pagetpl = new StdClass;
                $pagetpl->i = $i;
                $pagetpl->pagestyle = $pagestyle;
                $pagetpl->libraryurl = $libraryurl;
                $template->pages[] = $pagetpl;
            }
        }

        return $this->output->render_from_template('local_sharedresources/pager', $template);
    }

    /**
     * print list of the selected resources
     * @param arrayref &$resources the selection of resources to print
     * @param objectref &$course the course being used as origin
     * @param int $section the section to return to to keep course context
     * @param bool $isediting do the user have editing capabilities ?
     * @param string $repo the current repository
     */
    public function resources_list(&$resources, &$course, $section, $isediting = false, $repo = 'local') {
        global $CFG, $OUTPUT;

        $resources = (array)$resources;

        $listviewthreshold = get_config('local_sharedresources', 'listviewthreshold');

        $str = '';

        $consumers = sharedresources_get_consumers();

        $courseid = (empty($course->id)) ? '' : $course->id;

        $gui = $this->get_gui();
        $gui->hasproviders = count($providers = sharedresources_get_providers());

        if (!empty($listviewthreshold) && (!empty($resources)) && (count($resources) < $listviewthreshold)) {
            $gui->bodytplname = 'boxresourcebody';
        }
        if (!empty($CFG->resourcebodytplname)) {
            $gui->bodytplname = $CFG->resourcebodytplname;
        };

        if (!defined('RETURN_PAGE')) {
            define('RETURN_PAGE', get_config('local_sharedresources', 'defaultlibraryindexpage'));
        }

        if (!empty($resources)) {

            $str .= $this->output->render_from_template('local_sharedresources/'.$gui->bodytplname.'start', null);

            foreach ($resources as $resource) {
                $str .= $this->print_resource($resource, $course, $repo, $isediting, $gui);
            }

            $str .= $this->output->render_from_template('local_sharedresources/'.$gui->bodytplname.'end', null);
        } else {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                $str .= $OUTPUT->notification(get_string('noresourceshere', 'local_sharedresources'));
            }
        }

        return $str;
    }

    /**
     * Make stub of static data used in print loop.
     */
    public function get_gui() {

        $gui = new StdClass;
        $gui->editstr = get_string('update');
        $gui->deletestr = get_string('delete');
        $gui->exportstr = get_string('export', 'sharedresource');
        $gui->forcedeletestr = get_string('forcedelete', 'local_sharedresources');
        $gui->aclsstr = get_string('accesscontrol', 'local_sharedresources');

        $gui->aclspix = $this->output->pix_icon('i/permissions', $gui->aclsstr);
        $gui->deletepix = $this->output->pix_icon('t/delete', $gui->deletestr, 'core');
        $gui->forcedeletepix = $this->output->pix_icon('t/delete', $gui->forcedeletestr, 'core');
        $gui->exportpix = $this->output->pix_icon('export', $gui->exportstr, 'sharedresource');
        $gui->defaultresourcepixurl = $this->output->image_url('defaultdocument', 'sharedresource');

        $gui->bodytplname = 'resourcebody';
        return $gui;
    }

    /**
     * Print a single resource.
     */
    public function print_resource($resource, $course, $repo, $isediting, $gui) {
        static $shrconfig;
        static $config;
        static $fs;
        static $i = 0;
        global $CFG, $FULLME, $USER, $DB;

        $str = '';

        if (is_null($config)) {
            // Get config once.
            $shrconfig = get_config('sharedresource');
            $config = get_config('local_sharedresources');
            $fs = get_file_storage();
        }

        // TODO : needs to be reworked.
        if ($repo == 'local' || $repo == $CFG->mnet_localhost_id) {
            // Get local once.
            $resource->uses = sharedresource_get_usages($resource, $response, null);
            $reswwwroot = $CFG->wwwroot;
        } else {
            // remote repo.
            $resourcehost = $DB->get_record('mnet_host', array('id' => $repo));
            $reswwwroot = $resourcehost->wwwroot;
        }

        // Librarian controls.
        $commands = '';
        if ($isediting) {
            $catid = optional_param('catid', '', PARAM_INT);
            $catpath = optional_param('catpath', '', PARAM_TEXT);

            $params = array('course' => 1,
                            'type' => 'file',
                            'fromlibrary' => true,
                            'returnpage' => RETURN_PAGE,
                            'return' => 0, /* not significant here as not comming from course workflow */
                            'mode' => 'update',
                            'entryid' => $resource->id,
                            'catid' => $catid,
                            'catpath' => $catpath);
            $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
            $commands = '<a href="'.$editurl.'" title="'.$gui->editstr.'">'.$this->output->pix_icon('t/edit', get_string('edit')).'</a>';

            if (sharedresource_supports_feature('entry/accessctl') && $shrconfig->accesscontrol) {
                $params = array('course' => $courseid, 'resourceid' => $resource->id, 'return' => 'localindex');
                $aclsurl = new moodle_url('/mod/sharedresource/pro/classificationacls.php', $params);
                $commands .= '&nbsp;<a href="'.$aclsurl.'" title="'.$gui->aclsstr.'">'.$gui->aclspix.'</a>';
            }

            if (@$resource->uses == 0) {
                $params = array('what' => 'delete', 'course' => $course->id, 'id' => $resource->id);
                $deleteurl = new moodle_url($FULLME, $params);
                $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$gui->deletestr.'" class="sharedresource delete">'.$gui->deletepix.'</a>';
            } else {
                $params = array('what' => 'forcedelete', 'course' => $course->id, 'id' => $resource->id);
                $deleteurl = new moodle_url($FULLME, $params);
                $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$gui->forcedeletestr.'" class="sharedresource force-delete">'.$gui->forcedeletepix.'</a>';
            }
            if (!empty($gui->hasprovider)) {
                $params = array('course' => $course->id, 'resourceid' => $resource->id);
                $pushurl = new moodle_url('/local/sharedresources/pushout.php', $params);
                $commands .= '&nbsp;<a href="'.$pushurl.'" title="'.$gui->exportstr.'">'.$gui->exportpix.'</a>';
            }
        }

        $template = new StdClass;

        if ('pro' == local_sharedresources_supports_feature('emulate/community')) {
            if (!empty($config->hidesocial)) {
                $template->hidesocial = true;
            }
        }

        // Resource heading.
        $icon = ($repo != 'local') ? 'remoteicon' : 'icon';
        $template->community = !empty($config->community);
        $template->ishiddenbyrule = (!empty($resource->hidden)) ? "is-hidden-by-rule" : '';
        $template->pixurl = $this->output->image_url($icon, 'sharedresource');

        $systemcontext = context_system::instance();
        $contextid = $systemcontext->id;
        $component = 'mod_sharedresource';
        $area = 'thumbnail';
        $itemid = $resource->id;

        if (($repo == 'local') || ($repo == $CFG->mnet_localhost_id)) {
            $mainfile = false;
            if (!empty($resource->file)) {
                if ($mainfile = $fs->get_file_by_id($resource->file)) {
                    $resource->filename = $mainfile->get_filename();
                    $resource->filepath = $mainfile->get_filepath();
                    $template->mimetype = $mainfile->get_mimetype();
                }
            }

            // Used with iconurl.
            $template->iconsize = 32;

            $customresourcethumbs = $fs->get_area_files($contextid, $component, $area, $itemid, '', false);
            $template->iscustomicon = false;
            if (!empty($customresourcethumbs)) {
                $customthumbfile = array_pop($customresourcethumbs);
                $template->largepixurl = moodle_url::make_pluginfile_url($contextid, $component, $area, $itemid,
                                                 $customthumbfile->get_filepath(), $customthumbfile->get_filename(), false);
                $template->iscustomicon = true;
            } else {
                if (!empty($mainfile)) {
                    $template->largepixurl = $this->output->image_url(file_file_icon($mainfile, 128));
                    $template->pixurl = $this->output->image_url(file_extension_icon($mainfile->get_filename()));
                    $template->filesize = sprintf('%.2f', $mainfile->get_filesize() / 1000);
                    $template->fileunit = ' ko';
                    if ($template->filesize > 1000) {
                        $template->filesize = sprintf('%.2f', $template->filesize / 1000);
                        $template->fileunit = ' Mo';
                    }
                } else {
                    $template->largepixurl = $this->output->image_url('weblink', 'local_sharedresources');
                }
            }
        } else {
            $resource->filename = @$resource->file_filename;
            $resource->filepath = @$resource->file_filepath;
            $template->largepixurl = @$resource->file_iconurl;
            $template->mimetype = @$resource->file_mimetype;
        }

        $template->downloadpixurl = $this->output->image_url('download', 'local_sharedresources');
        $template->boxdownloadpixurl = $this->output->image_url('boxdownload', 'local_sharedresources');
        $template->xmlpixurl = $this->output->image_url('notice', 'local_sharedresources');
        $template->boxxmlpixurl = $this->output->image_url('boxnotice', 'local_sharedresources');

        $template->quotedurl = htmlentities($resource->url, ENT_QUOTES, 'UTF-8');

        // Only for file type resources or remote resources.
        $template->quotedfilename = htmlentities(@$resource->filename, ENT_QUOTES, 'UTF-8');
        $template->quotedfilepath = htmlentities(@$resource->filepath, ENT_QUOTES, 'UTF-8');

        $template->title = $this->trim_chars($resource->title, 120);
        $template->editioncommands = $commands;
        $template->haseditioncommands = !empty($commands);
        $template->identifier = $resource->identifier;
        if ($resource->provider != 'local') {
            $providerhostid = $DB->get_field('mnet_host', 'id', array('wwwroot' => $resource->provider));
        } else {
            $providerhostid = $CFG->mnet_localhost_id;
        }
        $template->repoid = $providerhostid;

        // Print notice access.
        $template->shownotice = false;
        if (empty($config->hidenotice)) {
            $template->shownotice = true;
            $readnotice = get_string('readnotice', 'sharedresource');
            $url = "{$reswwwroot}/mod/sharedresource/metadatanotice.php?identifier={$resource->identifier}";
            $popupaction = new popup_action('click', $url, 'popup', array('width' => 800, 'height' => 600));
            $pixicon = new pix_icon('notice', $readnotice, 'local_sharedresources');
            $template->noticepopupactionlink = $this->output->action_link($url, '', $popupaction, array('title' => $readnotice), $pixicon);
            $pixicon = new pix_icon('boxnotice', $readnotice, 'local_sharedresources');
            $template->boxnoticepopupactionlink = $this->output->action_link($url, '', $popupaction, array('title' => $readnotice), $pixicon);
        }

        // Content toggler.
        $template->handlepixurl = $this->output->image_url('rightarrow', 'local_sharedresources');

        $template->uses = $resource->uses ?? '';
        if (!empty($resource->uses)) {
            $params = array('courseid' => @$course->id, 'entryid' => $resource->id);
            $template->courselisturl = new moodle_url('/local/sharedresources/courses.php', $params);
            $template->uses = $resource->uses;
        }

        // Views.
        $template->views = $resource->scoreview;

        // Likes.
        $template->stars = $this->stars($resource->scorelike, 15);

        // Resource descriptors.
        if (!empty($resource->description)) {
            $template->thumbnail = $this->thumbnail($resource);
            $template->description = $resource->description;

            // Keywords.
            $template->keywords = $resource->keywords;
            $template->quotedkeywords = htmlentities($resource->keywords, ENT_QUOTES, 'UTF-8');
        }

        // Download url.
        if (empty($resource->url)) {
            $resourceurl = new moodle_url('/local/sharedresources/view.php', array('identifier' => $resource->identifier));
        } else {
            $resourceurl = $resource->url;
        }

        if ($repo == 'local' || ($repo == $CFG->mnet_localhost_id)) {
            if ($resource->context > 1) {
                $viewcap = 'repository/sharedresources:view';
                try {
                    $access = sharedresources_has_capability_in_upper_contexts($viewcap, $resource->context, true, true);
                    if ($access) {
                        $template->url = $resourceurl;
                    } else {
                        // Show the resource but do not allow download.
                        $template->url = false;
                    }
                } catch (Exception $e) {
                    $template->url = false;
                }
            } else {
                // No conditions
                $template->url = $resourceurl;
            }
        } else {
            // A remote resource is always downloadable.
            $template->url = $resource->url;

            // A remote resource may need a token.
            $template->token = $resource->token;
        }

        // Resource caracterization.
        $template->isresource = true; // Default, may be overriden by other types.
        $template->islocalizable = true; // Default, may be overriden by other types.
        $template->i = $i;
        $template->isltitool = sharedresource_is_lti($resource);
        $template->ismoodleactivity = sharedresource_is_moodle_activity($resource);
        $template->isscorm = sharedresource_is_scorm($resource);
        $template->isplayablemedia = sharedresource_is_media($resource);

        if ($template->isltitool) {
            $template->mimetype = 'application/lti';
            $template->islocalizable = false;
            $template->isresource = false;
            if (empty($template->iscustomicon)) {
                $template->largepixurl = $this->output->image_url('icon', 'mod_lti');
            }
        }

        // Ressource version links.
        $template->hasversions = false;
        $resourceobj = mod_sharedresource\entry::get_by_id($resource->id);
        // Previous and next return the current resource id (same id) if no lower or (resp) upper version is available.
        $previous = $resourceobj->get_previous();
        $hasprevious = ($previous > 0) && ($previous != $resource->id);
        $next = $resourceobj->get_next();
        $hasnext = ($next > 0) && ($next != $resource->id);
        if ($hasprevious || $hasnext) {
            $template->hasversions = true;

            $template->hasprevious = false;
            $template->hasnext = false;
            if ($hasprevious) {
                $previousobj = mod_sharedresource\entry::get_by_id($previous);
                $template->hasprevious = true;
                $template->previous = $previousobj->identifier;
            }

            if ($hasnext) {
                $nextobj = mod_sharedresource\entry::get_by_id($next);
                $template->hasnext = true;
                $template->next = $nextobj->identifier;
            }

        }

        // Ressource commands.
        $template->hascommands = false;
        if (!empty($course) && ($course->id > SITEID)) {
            $context = context_course::instance($course->id);

            if (has_capability('moodle/course:manageactivities', $context) &&
                has_capability('repository/sharedresources:use', $context)) {
                $template->hascommands = true;

                $template->isremote = !($repo == 'local' || ($repo == $CFG->mnet_localhost_id));
                if (!$template->isremote) {
                    // If is local or already proxied.
                    $template->formurl = new moodle_url('/mod/sharedresource/addlocaltocourse.php');
                } else {
                    // If is a true remote.
                    $template->formurl = new moodle_url('/mod/sharedresource/addremotetocourse.php');
                    $template->islocalizable = false; // a remote resource cannot be localized.
                }
                $template->courseid = $course->id;
                $template->section = optional_param('section', 0, PARAM_INT);
                $template->identifier = $resource->identifier;
                $template->quoteddesc = htmlentities($resource->description, ENT_QUOTES, 'UTF-8');
                $template->quotedtitle = htmlentities($resource->title, ENT_QUOTES, 'UTF-8');
                $template->repo = $repo;
                $template->file = $resource->file;

                if ($template->isscorm) {
                    $template->islocalizable = false;
                }

                if ($template->ismoodleactivity) {
                    // Check deployable moodle activity.
                    if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
                        include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');
                        $template->isdeployable = true;
                        $template->islocalizable = false;
                    }
                }

                if ($template->isplayablemedia) {
                    if (is_dir($CFG->dirroot.'/mod/mplayer')) {
                        $template->isplayable = true;
                        $template->islocalizable = false;
                    }
                }

                // Quote url for command form.
                $template->quotedurl = urlencode($template->url);
                $template->quotedmimetype = urlencode(@$template->mimetype);

            }
        }

        $str .= $this->output->render_from_template('local_sharedresources/'.$gui->bodytplname, $template);
        $i++;
        return $str;
    }

    /**
     * A set of links visible from the Library administrator.
     * @param object $course the course the Library is browsed in the context of.
     */
    public function tools($course) {

        $template = new StdClass;

        $systemcontext = context_system::instance();
        if (sharedresources_has_capability_somewhere('repository/sharedresources:create', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {

            if ($course->id > SITEID) {
                // User has capability to convert his local resources to shared entries.
                $template->convertstr = get_string('resourceconversion', 'local_sharedresources');
                $template->converturl = new moodle_url('/mod/sharedresource/admin_convertall.php', array('course' => $course->id));
            }

            // Librarian should have the capability everywhere. Enabled teachers in their own course.
            $template->newresourcestr = get_string('newresource', 'local_sharedresources');
            $catid = optional_param('catid', 0, PARAM_INT);
            $catpath = optional_param('catpath', '', PARAM_TEXT);
            $params = array('course' => $course->id,
                            'type' => 'file',
                            'fromlibrary' => true,
                            'returnpage' => RETURN_PAGE,
                            'mode' => 'add',
                            'catid' => $catid,
                            'catpath' => $catpath);
            $template->editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
        }

        if (local_sharedresources_supports_feature('import/mass')) {
            if (has_capability('repository/sharedresources:massimport', $systemcontext)) {
                // Only librarians in a "pro" version can mass import.
                $template->massimportstr = get_string('massimport', 'local_sharedresources');
                $template->importurl = new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php', array('course' => $course->id));
            }
        }

        return $this->output->render_from_template('local_sharedresources/librariantools', $template);
    }

    /**
     * print tabs allowing selection of the current repository provider
     * note that provider is necessarily a mnet host identity.
     * Only available in "pro" version.
     *
     * @see local/sharedresources/pro/lib.php.
     *
     * @param string $repo the current repo
     * @param object $course
     */
    public function browse_tabs($repo, $course) {
        return sharedresources_get_provider_tabs($repo, $course);
    }

    /**
     * Print resource repository tabs
     * @param string $repo the current repo
     * @param mixed $courseorid the course context, id or object.
     */
    public function search_tabs($repo, $courseorid) {

        if (is_int($courseorid)) {
            $courseid = $courseorid;
        } else {
            $courseid = $courseorid->id;
        }

        $str = '';

        $repos = get_list_of_plugins('/local/sharedresources/plugins');

        if (!in_array($repo, $repos)) {
            $repo = $repos[0];
        }

        foreach ($repos as $arepo) {
            $repourl = new moodle_url('/local/sharedresources/search.php', array('id' => $courseid, 'repo' => $arepo));
            $rows[0][] = new tabobject($arepo, $repourl, get_string($arepo.'_reponame', 'local_sharedresources'));
        }

        $str .= print_tabs($rows, $repo, $repos[$repo], null, true);

        return $str;
    }

    /**
     * @param int $courseid the origin course id
     */
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
                $freq = sprintf('%02d', $kwinfo->ranking);
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

    public function category(&$cat, &$catpath, $resourcecount, $current = 'current', $up = false) {
        global $COURSE;

        $courseid = optional_param('course', $COURSE->id, PARAM_INT);
        $section = optional_param('section', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_INT);

        $template = new StdClass;

        $template->current = $current;

        $nextpath = (empty($catpath)) ? $cat->id.'/' : $catpath.$cat->id.'/';

        if (strpos($catpath, '/') === false) {
            $prevpath = '';
        } else {
            $prevpath = preg_replace('#'.$cat->id.'/$#', '', $catpath);
        }

        if ($up) {
            $template->hasup = true;
            $template->upstr = get_string('up', 'local_sharedresources');
            if (!is_null($cat->parent)) {
                $params = array('catid' => $cat->parent, 'catpath' => $prevpath, 'course' => $courseid);
                if (!empty($section)) {
                    $params['section'] = $section;
                }
                if (!empty($return)) {
                    $params['return'] = $return;
                }
                $template->parentcaturl = new moodle_url('/local/sharedresources/browse.php', $params);
                $template->upiconurl = $this->output->image_url('up', 'local_sharedresources');
                $template->catspan = 9;
            } else {
                $params = array('catid' => 0, 'catpath' => '', 'course' => $courseid);
                if (!empty($section)) {
                    $params['section'] = $section;
                }
                if (!empty($return)) {
                    $params['return'] = $return;
                }
                $template->parentcaturl = new moodle_url('/local/sharedresources/browse.php', $params);
                $template->upiconurl = $this->output->image_url('up', 'local_sharedresources');
                $template->catspan = 9;
            }
        } else {
            $template->catspan = 11;
        }

        if ($current == 'sub') {
            $params = array('catid' => $cat->id, 'catpath' => $nextpath, 'course' => $courseid);
            if (!empty($section)) {
                $params['section'] = $section;
            }
            if (!empty($return)) {
                $params['return'] = $return;
            }
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
            foreach ($cat->cats as $child) {
                $str .= $this->child($child, $catpath);
            }
        }

        return $str;
    }

    protected function child(&$cat, $catpath) {
        $catcount = $this->navigator->count_entries_rec($catpath.$cat->id.'/');
        return $this->category($cat, $catpath, $catcount, 'sub', false);
    }

    public function filters() {
        return '';
    }

    public function searchlink() {
        global $COURSE;

        $courseid = optional_param('course', $COURSE->id, PARAM_INT);
        $section = optional_param('section', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_INT);

        $template = new StdClass;
        $template->buttonstr = get_string('searchinlibrary', 'local_sharedresources');
        $params = ['course' => $courseid];
        if (!empty($section)) {
            $params['section'] = $section;
        }
        if (!empty($return)) {
            $params['return'] = $return;
        }

        $buttonurl = new moodle_url('/local/sharedresources/explore.php', $params);
        $template->buttonurl = $buttonurl->out();
        $template->class = 'sharedresources-link-to-search';

        return $this->output->render_from_template('local_sharedresources/modebutton', $template);
    }

    public function browserlink() {
        global $COURSE;

        $courseid = optional_param('course', $COURSE->id, PARAM_INT);
        $section = optional_param('section', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_INT);

        $template = new StdClass;
        $template->buttonstr = get_string('browse', 'local_sharedresources');
        if ($courseid == SITEID) {
            $template->buttonurl = new moodle_url('/local/sharedresources/browse.php');
        } else {
            $params = ['course' => $courseid];
            if (!empty($section)) {
                $params['section'] = $section;
            }
            if (!empty($return)) {
                $params['return'] = $return;
            }
            $buttonurl = new moodle_url('/local/sharedresources/browse.php', $params);
            $template->buttonurl = $buttonurl->out();
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

        $systemcontext = context_system::instance();
        if (!has_capability('repository/sharedresources:manage', $systemcontext)) {
            // Discard taxonomies that are disabled by rules.
            foreach (array_keys($enabledtaxonomies) as $txid) {
                $taxo = new \local_sharedresources\browser\navigation($txid);
                if (!$taxo->can_use()) {
                    unset($enabledtaxonomies[$txid]);
                }
            }
        }

        if (empty($enabledtaxonomies)) {
            throw new moodle_exception(get_string('notaxonomiesenabled', 'local_sharedresources'));
        }

        $taxonomyids = array_keys($enabledtaxonomies);

        if ((count($enabledtaxonomies) < 2) || empty($SESSION->sharedresources->taxonomy)) {
            if (is_null($SESSION->sharedresources)) {
                $SESSION->sharedresources = new StdClass;
            }
            $SESSION->sharedresources->taxonomy = array_shift($taxonomyids);
            if (count($enabledtaxonomies) < 2) {
                return $this->output->heading($enabledtaxonomies[$SESSION->sharedresources->taxonomy]);
            }
        }

        $template = new StdClass;
        $selected = @$SESSION->sharedresources->taxonomy;
        $meurl = new moodle_url('/local/sharedresources/browse.php');
        $template->taxonomychooser = $this->output->single_select($meurl, 'taxonomy', $enabledtaxonomies, $selected);
        $template->taxonomystr = get_string('choosetaxonomy', 'local_sharedresources');

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

    /**
     * @params $catpath idencoded path from root to category
     */
    public function add_path($catpath, $navigator) {
        global $SESSION, $DB, $PAGE;

        $courseid = optional_param('course', SITEID, PARAM_INT);

        if (!isset($SESSION->sharedresources)) {
            $SESSION->sharedresources = new StdClass;
        }

        if ($SESSION->sharedresources->taxonomy = optional_param('taxonomy', @$SESSION->sharedresources->taxonomy, PARAM_INT)) {
            $taxoname = $DB->get_field('sharedresource_classif', 'name', array('id' => $SESSION->sharedresources->taxonomy));
            $params = array('course' => $courseid, 'catpath' => '', 'catid' => 0);
            $url = null;
            if (!empty($catpath)) {
                $url = new moodle_url('/local/sharedresources/browse.php', $params);
            }
            $PAGE->navbar->add($taxoname, $url);
            $catpath = rtrim($catpath, '/');

            if (!empty($catpath)) {
                $pathids = explode('/', $catpath);
                $path = '';
                while ($catid = array_shift($pathids)) {
                    $category = $navigator->get_category($catid);
                    if (count($pathids) >= 1) {
                        $path .= $catid.'/';
                        $params = array('course' => $courseid, 'catpath' => $path, 'catid' => $catid);
                        $url = null;
                        if (!empty($pathids)) {
                            $url = new moodle_url('/local/sharedresources/browse.php', $params);
                        }
                        $PAGE->navbar->add($category->name, $url);
                    } else {
                        $PAGE->navbar->add($category->name);
                    }
                }
            }
        }
    }

    /**
     * Cut a text to some length.
     *
     * @param $str
     * @param $n
     * @param $end_char
     * @return string
     */
    public function trim_chars($str, $n = 500, $endchar = '...') {

        $debug = optional_param('debug', false, PARAM_BOOL);

        $opentags = [];
        $singletags = ['br', 'hr', 'img', 'input', 'link'];

        if (empty($str)) {
            // Optimize return.
            return '';
        }

        // Tokenize around HTML tags.
        $htmltagpattern = "#(.*?)(</?[^>]+?>)(.*)#s";
        $end = $str;
        $parts = [];
        while (preg_match($htmltagpattern, $end, $matches)) {
            if (!empty($matches[1])) {
                array_push($parts, $matches[1]);
            }
            if (!empty($matches[2])) {
                array_push($parts, $matches[2]);
            }
            $end = $matches[3];
        }
        if (!empty($matches)) {
            // Take last end that has no more tags inside.
            array_push($parts, $end);
        }

        $buflen = 0;
        $buf = '';
        $iscutoff = false;

        if ($debug) {
            echo "Having ".count($parts)." to parse\n";
        }

        while ($part = array_shift($parts)) {

            if ($buflen > $n) {
                $iscutoff = true;
                if ($debug) {
                    echo "Cutting off\n";
                }
                break;
            }

            if (strpos($part, '<') === 0) {
                // is a tag.
                preg_match('#<(/?)([a-zA-Z0-6]+).*(/?)>#', $part, $matches);
                $isendtag = !empty($matches[1]);
                $tagname = $matches[2];
                $issingletag = (!empty($matches[3]) || in_array($tagname, $singletags));
                if (!$issingletag) {
                    if (!$isendtag) {
                        // So its a starting tag and NOT single.
                        if ($debug) {
                            echo "Start Tag $tagname\n";
                        }
                        array_push($opentags, $tagname);
                    } else {
                        // So its an ending tag. We just check it has been correctly stacked.
                        $lasttag = array_pop($opentags);
                        if ($debug) {
                            echo "End Tag $tagname\n";
                        }
                        if ($lasttag !== $tagname) {
                            // This is a nesting error in the source HTML.
                            throw new moodle_exception("Malformed HTML content somewhere in product descriptions");
                        }
                    }
                } else {
                    if ($debug) {
                        echo "Single Tag $tagname\n";
                    }
                }
            } else {
                // Is text.
                // TODO : cut the text to the remaining amount of chars to get near $n chars.
                $buflen += mb_strlen(str_replace("\n", '', $part));
                if ($debug) {
                    echo "Text node '$part': Adding ".mb_strlen(str_replace("\n", '', $part))."\n";
                    echo "Buflen : $buflen\n";
                }
            }
            $buf .= $part;
        }

        if (!$iscutoff) {
            if ($debug) {
                echo '</pre>';
            }
            return $str;
        }

        if (!empty($parts)) {
            // Add final ellipsis if there is something retained in original string.
            $buf .= $endchar;
        }

        // At this point, $opentags should be empty if all openedtags have been closed.
        while (!empty($opentags)) {
            $closing = '</'.array_pop($opentags).'>';
            $buf .= $closing;
        }

        if ($debug) {
            echo '</pre>';
        }
        return $buf;
    }
}