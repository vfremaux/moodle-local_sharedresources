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
namespace local_sharedresources\importer;

use \context;
use \context_system;
use \context_module;
use \StdClass;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php';
require_once $CFG->dirroot.'/mod/sharedresource/locallib.php';
require_once $CFG->dirroot.'/course/lib.php';

class file_importer_base {

    /**
     * the file descriptor out from data collection. Descriptor is an array of properties
     */
    protected $fd;

    /**
     * An open filehandle for logging purpose.
     */
    protected $logfile;

    /**
     * the created or matched sharedresource entry
     */
    protected $sharedresourceentry;

    protected $new;

    /**
     * the final metadata entries
     */
    protected $metadatadefines;

    /*
     * the metadata active standard (singleton, site wide)
     */
    static protected $mtdstandard;

    /*
     * an internal $classification record used when processing taxon paths
     */
    protected $classif;

    /**
     * Default keymap for common LOM schemas.
     */
    protected $metadatakeymap = array('title' => '1_2:0_0',
                           'language' => '1_3:0_0',
                           'description' => '1_4:0_0',
                           'documenttype' => '1_9:0_0',
                           'documentnature' => '1_10:0_0',
                           'pedagogictype' => '5_2:0_0',
                           'difficulty' => '5_9:0_0',
                           'guidance' => '5_10:0_0',
                           'purpose' => '9_1:0_0',
                           'taxon' => '9_2_2_2:0_0_0_0',
                           'taxonid' => '9_2_2_1:0_0_0_0',
                          );

    /**
     * Processing options comming fro GUI form, or from CLI options
     */
    protected $options;

    public function __construct($descriptor, $options) {
        global $CFG;

        $config = get_config('sharedresource');
        $namespace = $config->schema;
        self::$mtdstandard = sharedresource_get_plugin($namespace);

        $this->fd = $descriptor;
        $this->sharedresourceentry = null;
        $this->metadatadefines = array();
        $this->options = $options;
        $this->new = true;

    }

    public function set_log_file($logfile) {
        $this->logfile = $logfile;
    }

    /**
     * Creates the sharedresource entry or loads an existing one if matches for aggregating metadata to it, and saves
     * physical file everything is in the descriptor. the resource has no title yet, Title comming from aggregated metadata.
     * @param int $contextid the sharing context of the sharedresource. 0 stands for system context.
     */
    public function make_resource_entry($contextid = 0) {
        global $CFG;

        /*
         * first we check we do not have this file yet. We must create a temporary file record for this
         * this will allow us to access all the stored_file API for this file.
         */
        if ($contextid == 0) {
            $context = context_system::instance();
        } else {
            $context = context::instance_by_id($contextid);
        }
        $filerecord = new StdClass();
        $filerecord->contextid = $context->id;
        $filerecord->component = 'mod_sharedresource';
        $filerecord->filearea = 'temp';
        $filerecord->filepath = '/';
        if ($CFG->ostype == 'WINDOWS' && !$this->options['nativeutf8']) {
            $filerecord->filename = utf8_decode(pathinfo($this->fd['fullpath'], PATHINFO_BASENAME));
        } else {
            $filerecord->filename = pathinfo($this->fd['fullpath'], PATHINFO_BASENAME);
        }
        $filerecord->itemid = 0;

        $this->pre_process_file($this->fd['fullpath']);

        $fs = get_file_storage();

        if (!$storedfile = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea,
                                          $filerecord->itemid, $filerecord->filepath, $filerecord->filename)) {
            if (empty($this->simulate)) {
                if ($CFG->ostype == 'WINDOWS' && !$this->options['nativeutf8']) {
                    $storedfile = $fs->create_file_from_pathname($filerecord, utf8_encode(trim($this->fd['fullpath'])));
                } else {
                    $storedfile = $fs->create_file_from_pathname($filerecord, trim($this->fd['fullpath']));
                }
            } else {
                $message = "Test mode : File resource $filerecord->contextid, $filerecord->component, ";
                $message .= "$filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename created";
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }
        }

        // Now check we do not have it yet in the library? If we do, we load the library entry and we continue.
        $newidentifier = $storedfile->get_contenthash();
        if (!$this->sharedresourceentry = \mod_sharedresource\entry::get_by_identifier($newidentifier)) {

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
            $sharedresourceentry->title = '';
            $sharedresourceentry->context = $context->id;
            $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
            $this->sharedresourceentry = new $entryclass($sharedresourceentry);
            $this->sharedresourceentry->storedfile = $storedfile;

            // This is a default title (with a bit formatting) that can be overriden by explicit metadata.
            if (!array_key_exists('title', $this->fd)) {
                $this->fd['title'] = str_replace('_', ' ', $storedfile->get_filename());
            }

        } else {
            $this->new = false;
        }
        $message = 'Sharedresource entry prepared for '.$storedfile->get_filepath().$storedfile->get_filename();
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
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

            /*
             * We can defer any metadata preparation to an external handler. This is needed when the metadata
             * processing is not trivial. F.e, when binding to a taxon, several metadata must nbe setup in
             * accordance. when receiving a Author data, VCard must be built. etc.
             */

            // some key may have indexed instances. Just use one single handler for all.
            $reducedinputkey = preg_replace('/\d+$/', '', $inputkey);

            if (method_exists('\\local_sharedresources\\importer\\file_importer_base', 'prepare_'.$reducedinputkey)) {
                $f = 'prepare_'.$reducedinputkey;
                mtrace('Preparing '.$inputkey.' using preparation handler');
                $fd[$inputkey] = $this->$f($inputvalue, $inputkey);
            } else {
                // Default case.
                // Or we just transfer the value into metadata stub after keymapping to Dublin Core node identifier.
                if (array_key_exists($inputkey, $this->metadatakeymap)) {
                    $instancekey = $this->metadatakeymap[$inputkey];
                    list ($nodekey, $instance) = explode(':', $instancekey);
                    // This adapts to really used metadata standard, whatever rich the metadata.csv file is.
                    if (self::$mtdstandard->hasNode($nodekey)) {
                        $this->metadatadefines[$instancekey] = $inputvalue;
                    }
                }
            }
        }
    }

    /**
     * Aggregates metadata along within sharedresource entry.
     */
    public function aggregate_metadata() {

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        // We do not have correct sharedresource entry to attach metadata to.
        if (empty($this->sharedresourceentry)) {
            $message = "No sharedresource object created. Sipping metadata";
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }
            return;
        }

        if (!empty($this->metadatadefines)) {
            foreach ($this->metadatadefines as $node => $mtdentry) {
                if (empty($this->options['simulate'])) {
                    $this->sharedresourceentry->add_element($node, $mtdentry, $namespace);
                    $message = "Adding MTD : $node, $mtdentry";
                    mtrace($message);
                } else {
                    $message = "SIMUL : Adding MTD : $node, $mtdentry";
                    mtrace($message);
                    if ($this->logfile) {
                        fputs($this->logfile, $message);
                    }
                }
            }
        } else {
            mtrace("empty metadatas");
        }
    }

    public function save() {
        if (empty($this->options['simulate'])) {
            if ($this->new == true) {
                $this->sharedresourceentry->add_instance();
            } else {
                $this->sharedresourceentry->update_instance();
            }
            $this->mark_file_imported($this->fd['fullpath']);
            $message = "Saving....\n";
        } else {
            $message = "SIMUL : Saving....\n";
        }
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
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

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        if (empty($this->fd['shortname'])) {
            return;
        }

        if (!$course = $DB->get_record('course', array('shortname' => $this->fd['shortname']))) {
            $message = 'Unexisting course '.$this->fd['shortname'].' for attachement. Skipping....';
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }
            return;
        }

        if (!empty($this->options['simulate'])) {
            $message = 'SIMUL : attaching to '.$this->fd['shortname'];
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }
            return;
        }

        $message = 'attaching to '.$this->fd['shortname'];
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
        }
        $sectionnum = (!empty($this->fd['section'])) ? $this->fd['section'] : 0;

        $visible = (isset($this->fd['visible'])) ? $this->fd['visible'] : 1;

        /*
         * if we ever have collected pedagogic description as a guidance, and we want to make
         * automatically labels from them...
         */
        $guidance = $this->sharedresourceentry->element('5_10:0_0', $namespace);
        if ($this->options['makelabelsfromguidance'] && $guidance) {
            $this->add_label_to_section($guidance, $course, $sectionnum, $visible);
        }

        $instance = new \mod_sharedresource\base(0, $this->sharedresourceentry->identifier);
        $instance->options = 0;
        $instance->popup = 0;
        $instance->name = $this->sharedresourceentry->title;
        $instance->intro = $this->sharedresourceentry->description;
        $instance->introformat = FORMAT_MOODLE;
        $instance->course = $course->id;
        $instance->alltext = '';
        if (empty($this->options['simulate'])) {
            $message = 'Adding base resource module ';
            $instance->id = $instance->add_instance();
        } else {
            $message = 'SIMUL : Adding base resource module ';
        }
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
        }

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
        if (empty($this->options['simulate'])) {
            if (!$cm->id = add_course_module($cm)) {
                $message = get_string('errorcmaddition', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }

            if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)) {
                $message = get_string('errorsectionaddition', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }

            $context = context_module::instance($cm->id);

            // Now we have the real value for the $cm->section.
            if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
                $message = get_string('errorcmsectionbinding', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }
        } else {
            $message = 'SIMUL : Adding course module to course ';
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }
        }

        // We can post process a resource conversion when everything is clear.
        if ((!empty($this->fd->coursemoduletype) && $this->fd->coursemoduletype == 'resource') ||
                !empty($this->options['converttoresource'])) {
            if (empty($this->options['simulate'])) {
                $message = "Converting to legacy resource";
                $instanceid = sharedresource_convertfrom($instance, false);
            } else {
                $message = "SIMUL : Converting to legacy resource";
            }
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }

            // We can autodeploy zips if required.
            if (!empty($this->options['deployzips']) && preg_match('/\.zip$/', $this->fd['file'])) {
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

        if (empty($this->options['simulate'])) {
            $instance->id = $DB->insert_record('label', $instance);
            $message = "Adding guidance label to section";
        } else {
            $instance->id = 0;
            $message = "SIMUL : Adding guidance label to section";
        }
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
        }

        // Make a new course module.
        $module = $DB->get_record('modules', array('name' => 'label'));
        $cm = new StdClass;
        $cm->instance = $instance->id;
        $cm->module = $module->id;
        $cm->visible = $visible;
        $cm->course = $course->id;
        $cm->section = 1;

        // Insert the course module in course.
        if (empty($this->options['simulate'])) {
            if (!$cm->id = add_course_module($cm)) {
                $message = get_message('errorcmaddition', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }

            // Reset the course modinfo cache.
            $DB->set_field('course', 'modinfo', '', array('id' => $course->id));

            if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)) {
                $message = get_string('errorsectionaddition', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }

            if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
                $message = get_string('errorcmsectionbinding', 'sharedresource');
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
                return;
            }
            $message = "Added Label course module $cm->id in section $sectionid";
        } else {
            $message = "SIMUL : Added Label course module $cm->id in section $sectionid";
        }

        if ($this->logfile) {
            fputs($this->logfile, $message);
        }
        mtrace($message);
    }

    /**
     * this function should be overriden by more specific format dedicated
     * subclass if any metadata can be guessed from the file content itself
     */
    public function get_metadata_from_file() {
        return;
    }

    /* * static handlers for metadata inputs * */

    // From a list of keywords, build a set of metadata nodes.
    public function prepare_keywords($keywords) {
        $kws = explode(',', $keywords);
        $standardelm = self::$mtdstandard->getKeywordElement();
        $i = 0;
        foreach ($kws as $kw) {
            $kw = trim($kw);
            $this->metadatadefines[$standardelm->node.':0_'.$i] = $kw;
            $i++;
        }
    }

    /**
     * real wrappers from the CSV file format
     * add both in sharedresource record AND metadata
     */
    public function prepare_description($description) {

        // Filter out some Excel textual formatting.
        $description = str_replace('""', '"', $description);
        $description = preg_replace('/^"|"$/', '', $description);

        $standardelm = self::$mtdstandard->getDescriptionElement();
        $this->metadatadefines[$standardelm->node.':0_0'] = $description;
        if (empty($this->options['simulate'])) {
            // We must simulate htmleditor return.
            $this->sharedresourceentry->description = $description;
        }
    }

    /**
     * real wrappers from the CSV file format
     * add both in sharedresource record AND metadata
     */
    public function prepare_title($title) {

        // Filter out some Excel textual formatting.
        $title = str_replace('""', '"', $title);
        $title = preg_replace('/^"|"$/', '', $title);

        $standardelm = self::$mtdstandard->getTitleElement();
        $this->metadatadefines[$standardelm->node.':0_0'] = $title;
        $this->sharedresourceentry->title = $title;
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
     * @param string $authors a comma separated list of authornames followed by date in DD/MM/YYYY or YYYY-MM-DD format.
     * @return void. Fills result directly into metadatadefines array.
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
     * special handler when binding entries to existing taxons (We cannot create here with only a label name). Taxon key
     * comes as potentially indexes if multitaxon binding is required by the csv file.
     */
    protected function prepare_taxon($taxonname, $inputkey) {
        global $DB;

        if (!$taxon = $DB->get_record('sharedresource_taxonomy', array('value' => $taxonname))) {
            // No match. Ignore.
            return;
        }
        return $this->bind_taxon($taxon, $inputkey);
    }

    /**
     * special handler when binding entries to existing taxons (We cannot create here with only a label name). Taxon key
     * commes as potentially indexes if multitaxon binding is required by the csv file.
     */
    protected function prepare_taxonid($taxonname, $inputkey) {
        global $DB;

        if (!$taxon = $DB->get_record('sharedresource_taxonomy', array('idnumber' => $taxonidnumber))) {
            // No match. Ignore.
            return;
        }
        return $this->bind_taxon($taxon, $inputkey);
    }

    /**
     * Do real work :
     * - bind internal id to node 9_2_2_1
     * - bind taxon value to node 9_2_2_2
     * - bind taxon classification source id to node 9_2_1
     *
     * Use input key index to instanciate the node instances
     *
     * Knownclassifs static will hold the mapping of addressed classifications (sources) to
     * instance branch index, starting from 0.
     */
    protected function bind_taxon($taxon, $inputkey) {
        static $knownclassifs = array();
        static $lastsourceindex = 0;
        static $lasttaxonindexes = array();

        preg_match('/(\d*)$/', $inputkey, $matches);
        if (empty($matches[1])) {
            $index = 0;
        } else {
            $index = $matches[1] - 1;
        }

        // Register classification, starting from 0.
        if (!array_key_exists($taxon->classificationid, $knownclassifs)) {
            $knownclassifs[$taxon->classificationid] = $lastsourceindex;
            $lastsourceindex++;
        }

        $s = $knownclassifs[$taxon->classificationid];

        if (!array_key_exists($s, $lasttaxonindexes)) {
            $lasttaxonindexes[$s] = 0;
        }

        $t = $lasttaxonindexes[$s];
        $lasttaxonindexes[$s]++;

        /*
         * compute instances :
         * 9_2_1 : 0_<s>_<t>
         * 9_2_2_2 : 0_<s>_<t>_0
         * 9_2_2_1 : 0_<s>_<t>_0
         */

        $sourceelement = '9_2_1:0_'.$s.'_'.$t;
        $taxonidelement = '9_2_2_1:0_'.$s.'_'.$t.'_0';
        $taxonvalueelement = '9_2_2_2:0_'.$s.'_'.$t.'_0';

        $this->metadatadefines[$sourceelement] = $taxon->classificationid;
        $this->metadatadefines[$taxonidelement] = $taxon->id;
        $this->metadatadefines[$taxonvalueelement] = $taxon->value;
    }

    /**
     * Processes a list of taxonpaths, creating missing taxon elements and registering metadata bindings.
     * @param string $taxonpathlist a double column ('::') list of taxons paths.
     *
     * @see lib.php ง949 where the 'taxonomy' attribute is computed from the metadata file or the path.
     */
    public function prepare_taxonomy($taxonpathlist) {
        global $DB;

        $config = get_config('local_sharedresources');

        $taxumarray = self::$mtdstandard->getTaxumpath();
        if (!$taxumarray) {
            // If current metadata model has no taxonomy metadata.
            return;
        }

        $this->classif = $DB->get_record('sharedresource_classif', array('id' => $this->options['taxonomy']));

        $taxonpaths = explode('::', $taxonpathlist);

        if (empty($taxonpaths)) {
            return;
        }

        foreach ($taxonpaths as $taxonpath) {
            $this->prepare_taxon_path($taxonpath);
        }
    }

    /**
     * one taxon path in processed in two steps :
     * 1 - The taxonomy reference must be fed with taxonomy entries
     * 2 - Some metadata entries must be prepared for the imported resource
     * @param string $taxons a taxon path as slashed separated path element list.
     *
     * @see lib.php ยง804 where the 'taxonomy' attribute is computed from the metadata file or the path.
     */
    protected function prepare_taxon_path($taxons) {
        global $CFG, $DB;
        static $taxumarray;

        if (is_null($taxumarray)) {
            $taxumarray = self::$mtdstandard->getTaxumpath();
        }

        // Proxy the member for clarity.
        $classif = $this->classif;

        /*
         * in the future, we should define one classification definition per purpose
         */
        $table = $classif->tablename;
        $labelfield = $classif->sqllabel;
        $parentfield = $classif->sqlparent;
        $orderingfield = $classif->sqlsortorder;
        $minordering = $classif->sqlsortorderstart;

        $taxonarr = explode('/', $taxons);
        $records = array();

        $hastaxonomy = false;
        $taxonpath = '';

        for ($i = 0; $i < count($taxonarr); $i++) {
            // Prepare each taxon.

            if ($i == 0) {
                $parent = 0;
            } else {
                $parent = $records[$i - 1]->id;
            }

            $params = array();
            $selects = array();
            // TODO : purpose may not be generic on other taxonomy tables. Protect this.
            if ($table == 'sharedresource_taxonomy') {
                $params = array($classif->id);
                $selects[] = ' classificationid = ? ';
            }

            $params[] = $parent;
            $selects[] = " $parentfield = ? ";

            if (!empty($classif->sqlrestriction)) {
                $selects[] = $classif->sqlrestriction;
            }

            $select = implode(' AND ', $selects);
            $maxorderingvalue = $DB->get_record_select($table, $select, $params, "id, MAX({$orderingfield}) as max");
            if ($maxorderingvalue === false) {
                $orderingvalue = 0;
            } else {
                $orderingvalue = $maxorderingvalue->max + 1;
            }

            // Add the taxon text check to the previous SQL parameters.
            $selects[] = " $labelfield = ? ";
            $params[] = trim($taxonarr[$i]);

            $select = implode(' AND ', $selects);
            if (!$taxon = $DB->get_record_select($table, $select, $params)) {
                $taxon = new StdClass();
                $taxon->$labelfield = trim($taxonarr[$i]);
                if ($table == 'sharedresource_taxonomy') {
                    $taxon->classificationid = $classif->id;
                }
                $taxon->$parentfield = $parent;
                $taxon->$orderingfield = $orderingvalue;
                if (empty($this->options['simulate'])) {
                    $taxon->id = $DB->insert_record($table, $taxon);
                    $message = 'Adding taxon '.trim($taxonarr[$i])." to taxonomy.\n";

                    // If restriction is based on explicit taxon selection, add the new taxon to selection.
                    if (!empty($classif->taxonselection)) {
                        $classif->taxonselection .= ','.$taxon->id;
                        $DB->set_field('sharedresource_classif', 'taxonselection', $classif->taxonselection);
                    }
                } else {
                    $taxon->id = 0;
                    $message = 'SIMUL : Adding taxon '.$taxonarr[$i]."\n";
                }
                mtrace($message);
                if ($this->logfile) {
                    fputs($this->logfile, $message);
                }
            } else {
                    $message = 'Taxon already exist for "'.trim($taxonarr[$i])."\". Using it. \n";
                    mtrace($message);
                    if ($this->logfile) {
                        fputs($this->logfile, $message);
                    }
            }

            $records[$i] = $taxon;
            $taxonpath .= $taxon->id.'/';

            $hastaxonomy = true;
        }

        // Pursue preparing metadata binding : $records[$i] is the last taxon in the path.
        if ($hastaxonomy) {

            $i = 0;
            // Check if not already available instances in the original sharedresource.
            if (!$this->new) {
                // Find the highest instanceid occurrence in taxonomy records.
                $taxonpathnodeid = $taxumarray['main'];
                $select = "
                    entryid = ? AND
                    element LIKE '{$taxonpathnodeid}:%'
                ";
                $params = array($this->sharedresourceentry->id);
                if ($allrecs = $DB->get_records_select('sharedresource_metadata', $select, $params, 'id, element')) {
                    $elementixs = array();
                    foreach ($allrecs as $r) {
                        $elementixs[] = str_replace("{$sourcenodeid}:0_0_", '', $r->element);
                    }
                    $i = max($elementixs);
                    $i++;
                }
            }

            $sourcenodeid = $taxumarray['source'];
            $idnodeid = $taxumarray['id'];
            $entrynodeid = $taxumarray['entry'];
            $this->metadatadefines["{$sourcenodeid}:0_0_{$i}"] = $classif->id;
            $this->metadatadefines["{$idnodeid}:0_0_{$i}_0"] = $taxonpath;
            $this->metadatadefines["{$entrynodeid}:0_0_{$i}_0"] = $taxon->$labelfield;
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

        $config = get_config('local_sharedresources');

        $context = context_module::instance($cm->id);

        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);

        if (empty($areafiles)) {
            $message = "\tDeploy : Skipping as no files in area";
            mtrace($message);
            if ($this->logfile) {
                fputs($this->logfile, $message);
            }
            return;
        }

        $archivefile = array_pop($areafiles);

        include_once($CFG->libdir.'/filestorage/zip_packer.php');
        $packer = new zip_packer();

        if (empty($this->options['simulate'])) {
            $message = "\nExtracting archive...\n";

            $packer->extract_to_storage($archivefile, $context->id, 'mod_resource', 'content', 0, '/');

            // Pointing to some special file.

            if (empty($this->fd['mainfile'])) {

                if ($config->defaultmainfiles) {
                    $mainfiles = explode(',', $config->defaultmainfiles);
                    list($filepath, $filename) = $this->find_main_file($archivefile, $mainfiles, $context->id,
                                                                       'mod_resource', 'content', 0, '/');
                }

                if (is_null($filename)) {
                    $message .= "\tDeploy : Skipping as no main file in descriptor or no default file found\n";
                    mtrace($message);
                    if ($this->logfile) {
                        fputs($this->logfile, $message);
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
        } else {
            $message = "SIMUL : Deploying archive... \n";
        }

        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
        }
    }

    public function pre_process_file($realpath) {
        global $CFG;

        if ($CFG->ostype == 'WINDOWS' && !$this->options['nativeutf8']) {
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
            $message = "Searching for ... $guessname\n";
            mtrace($message);

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
        $message = "Searching for ... $guessname in ".$dirstruct['dirname']."\n";
        mtrace($message);
        if ($this->logfile) {
            fputs($this->logfile, $message);
        }
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

    /**
     * Renames an imported file so it would not be imported twice when
     * replaying an import.
     */
    public function mark_file_imported($upath) {
        global $CFG;

        if ($CFG->ostype == 'WINDOWS' && !$this->options['nativeutf8']) {
            $path = utf8_decode($upath);
        } else {
            $path = $upath;
        }

        $parts = pathinfo($path);
        $newname = $parts['dirname'].'/__'.$parts['basename'];
        rename($path, $newname);
    }
}