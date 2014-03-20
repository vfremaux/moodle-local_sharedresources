<?php

/**
* an abstract class that represents a single file importer
*
*/
require_once $CFG->dirroot.'/mod/sharedresource/sharedresource_metadata.class.php';
require_once $CFG->dirroot.'/mod/sharedresource/locallib.php';
require_once $CFG->dirroot.'/course/lib.php';

class file_importer_base{

	/**
	* the file descriptor out from data collection. Descriptor is an array of properties
	*/
	var $fd;

	/**
	* the created or matched sharedresource entry
	*/
	var $sharedresourceentry;

	var $new;

	/**
	* the final metadata entries
	*/
	var $METADATA;
	
	static protected $mtdstandard;
	
	var $MTDKEYMAP = array('title' => '1_2:0_0', 
						   'language' => '1_3:0_0',
						   'description' => '1_4:0_0',
						   'documenttype' => '1_9:0_0',
						   'documentnature' => '1_10:0_0',
						   'pedagogictype' => '5_2:0_0',
						   'difficulty' => '5_9:0_0',
						   'guidance' => '5_10:0_0',
						  );

	function __construct($descriptor){
		global $CFG;
		
		$this->fd = $descriptor;
		$this->sharedresourceentry = null;
		$this->METADATA = array();
		$this->new = true;
				
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
		if (empty(self::$mtdstandard)){
			self::$mtdstandard = new $object;
		}
	}

	/** creates the sharedresource entry or loads an existing one if matches for aggregating metadata to it, and saves physical file 
	* everything is in the descriptor
	* @param int $context the sharing context of the sharedresource
	*/
	function make_resource_entry($context = 1){
		global $CFG;
		
		// first we check we do not have this file yet. We must create a temporary file record for this
		// this will allow us to access all the stored_file API for this file.
		$systemcontext = context_system::instance();
		$filerecord = new StdClass();
		$filerecord->contextid = $systemcontext->id;
		$filerecord->component = 'mod_sharedresource';
		$filerecord->filearea = 'temp';
		$filerecord->filepath = '/';
		if ($CFG->ostype == 'WINDOWS'){
			$filerecord->filename = utf8_decode(pathinfo($this->fd['fullpath'], PATHINFO_BASENAME));
		} else {
			$filerecord->filename = pathinfo($this->fd['fullpath'], PATHINFO_BASENAME);
		}
		$filerecord->itemid = 0;
		
		$this->pre_process_file($this->fd['fullpath']);
		
		$fs = get_file_storage();

		if (!$stored_file = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename)){
			if (!defined('DO_NOT_WRITE')){
				if ($CFG->ostype == 'WINDOWS'){
					$stored_file = $fs->create_file_from_pathname($filerecord, utf8_decode(trim($this->fd['fullpath'])));
				} else {
					$stored_file = $fs->create_file_from_pathname($filerecord, trim($this->fd['fullpath']));
				}
			} else {
				if (defined('CLI_SCRIPT')) mtrace("Test mode : File resource $filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename created");
				return;
			}
		}

		// now check we do not have it yet in the library? If we do, we load the library entry and we continue.
		$newidentifier = $stored_file->get_contenthash();
		if (!$this->sharedresourceentry = sharedresource_entry::get_by_identifier($newidentifier)){
			
			// if not in library, 
			$sharedresourceentry = new StdClass();
	        $sharedresourceentry->type = 'file';
	        // is this a local resource or a remote one?
            // if resource uploaded then move to temp area until user has
            //save the file 
            $sharedresourceentry->identifier = $stored_file->get_contenthash();
            $sharedresourceentry->file = $stored_file->get_id();
            $sharedresourceentry->identifier = $newidentifier;
            $sharedresourceentry->url = '';
            $sharedresourceentry->context = $context;
			$this->sharedresourceentry = new sharedresource_entry($sharedresourceentry);
			$this->sharedresourceentry->storedfile = $stored_file;

			// this is a default title (with a bit formatting) that can be overriden by explicit metadata 
			if (!array_key_exists('title', $this->fd)){
				$this->fd['title'] = str_replace('_', ' ', $stored_file->get_filename());
			}
			
	    } else {
	    	$this->new = false;
	    }
		if (defined('CLI_SCRIPT')) mtrace('Sharedresource entry prepared for '.$stored_file->get_filepath().'/'.$stored_file->get_filename());
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
	function metadata_preprocess(){
		foreach($this->fd as $inputkey => $inputvalue){
			// we can defer all metadata preparation to an external handler.
			if (method_exists('file_importer_base', 'prepare_'.$inputkey)){
				$f = 'prepare_'.$inputkey;
				$fd[$inputkey] = $this->$f($inputvalue);
			} else {
				// or we just transfer the value into metadata stub after keymapping to Dublin Core node identifier.
				if (array_key_exists($inputkey, $this->MTDKEYMAP)){
					$instancekey = $this->MTDKEYMAP[$inputkey];
					list ($nodekey, $instance) = explode(':', $instancekey);
					// this adapts to really used metadata standard, whatever rich the metadata.csv file is.
					if (self::$mtdstandard->hasNode($nodekey)){
						$this->METADATA[$instancekey] = $inputvalue;
					}
				}
			}
		}
	}

	// aggregates metadata along within sharedresource entry	
	function aggregate_metadata(){
		global $CFG;
		
		// we do not have correct sharedresource entry to attach metadata to.
		if (empty($this->sharedresourceentry) && !defined('DO_NOT_WRITE')) return;
				
		if (!empty($this->METADATA)){
			foreach($this->METADATA as $node => $mtdentry){
				if (!defined('DO_NOT_WRITE')){
					$this->sharedresourceentry->add_element($node, $mtdentry, $CFG->pluginchoice);
				} else {
					mtrace("Test mode : Adding MTD : $node, $mtdentry");
				}
			}
		}
	}
	
	function save(){
		if (!defined('DO_NOT_WRITE')){
			if ($this->new == true){
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
	function attach(){
		global $DB, $CFG;
		
		if (empty($this->fd['shortname'])) {
			return;
		}

		if (!$course = $DB->get_record('course', array('shortname' => $this->fd['shortname']))){
			mtrace('Unexisting course '.$this->fd['shortname'].' for attachement. Skipping....');
			return;
		}

		if (defined('DO_NOT_WRITE')){
			if (defined('CLI_SCRIPT')) mtrace('Test mode : attaching to '.$this->fd['shortname']);
			return;			
		}
				
		if (defined('CLI_SCRIPT')) mtrace('attaching to '.$this->fd['shortname']);
		$sectionnum = (!empty($this->fd['section'])) ? $this->fd['section'] : 0 ;

		$visible = (isset($this->fd['visible'])) ? $this->fd['visible'] : 1 ;

		// if eve we have collected pédagogic description as a guidance, and we want to make
		// automatically labels from them...
		$guidance = $this->sharedresourceentry->element('5_10:0_0', $CFG->pluginchoice);
		if(defined('MAKE_LABELS_FROM_GUIDANCE') && $guidance){
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

	    // make a new course module for the initial sharedresource instanceid
	    $module = $DB->get_record('modules', array('name'=> 'sharedresource'));
	    $cm = new StdClass;
	    $cm->instance = $instance->id;
	    $cm->module = $module->id;
	    $cm->course = $course->id;
	    $cm->visible = $visible;
	    $cm->visibleold = $visible;
	    $cm->section = 1; // this is fake ! will be postfed after reals section number is known
	
	    /// remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
	    // valid also if LTI tool
	    if (!empty($this->sharedresourceentry->remoteid)){
	        $cm->idnumber = $this->sharedresourceentry->remoteid;
	    }
	
	    // insert the course module in course
	    if (!$cm->id = add_course_module($cm)){
	        print_error('errorcmaddition', 'sharedresource');
	    }
	    
	    if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)){
	        print_error('errorsectionaddition', 'sharedresource');
	    }
	    
	    $context = context_module::instance($cm->id);
	    
		// now we have the real value for the $cm->section	
	    if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
	        print_error('errorcmsectionbinding', 'sharedresource');
	    }	    

		// we can post process a resource conversion when everything is clear
		if ((!empty($this->fd->coursemoduletype) && $this->fd->coursemoduletype == 'resource') || defined('CONVERT_TO_RESOURCE')){
	        if (defined('CLI_SCRIPT')) mtrace("Converting to legacy resource");
	        $instanceid = sharedresource_convertfrom($instance, false);

			// We can autodeploy zips if required
			if (defined('AUTO_DEPLOY') && preg_match('/\.zip$/', $this->fd['file'])){
				$this->deploy($cm);
			}
	    }

	    // reset the course modinfo cache for rebuilding it all
	    $course->modinfo = null;
	    $DB->update_record('course', $course);
		
	}
	
	/**
	* makes a label course module and add it to section
	* @param string $guidance a guidance text 
	* @param int $courseid The course ID
	* @param int $sectionnum relative section number in course
	*/
	function add_label_to_section($guidance, $course, $sectionnum, $visible = 1){
		global $DB;
		
		$instance = new StdClass;
		$instance->course = $course->id;
		$instance->name = shorten_text($guidance, 200);
		$instance->intro = '<p>'.$guidance.'</p>';
		$instance->introformat = 1;
		$instance->timemodified = time();
		
		$instance->id = $DB->insert_record('label', $instance);
		
	    // make a new course module
	    $module = $DB->get_record('modules', array('name' => 'label'));
	    $cm = new StdClass;
	    $cm->instance = $instance->id;
	    $cm->module = $module->id;
	    $cm->visible = $visible;
	    $cm->course = $course->id;
	    $cm->section = 1;
		
	    // insert the course module in course
	    if (!$cm->id = add_course_module($cm)){
	        print_error('errorcmaddition', 'sharedresource');
	    }
	    
	    // reset the course modinfo cache
	    $DB->set_field('course', 'modinfo', '', array('id' => $course->id));
	
	    if (!$sectionid = course_add_cm_to_section($course, $cm->id, $sectionnum)){
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
	function get_metadata_from_file(){
	}
	
	/** static handlers for metadata inputs **/
	
	// from a list of keywords, build a set of metadata nodes
	function prepare_keywords($keywords){
		$kws = explode(',', $keywords);
		$kwfield = self::$mtdstandard->getKeywordElement();
		$i = 0;
		foreach($kws as $kw){
			$kw = trim($kw);
			$this->METADATA[$kwfield->name.':0_'.$i] = $kw;
			$i++;
		}
	}

	/** 
	* real wrappers from the CSV file format
	* add both in sharedresource record AND metadata
	*/
	function prepare_description($description){
		$descfield = self::$mtdstandard->getDescriptionElement();
		$this->METADATA[$descfield->name.':0_0'] = $description;
		if (!defined('DO_NOT_WRITE')){
			$this->sharedresourceentry->description = $description; // we must simulate htmleditor return
		}
	}

	/** 
	* real wrappers from the CSV file format
	* add both in sharedresource record AND metadata
	*/
	function prepare_title($title){
		$titlefield = self::$mtdstandard->getTitleElement();
		$this->METADATA[$titlefield->name.':0_0'] = $title;
		if (!defined('DO_NOT_WRITE')){
			$this->sharedresourceentry->title = $title;
		}
	}

	/** 
	* real wrappers from the CSV file format
	*/
	function prepare_authors($authors){
		$this->prepare_person($authors, 'author');
	}

	/** 
	* real wrappers from the CSV file format
	*/
	function prepare_contributors($authors){
		$this->prepare_person($authors, 'contributor');
	}

	/**
	* internal generic for any person whatever role
	*/
	protected function prepare_person($authors, $role = 'author'){
		
		$auths = explode(',', $authors);
		
		$i = 0;
		
		foreach($auths as $auth){
			// parse author string
			$auth = trim($auth);
			
			if (preg_match('/(.*)\s*\((\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})\)\s*/', $auth, $matches)){
				$person = $matches[1];
				$date = $matches[2];
			} else {
				$person = $auth;
			}
			
			// prepare date format
			if(isset($date)){
				$date = self::format_date($date);
			} else {
				$date = self::format_date(date('Y-m-d'));
			}
			
			// make vcard
			$vcard = self::build_vcard($person);
						
			// create role node
			$this->METADATA["2_3_1:0_{$i}_0"] = $role;

			// create entity subnodes
			$this->METADATA["2_3_2:0_{$i}_0"] = $vcard;

			// create date subnodes
			$this->METADATA["2_3_3:0_{$i}_0"] = $date;
			$i++;
		}
	}

	/** 
	* Taxonomy must be processed in two steps : 
	* 1 - The taxonomy reference must be fed with taxonomy entries
	* 2 - Some metadata entries must be prepared for the imported resource
	* this is done accordingly to what has been configured in classifarray in shared resource site configuration
	*/
	function prepare_taxonomy($taxons){
		global $CFG, $DB;
		
		$classifconfig = unserialize(get_config(NULL, 'classifarray'));
		
		if (empty($classifconfig)) return;
		
		// first ensure taxonomy items are in taxonomy table 
				
		// TODO : Change to a purpose driven classifarray storage 
		// in the future, we should define one classification definition per purpose
		$classifkeys = array_keys($classifconfig);
		$table = array_shift($classifkeys);
		$classif = array_shift($classifconfig);
		$labelfield = $classif['label'];
		$parentfield = $classif['parent'];
		$orderingfield = $classif['ordering'];
		$minordering = $classif['orderingmin'];

		if (empty($CFG->defaulttaxonomypurposeonimport)){
			set_config('defaulttaxonomypurposeonimport', 'discipline');
		}		
		$defaultpurpose = $CFG->defaulttaxonomypurposeonimport;

		$taxonarr = explode(',', $taxons);
		$records = array();
		
		$hastaxonomy = false;
	
		for($i = 0; $i < count($taxonarr); $i++){

			if ($i == 0){
				$parent = 0;
			} else {
				$parent = $records[$i - 1]->id;
			}

			$maxorderingvalue = $DB->get_record($table, array('purpose' => $defaultpurpose, $parentfield => $parent), "id, MAX({$orderingfield})");
			if ($maxorderingvalue === false){
				$orderingvalue = ++$maxorderingvalue;
			} else {
				$orderingvalue = $minordering;
			}

			if (!$taxon = $DB->get_record($table, array($labelfield => $taxonarr[$i], $parentfield => $parent, 'purpose' => $defaultpurpose))){
				$taxon = new StdClass();
				$taxon->$labelfield = $taxonarr[$i];
				$taxon->purpose = $defaultpurpose;
				$taxon->$parentfield = $parent;
				$taxon->$orderingfield = $orderingvalue;
				if (!defined('DO_NOT_WRITE')){
					$taxon->id = $DB->insert_record($table, $taxon);
				} else {
					$taxon->id = 0;
					mtrace('Test mode : Adding taxon '.$taxonarr[$i]."\n");
				}
			}
			
			$records[$i] = $taxon;

			$hastaxonomy = true;
		}
		
		// pursue preparing metadata binding : $records[$i] is the last taxon in the path
		if ($hastaxonomy){
			
			$i = 0;
			// check if not already available instances in the original sharedresource
			if (!$this->new){
				if ($allrecs = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND element LIKE '9_2_1:%' ", array($this->sharedresourceentry->id), 'id,element')){
					$elementixs = array();
					foreach($allrecs as $r){
						$elementixs[] = str_replace('9_2_1:0_0_', '', $r->element);						
					}
					$i = max($elementixs);
					$i++;
				}
			}
			
			$this->METADATA["9_2_1:0_0_{$i}"] = $defaultpurpose;
			$this->METADATA["9_2_1_1:0_0_{$i}_0"] = $taxon->id;
			$this->METADATA["9_2_1_2:0_0_{$i}_0"] = $taxon->$labelfield;
		}
	}

	static function build_vcard($person){
		
		$person = trim($person);
		if (preg_match('/(\S+)\s+(.*)/', $person, $matches)){
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
	*
	*/
	static function format_date($date){
		
		if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $date, $matches)){
			$y = $matches[3];
			$m = $matches[2];
			$d = $matches[1];
			return "$y-$m-$d";
		}

		elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $date, $matches)){
			return $date;
		}		
	}
	
	/**
	* given a resorce instance id containing a single zip file, 
	* @param int $resourceid
	* @param object $cm
	*/
	function deploy($cm){
		global $DB, $CFG;
				
		$context = context_module::instance($cm->id);
		
		$fs = get_file_storage();
		
		$areafiles = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);
		
		if (empty($areafiles)) {
			if (defined('CLI_SCRIPT')) mtrace("\tDeploy : Skipping as no files in area");
			return;
		}

		$archivefile = array_pop($areafiles);
		
		include_once $CFG->libdir.'/filestorage/zip_packer.php';
		$packer = new zip_packer();
		if (defined('CLI_SCRIPT')) mtrace("\nExtracting archive...\n");
		$packer->extract_to_storage($archivefile, $context->id, 'mod_resource', 'content', 0, '/');

		// pointing to some special file

		if(empty($this->fd['mainfile'])) {

			if (defined('DEFAULT_MAIN_FILES')){
				$mainfiles = explode(',', DEFAULT_MAIN_FILES);
				list($filepath, $filename) = $this->find_main_file($archivefile, $mainfiles, $context->id, 'mod_resource', 'content', 0, '/');
			}
			
			if (is_null($filename)){
				if (defined('CLI_SCRIPT')) mtrace("\tDeploy : Skipping as no main file in descriptor or no default file found\n");
				return;
			}
		}

		if (is_null($filename)){
			$filepath = '/'.pathinfo($this->fd['mainfile'], PATHINFO_DIRNAME).'/';
			$filepath = str_replace('//', '/', $filepath);
			$filename = pathinfo($this->fd['mainfile'], PATHINFO_BASENAME);
		}
		
        // reset sort order
        file_reset_sortorder($context->id, 'mod_resource', 'content', 0);
        // set main file
        $return = file_set_sortorder($context->id, 'mod_resource', 'content', 0, $filepath, $filename, 1);
		
	}
	
	function pre_process_file($realpath){
		global $CFG;
		
		if ($CFG->ostype == 'WINDOWS'){
			$realpath = utf8_decode($realpath);
		}
		
		if (preg_match('/\.html?$/', $realpath)) {
			
			$filestream = implode('', file($realpath));
			if (preg_match('/charset=iso-.*/', $filestream)){
				$this->filter_to_utf8($filestream);
				if ($FILE = fopen($realpath, 'w')){
					fputs($FILE, $filestream);
					fclose($FILE);
				}
			}
		}
	}
	
	function filter_to_utf8(&$content){
		$content = utf8_encode($content);
		$content = preg_replace('/charset=iso-.*"/', 'charset=utf-8"', $content);
	}
	
	function find_main_file($archivefile, $mainfiles, $contextid, $component, $filearea, $itemid, $path){
		
		$fs = get_file_storage();
		
		// we seek for no extension here
		$archivefilename = pathinfo($archivefile->get_filename(), PATHINFO_FILENAME);

		foreach($mainfiles as $guessname){
			$guessname = str_replace('%FILENAME%', $archivefilename, $guessname); // This is for something as %FILENAME%.htm pattern f.e.
			mtrace("Searching for ... $guessname\n");
			
			$allarea = $fs->get_area_tree($contextid, $component, $filearea, $itemid);
			$candidate = $this->_file_search_rec($allarea, $guessname);
			if ($candidate) return array($candidate->get_filepath(), $candidate->get_filename()); // first positive result traps
		}
		
		return array(null,null);
	}
	
	protected function _file_search_rec($dirstruct, $guessname){
		mtrace("Searching for ... $guessname in ".$dirstruct['dirname']."\n");
		if (!empty($dirstruct['files'])){
			foreach($dirstruct['files'] as $f){
				$fname = $f->get_filename();
				if ($fname == $guessname){
					return $f;
				}
			}
		}
		// if not found in immediate files, search deeper in directories
		if (!empty($dirstruct['subdirs'])){
			foreach($dirstruct['subdirs'] as $dirname => $d){
				$ret = $this->_file_search_rec($d, $guessname);
				if ($ret) return $ret; // trap positive result or continue
			}
		}
		// if nothing found
		return null;
	}
}