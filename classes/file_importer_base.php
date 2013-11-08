<?php

/**
* an abstract class that represents a single file importer
*
*/
require_once $CFG->dirroot.'/mod/sharedresource/sharedresource_metadata.class.php';

class file_importer_base{

	/**
	* the file descriptior out from data collection. Descriptor is an array of properties
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
						  );

	function __construct($descriptor){
		global $CFG;
		
		$this->fd = $descriptor;
		$this->sharedresourceentry = null;
		$this->METADATA = array();
		$this->new = true;
		
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		if (empty(self::$mtdstandard)){
			self::$mtdstandard = new $object;
		}
	}

	/** creates the sharedresource entry or loads an existing one if matches for aggregating metadata to it, and saves physical file 
	* everything is in the descriptor
	* @param int $context the sharing context of the sharedresource
	*/
	function make_resource_entry($context = 1){
		
		// first we check we do not have this file yet. We must create a temporary file record for this
		// this will allow us to access all the stored_file API for this file.
		$systemcontext = context_system::instance();
		$filerecord = new StdClass();
		$filerecord->contextid = $systemcontext->id;
		$filerecord->component = 'mod_sharedresource';
		$filerecord->filearea = 'temp';
		$filerecord->filepath = '/';
		$filerecord->filename = pathinfo($this->fd['fullpath'], PATHINFO_FILENAME);
		$filerecord->itemid = 0;
		
		$fs = get_file_storage();

		if (!$stored_file = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename)){
			$stored_file = $fs->create_file_from_pathname($filerecord, $this->fd['fullpath']);
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

			// thsi is a default title (with a bit formatting) that can be overriden by explicit metadata 
			if (!array_key_exists('title', $this->fd)){
				$this->fd['title'] = str_replace('_', ' ', $stored_file->get_filename());
			}
			
	    } else {
	    	$this->new = false;
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
		if (empty($this->sharedresourceentry)) return;
				
		if (!empty($this->METADATA)){
			foreach($this->METADATA as $node => $mtdentry){
				$this->sharedresourceentry->add_element($node, $mtdentry, $CFG->pluginchoice);
			}
		}
	}
	
	function save(){
		if ($this->new == true){
			$this->sharedresourceentry->add_instance();
		} else {
			$this->sharedresourceentry->update_instance();
		}
		sharedresources_mark_file_imported($this->fd['fullpath']);
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
		$this->sharedresourceentry->description = $description; // we must simulate htmleditor return
	}

	/** 
	* real wrappers from the CSV file format
	* add both in sharedresource record AND metadata
	*/
	function prepare_title($title){
		$titlefield = self::$mtdstandard->getTitleElement();
		$this->METADATA[$titlefield->name.':0_0'] = $title;
		$this->sharedresourceentry->title = $title;
	}

	/** 
	* real wrappers from the CSV file format
	*/
	function prepare_authors($authors){
		prepare_person($authors, 'author');
	}

	/** 
	* real wrappers from the CSV file format
	*/
	function prepare_contributors($authors){
		prepare_person($authors, 'contributor');
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
			$this->METADATA["2_3_1:0_$i_0"] = $role;

			// create entity subnodes
			$this->METADATA["2_3_2:0_$i_0"] = $vcard;

			// create date subnodes
			$this->METADATA["2_3_3:0_$i_0"] = $date;
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
		
		$defaultpurpose = @$CFG->defaulttaxonomypurposeonimport;

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
				$taxon->id = $DB->insert_record($table, $taxon);
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
		
		if (preg_match('/(\S+)\s+(.*)/', $person, $matches)){
			$firstname = $matches[1];
			$lastname = $matches[2];
		} else {
			$lastname = $person;
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
}