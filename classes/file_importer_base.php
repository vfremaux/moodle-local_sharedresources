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
 * @package    local_sharedresources
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * an abstract class that represents a single file importer
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/sharedresource/sharedresource_metadata.class.php';
require_once $CFG->dirroot.'/mod/sharedresource/locallib.php';
require_once $CFG->dirroot.'/course/lib.php';

class file_importer_base {

    /**
     * the file descriptor out from data collection. Descriptor is an array of properties
     */
    protected $fd;

    /**
     * the created or matched sharedresource entry
     */
    protected $sharedresourceentry;

    protected $new;

    /**
     * the final metadata entries
     */
    protected $metadatadefines;

    static protected $mtdstandard;

    protected $metadatakeymap = array('title' => '1_2:0_0',
                           'language' => '1_3:0_0',
                           'description' => '1_4:0_0',
                           'documenttype' => '1_9:0_0',
                           'documentnature' => '1_10:0_0',
                           'pedagogictype' => '5_2:0_0',
                           'difficulty' => '5_9:0_0',
                           'guidance' => '5_10:0_0',
                          );

    public function __construct($descriptor) {
        global $CFG;

        $this->fd = $descriptor;
        $this->sharedresourceentry = null;
        $this->metadatadefines = array();
        $this->new = true;

        $object = 'sharedresource_plugin_'.$CFG->pluginchoice;
        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
        if (empty(self::$mtdstandard)) {
            self::$mtdstandard = new $object;
        }
    }

    /** creates the sharedresource entry or loads an existing one if matches for aggregating metadata to it, and saves physical file 
     * everything is in the descriptor
     * @param int $context the sharing context of the sharedresource
     */
    public function make_resource_entry($context = 1) {
        global $CFG;

        /*
         * first we check we do not have this file yet. We must create a temporary file record for this
         * this will allow us to access all the stored_file API for this file.
         */
        $systemcontext = context_system::instance();
        $filerecord = new StdClass();
        $filerecord->contextid = $systemcontext->id;
        $filerecord->component = 'mod_sharedresource';
        $filerecord->filearea = 'temp';
        $filerecord->filepath = '/';
        if ($CFG->ostype == 'WINDOWS') {
            $filerecord->filename = utf8_decode(pathinfo($this->fd['fullpath'], PATHINFO_BASENAME));
        } else {
            $filerecord->filename = pathinfo($this->fd['fullpath'], PATHINFO_BASENAME);
        }
        $filerecord->itemid = 0;

        $this->pre_process_file($this->fd['fullpath']);

        $fs = get_file_storage();

        if (!$storedfile = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea,
                                          $filerecord->itemid, $filerecord->filepath, $filerecord->filename)) {
            if (!defined('DO_NOT_WRITE')) {
                if ($CFG->ostype == 'WINDOWS') {
                    $storedfile = $fs->create_file_from_pathname($filerecord, utf8_decode(trim($this->fd['fullpath'])));
                } else {
                    $storedfile = $fs->create_file_from_pathname($filerecord, trim($this->fd['fullpath']));
                }
            } else {
                if (defined('CLI_SCRIPT')) {
                    $message = "Test mode : File resource $filerecord->contextid, $filerecord->component, ";
                    $message .= "$filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename created";
                    mtrace($message);
                }
                return;
            }
        }

        // Now check we do not have it yet in the library? If we do, we load the library entry and we continue.
        $newidentifier = $storedfile->get_contenthash();
        if (!$this->sharedresourceentry = sharedresource_entry::get_by_identifier($newidentifier)) {

            // If not in library...
            $sharedresourceentry = new StdClass();
            $sharedresourceentry->type = 'file';
            /*
             * is this a local resource or a remote one?
             * if resource uploaded then move to temp area until user has
             * saved the file
             */
            $sharedresourceentry->identifier = $storedfile->get_contenthash();
            $sharedresourceentry->file = $storedfile->get_id();
            $sharedresourceentry->identifier = $newidentifier;
            $sharedresourceentry->url = '';
            $sharedresourceentry->context = $context;
            $this->sharedresourceentry = new sharedresource_entry($sharedresourceentry);
            $this->sharedresourceentry->storedfile = $storedfile;

            // This is a default title (with a bit formatting) that can be overriden by explicit metadata.
            if (!array_key_exists('title', $this->fd)) {
                $this->fd['title'] = str_replace('_', ' ', $storedfile->get_filename());
            }

        } else {
            $this->new = false;
        }
        if (defined('CLI_SCRIPT')) {
            mtrace('Sharedresource entry prepared for '.$storedfile->get_filepath().'/'.$storedfile->get_filename());
        }
    }

    /**
     * transforms metadata calling appropriate handler for each input field
     * cleans the metadata input depending on currently used schema.
     *
     * calls autoadaptative transformer. If no transformer is found, just remap the
     * incoming csv fieldname to proper metadata node:instance.
     *
     * transformer
     */
    public function metadata_preprocess() {
        foreach ($this->fd as $inputkey => $inputvalue) {
            // We can defer all metadata preparation to an external handler.
            if (method_exists('file_importer_base', 'prepare_'.$inputkey)) {
                $f = 'prepare_'.$inputkey;
                $fd[$inputkey] = $this->$f($inputvalue);
            } else {
                // Or we just transfer the value into metadata stub after keymapping to Dublin Core node identifier.
                if (array_key_exists($inputkey, $this->MTDKEYMAP)) {
                    $instancekey = $this->MTDKEYMAP[$inputkey];
                    list ($nodekey, $instance) = explode(':', $instancekey);
                    // This adapts to really used metadata standard, whatever rich the metadata.csv file is.
                    if (self::$mtdstandard->hasNode($nodekey)) {
                        $this->metadatadefines[$instancekey] = $inputvalue;
                    }
                }
            }
        }
    }

    // Aggregates metadata along within sharedresource entry.
    public function aggregate_metadata() {
        global $CFG;

        // We do not have correct sharedresource entry to attach metadata to.
        if (empty($this->sharedresourceentry) && !defined('DO_NOT_WRITE')) {
            return;
        }

        if (!empty($this->metadatadefines)) {
            foreach ($this->metadatadefines as $node => $mtdentry) {
                if (!defined('DO_NOT_WRITE')) {
                    $this->sharedresourceentry->add_element($node, $mtdentry, $CFG->pluginchoice);
                } else {
                    mtrace("Test mode : Adding MTD : $node, $mtdentry");
                }
            }
        }
    }

    public function save() {
        if (!defined('DO_NOT_WRITE')) {
            if ($this->new == true) {
                $this->sharedresourceentry->add_instance();
            } else {
                $this->sharedresourceentry->update_instance();
            }
            sharedresources_mark_file_imported($this->fd['fullpath']);
        } else {
            mtrace("Test mode : Saving....\n");
        }
    }

    /**
     * if a course is mentionned in file description in field course
     * (based on course shortname), and an eventual section (field section) number is given,
     * this will add a course module to the relevant section of the course, on this entry.
     * an optional field (coursemoduletype) let you decide if the attached module is a sharedresource
     * or a standard "file" resource (unshared, cloned)
     * this function DOES NOT HANDLE paged formats
     */
    public function attach() {
        global $DB, $CFG;

        if (empty($this->fd['shortname'])) {
            return;
        }

        if (!$course = $DB->get_record('course', array('shortname' => $this->fd['shortname']))) {
            mtrace('Unexisting course '.$this->fd['shortname'].' for attachement. Skipping....');
            return;
        }

        if (defined('DO_NOT_WRITE')) {
            if (defined('CLI_SCRIPT')) {
                mtrace('Test mode : attaching to '.$this->fd['shortname']);
            }
            return;
        }

        if (defined('CLI_SCRIPT')) {
            mtrace('attaching to '.$this->fd['shortname']);
        }
        $sectionnum = (!empty($this->fd['section'])) ? $this->fd['section'] : 0;

        $visible = (isset($this->fd['visible'])) ? $this->fd['visible'] : 1;

        /*
         * if we ever have collected pedagogic description as a guidance, and we want to make
         * automatically labels from them...
         */
        $guidance = $this->sharedresourceentry->element('5_10:0_0', $CFG->pluginchoice);
        if (defined('MAKE_LABELS_FROM_GUIDANCE') && $guidance) {
            $this->add_label_to_section($guidance, $course, $sectionnum, $visible);
        }

        $instance = new sharedresource_base(0, $this->sharedresourceentry->identifier);
        $instance->options = 0;
        $instance->popup = 0;
        $instance->name = $this->sharedresourceentry->title;
        $instance->intro = $this->sharedresourceentry->description;
        $instance->introformat = FORMAT_MOODLE;
        $instance->course = $course->id;
        $instance->alltext = '';
        $instance->id = $instance->add_instance();

        // Make a new course module for the initial sharedresource instanceid.
        $module = $DB->get_record('modules', array('name'=> 'sharedresource'));
        $cm = new StdClass;
        $cm->instance = $instance->id;
        $cm->module = $module->id;
        $cm->course = $course->id;
        $cm->visible = $visible;
        $cm->visibleold = $visible;
        $cm->section = 1;
        // This is fake ! will be postfed after reals section number is known.

        // Remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !!
        // Valid also if LTI tool.
        if (!empty($this->sharedresourceentry->remoteid)) {
            $cm->idnumber = $this->sharedresourceentry->remoteid;
        }

        // Insert the course module in course.
        if (!$cm->id = add_course_module($cm)) {
            print_error('errorcmaddition', 'sharedresource');
        }

        if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)) {
            print_error('errorsectionaddition', 'sharedresource');
        }

        $context = context_module::instance($cm->id);

        // Now we have the real value for the $cm->section.
        if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
            print_error('errorcmsectionbinding', 'sharedresource');
        }

        // We can post process a resource conversion when everything is clear.
        if ((!empty($this->fd->coursemoduletype) && $this->fd->coursemoduletype == 'resource') ||
                defined('CONVERT_TO_RESOURCE')) {
            if (defined('CLI_SCRIPT')) {
                mtrace("Converting to legacy resource");
            }
            $instanceid = sharedresource_convertfrom($instance, false);

            // We can autodeploy zips if required.
            if (defined('AUTO_DEPLOY') && preg_match('/\.zip$/', $this->fd['file'])) {
                $this->deploy($cm);
            }
        }

        // Reset the course modinfo cache for rebuilding it all.
        $course->modinfo = null;
        $DB->update_record('course', $course);
    }

    /**
     * makes a label course module and add it to section
     * @param string $guidance a guidance text
     * @param int $courseid The course ID
     * @param int $sectionnum relative section number in course
     */
    public function add_label_to_section($guidance, $course, $sectionnum, $visible = 1) {
        global $DB;

        $instance = new StdClass;
        $instance->course = $course->id;
        $instance->name = shorten_text($guidance, 200);
        $instance->intro = '<p>'.$guidance.'</p>';
        $instance->introformat = 1;
        $instance->timemodified = time();

        $instance->id = $DB->insert_record('label', $instance);

        // Make a new course module.
        $module = $DB->get_record('modules', array('name' => 'label'));
        $cm = new StdClass;
        $cm->instance = $instance->id;
        $cm->module = $module->id;
        $cm->visible = $visible;
        $cm->course = $course->id;
        $cm->section = 1;

        // Insert the course module in course.
        if (!$cm->id = add_course_module($cm)) {
            print_error('errorcmaddition', 'sharedresource');
        }

        // Reset the course modinfo cache.
        $DB->set_field('course', 'modinfo', '', array('id' => $course->id));

        if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)) {
            print_error('errorsectionaddition', 'sharedresource');
        }

        if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
            print_error('errorcmsectionbinding', 'sharedresource');
        }

        if (defined('CLI_SCRIPT')) mtrace("Added Label course module $cm->id in section $sectionid");
    }

    /**
     * this function should be overriden by more specific format dedicated
     * subclass if any metdata can be guessed from the file content itself
     */
    public function get_metadata_from_file() {
        return;
    }

    /* * static handlers for metadata inputs * */

    // From a list of keywords, build a set of metadata nodes.
    public function prepare_keywords($keywords) {
        $kws = explode(',', $keywords);
        $kwfield = self::$mtdstandard->getKeywordElement();
        $i = 0;
        foreach ($kws as $kw) {
            $kw = trim($kw);
            $this->metadatadefines[$kwfield->name.':0_'.$i] = $kw;
            $i++;
        }
    }

    /**
     * real wrappers from the CSV file format
     * add both in sharedresource record AND metadata
     */
    public function prepare_description($description) {
        $descfield = self::$mtdstandard->getDescriptionElement();
        $this->metadatadefines[$descfield->name.':0_0'] = $description;
        if (!defined('DO_NOT_WRITE')) {
            $this->sharedresourceentry->description = $description; // We must simulate htmleditor return.
        }
    }

    /**
     * real wrappers from the CSV file format
     * add both in sharedresource record AND metadata
     */
    public function prepare_title($title) {
        $titlefield = self::$mtdstandard->getTitleElement();
        $this->metadatadefines[$titlefield->name.':0_0'] = $title;
        if (!defined('DO_NOT_WRITE')) {
            $this->sharedresourceentry->title = $title;
        }
    }

    /**
     * real wrappers from the CSV file format
     */
    public function prepare_authors($authors) {
        $this->prepare_person($authors, 'author');
    }

    /**
     * real wrappers from the CSV file format
     */
    public function prepare_contributors($authors) {
        $this->prepare_person($authors, 'contributor');
    }

    /**
     * internal generic for any person whatever role
     */
    protected function prepare_person($authors, $role = 'author') {

        $auths = explode(',', $authors);

        $i = 0;

        foreach ($auths as $auth) {
            // Parse author string.
            $auth = trim($auth);

            if (preg_match('/(.*)\s*\((\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})\)\s*/', $auth, $matches)) {
                $person = $matches[1];
                $date = $matches[2];
            } else {
                $person = $auth;
            }

            // Prepare date format.
            if (isset($date)) {
                $date = self::format_date($date);
            } else {
                $date = self::format_date(date('Y-m-d'));
            }

            // Make vcard.
            $vcard = self::build_vcard($person);

            // Create role node.
            $this->metadatadefines["2_3_1:0_{$i}_0"] = $role;

            // Create entity subnodes.
            $this->metadatadefines["2_3_2:0_{$i}_0"] = $vcard;

            // Create date subnodes.
            $this->metadatadefines["2_3_3:0_{$i}_0"] = $date;
            $i++;
        }
    }

    /**
     * Taxonomy must be processed in two steps :
     * 1 - The taxonomy reference must be fed with taxonomy entries
     * 2 - Some metadata entries must be prepared for the imported resource
     * this is done accordingly to what has been configured in classifarray in shared resource site configuration
     */
    public function prepare_taxonomy($taxons) {
        global $CFG, $DB;

        $config = get_config('local_sharedresources');

        $classifconfig = unserialize(get_config(null, 'classifarray'));

        if (empty($classifconfig)) {
            return;
        }

        // First ensure taxonomy items are in taxonomy table.

        /*
         * TODO : Change to a purpose driven classifarray storage
         * in the future, we should define one classification definition per purpose
         */
        $classifkeys = array_keys($classifconfig);
        $table = array_shift($classifkeys);
        $classif = array_shift($classifconfig);
        $labelfield = $classif['label'];
        $parentfield = $classif['parent'];
        $orderingfield = $classif['ordering'];
        $minordering = $classif['orderingmin'];

        if (empty($config->defaulttaxonomypurposeonimport)) {
            set_config('defaulttaxonomypurposeonimport', 'discipline', 'local_sharedresources');
        }

        $defaultpurpose = $config->defaulttaxonomypurposeonimport;

        $taxonarr = explode(',', $taxons);
        $records = array();

        $hastaxonomy = false;

        for ($i = 0; $i < count($taxonarr); $i++) {

            if ($i == 0) {
                $parent = 0;
            } else {
                $parent = $records[$i - 1]->id;
            }

            $params = array('purpose' => $defaultpurpose, $parentfield => $parent);
            $maxorderingvalue = $DB->get_record($table, $params, "id, MAX({$orderingfield})");
            if ($maxorderingvalue === false) {
                $orderingvalue = ++$maxorderingvalue;
            } else {
                $orderingvalue = $minordering;
            }

            $params = array($labelfield => $taxonarr[$i], $parentfield => $parent, 'purpose' => $defaultpurpose);
            if (!$taxon = $DB->get_record($table, $params)) {
                $taxon = new StdClass();
                $taxon->$labelfield = $taxonarr[$i];
                $taxon->purpose = $defaultpurpose;
                $taxon->$parentfield = $parent;
                $taxon->$orderingfield = $orderingvalue;
                if (!defined('DO_NOT_WRITE')) {
                    $taxon->id = $DB->insert_record($table, $taxon);
                } else {
                    $taxon->id = 0;
                    mtrace('Test mode : Adding taxon '.$taxonarr[$i]."\n");
                }
            }

            $records[$i] = $taxon;

            $hastaxonomy = true;
        }

        // Pursue preparing metadata binding : $records[$i] is the last taxon in the path.
        if ($hastaxonomy) {

            $i = 0;
            // Check if not already available instances in the original sharedresource.
            if (!$this->new) {
                $select = " entry_id = ? AND element LIKE '9_2_1:%' ";
                $params = array($this->sharedresourceentry->id);
                if ($allrecs = $DB->get_records_select('sharedresource_metadata', $select, $params, 'id,element')) {
                    $elementixs = array();
                    foreach ($allrecs as $r) {
                        $elementixs[] = str_replace('9_2_1:0_0_', '', $r->element);
                    }
                    $i = max($elementixs);
                    $i++;
                }
            }

            $this->metadatadefines["9_2_1:0_0_{$i}"] = $defaultpurpose;
            $this->metadatadefines["9_2_1_1:0_0_{$i}_0"] = $taxon->id;
            $this->metadatadefines["9_2_1_2:0_0_{$i}_0"] = $taxon->$labelfield;
        }
    }

    public static function build_vcard($person) {

        $person = trim($person);
        if (preg_match('/(\S+)\s+(.*)/', $person, $matches)) {
            $firstname = $matches[1];
            $lastname = $matches[2];
        } else {
            $lastname = $person;
            $firstname = '';
        }

        $str = "BEGIN:VCARD\n";
        $str .= "VERSION:3.0\n";
        $str .= "N:{$lastname};{$firstname}\n";
        $str .= "FN:{$firstname} {$lastname}\n";
        $str .= "ORG:\n";
        $str .= "TITLE:\n";
        $vcarddate = date('Ymd\This\Z');
        $str .= "REV:{$vcarddate}\n";
        $str .= "END:VCARD\n";

        return $str;
    }

    /**
     * a date format wrapper from csv file to metadata standard formats
     */
    public static function format_date($date) {

        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $date, $matches)) {
            $y = $matches[3];
            $m = $matches[2];
            $d = $matches[1];
            return "$y-$m-$d";
        } else if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $date, $matches)) {
            return $date;
        }
    }

    /**
     * given a resource instance id containing a single zip file,
     * @param int $resourceid
     * @param object $cm
     */
    public function deploy($cm) {
        global $DB, $CFG;

        $context = context_module::instance($cm->id);

        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);

        if (empty($areafiles)) {
            if (defined('CLI_SCRIPT')) {
                mtrace("\tDeploy : Skipping as no files in area");
            }
            return;
        }

        $archivefile = array_pop($areafiles);

        include_once($CFG->libdir.'/filestorage/zip_packer.php');
        $packer = new zip_packer();
        if (defined('CLI_SCRIPT')) {
            mtrace("\nExtracting archive...\n");
        }
        $packer->extract_to_storage($archivefile, $context->id, 'mod_resource', 'content', 0, '/');

        // Pointing to some special file.

        if (empty($this->fd['mainfile'])) {

            if (defined('DEFAULT_MAIN_FILES')) {
                $mainfiles = explode(',', DEFAULT_MAIN_FILES);
                list($filepath, $filename) = $this->find_main_file($archivefile, $mainfiles, $context->id,
                                                                   'mod_resource', 'content', 0, '/');
            }

            if (is_null($filename)) {
                if (defined('CLI_SCRIPT')) {
                    mtrace("\tDeploy : Skipping as no main file in descriptor or no default file found\n");
                }
                return;
            }
        }

        if (is_null($filename)) {
            $filepath = '/'.pathinfo($this->fd['mainfile'], PATHINFO_DIRNAME).'/';
            $filepath = str_replace('//', '/', $filepath);
            $filename = pathinfo($this->fd['mainfile'], PATHINFO_BASENAME);
        }

        // Reset sort order.
        file_reset_sortorder($context->id, 'mod_resource', 'content', 0);
        // Set main file.
        $return = file_set_sortorder($context->id, 'mod_resource', 'content', 0, $filepath, $filename, 1);
    }

    public function pre_process_file($realpath) {
        global $CFG;

        if ($CFG->ostype == 'WINDOWS') {
            $realpath = utf8_decode($realpath);
        }

        if (preg_match('/\.html?$/', $realpath)) {
            $filestream = implode('', file($realpath));
            if (preg_match('/charset=iso-.*/', $filestream)) {
                $this->filter_to_utf8($filestream);
                if ($file = fopen($realpath, 'w')) {
                    fputs($file, $filestream);
                    fclose($file);
                }
            }
        }
    }

    public function filter_to_utf8(&$content) {
        $content = utf8_encode($content);
        $content = preg_replace('/charset=iso-.*"/', 'charset=utf-8"', $content);
    }

    public function find_main_file($archivefile, $mainfiles, $contextid, $component, $filearea, $itemid, $path) {

        $fs = get_file_storage();

        // We seek for no extension here.
        $archivefilename = pathinfo($archivefile->get_filename(), PATHINFO_FILENAME);

        foreach ($mainfiles as $guessname) {
            // This is for something as %FILENAME%.htm pattern f.e.
            $guessname = str_replace('%FILENAME%', $archivefilename, $guessname);
            mtrace("Searching for ... $guessname\n");

            $allarea = $fs->get_area_tree($contextid, $component, $filearea, $itemid);
            $candidate = $this->file_search_rec($allarea, $guessname);
            if ($candidate) {
                // First positive result traps.
                return array($candidate->get_filepath(), $candidate->get_filename());
            }
        }

        return array(null, null);
    }

    protected function file_search_rec($dirstruct, $guessname) {
        mtrace("Searching for ... $guessname in ".$dirstruct['dirname']."\n");
        if (!empty($dirstruct['files'])) {
            foreach ($dirstruct['files'] as $f) {
                $fname = $f->get_filename();
                if ($fname == $guessname) {
                    return $f;
                }
            }
        }

        // If not found in immediate files, search deeper in directories.
        if (!empty($dirstruct['subdirs'])) {
            foreach ($dirstruct['subdirs'] as $dirname => $d) {
                $ret = $this->_file_search_rec($d, $guessname);
                if ($ret) {
                    return $ret; // Trap positive result or continue.
                }
            }
        }

        // If nothing found.
        return null;
    }
}