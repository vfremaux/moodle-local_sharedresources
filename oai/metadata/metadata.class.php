<?php
/**
 * Created on 23 June 2009
 * This file shares functions for creating LOM metadata on all export formats
 * from the OUContent module exporter, OAI-PMH service and block/formats
 * create a download function (LabSpace only) so that metadata
 * is provided in a consistent way in all places
 *
 * @copyright &copy; 2009 The Open University
 * @author j.m.gray@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 * TAO Notice ; THIS FILE IS NOT USED AS BEING BOUND TO INTERNAL ORGANISATION OF THE
 * OPEN UNIVERSITY CUSTOMIZATION.
 */

class metadata {
    private $type;
    private $attributes;
    private $course;
    private $langstring;
    private $tr; // tag renderer class instance

    /*
     * Constructor function
     * @param $p the prefix for each lom tag
     * @param $t indicates scorm, ims cc or ims cp to get the right flavour of LOM
     * @param $a array of attribute key value pairs for the lom tag
     * @param $i the level of indent to start from
     */
    function __construct($t,$p='',$a=array(),$i=0) {
        $this->type = $t;
        $this->attributes = $a;

        $langstring = ($this->type == 'imscp')? 'langstring': 'string';
        $this->tr = new tag_renderer($p,$i,$langstring);
    }

    /*
     * function simply sets up the metadata tag itself with schema info
     * should be used in a pair with end_metadata
     * includes manipulation of the prefix so there is not one for this tag
     */
<<<<<<< HEAD
    public function start_metadata(){
=======
    public function start_metadata() {
>>>>>>> MOODLE_33_STABLE
        $oldprefix = $this->tr->get_prefix();
        $this->tr->set_prefix();
        $lom = $this->tr->start_tag('metadata');

        // different for the various types
        switch ($this->type) {
            case 'imscp':
                $schema     = "IMS Content Package";
                $version    = "1.1.4";
                break;
            case 'scorm':
                $schema     = "ADL SCORM";
                $version    = "1.2";
                break;
            case 'imscc':
                $schema     = "IMS Common Cartridge";
                $version    = "1.0.0";
        }

        if (isset($schema)) {
            $lom .= $this->tr->full_tag('schema',$schema);
            $lom .= $this->tr->full_tag('schemaversion',$version);
        }

        $this->tr->set_prefix($oldprefix);
        return $lom;
    }

    /*
     * function simply closes the metadata tag
     * should be used in a pair with start_metadata
     */
    public function end_metadata() {
        $oldprefix = $this->tr->get_prefix();
        $this->tr->set_prefix();
        $lom = $this->tr->end_tag('metadata');
        $this->tr->set_prefix($oldprefix);
        return $lom;
    }

    /*
     * Helper function allows lom tag to be called with surrounding metadata tags
     * in one line.
     */
    public function get_metadata($course, $schema) {
        $this->set_course($course);

        $lom = $this->start_metadata();
        switch ($schema) {
            case 'lom':
                $lom .= $this->get_lom();
                break;
            case 'dc':
                $lom .= $this->get_dc();
                break;
            default:
                $lom .= "<error>UNDEFINED METADATA SCHEMA</error>";
        }

        $lom .= $this->end_metadata();

        return $lom;
    }

    /*
     * This is the main function which generates the LOM metadata tag
     * @param $course the object for the course on which metadata is required (course-extended-meta and course tables)
     * @returns string
     */
    public function get_lom($course=null,$lommode='LOMv1.0') {
        global $CFG;

        if (isset($course)) {
            $this->set_course($course);
        }
        $this->lommode = $lommode;

        $lom = $this->tr->start_tag('lom',$this->attributes);

        $lom .= $this->get_general();
        $lom .= $this->get_lifecycle();
        $lom .= $this->get_metameta();
        $lom .= $this->get_technical();
        $lom .= $this->get_educational();
        $lom .= $this->get_rights();
        $lom .= $this->get_relation();

        $lom .= $this->tr->end_tag('lom');

        return $lom;
    }

    private function get_general() {
        $lom = $this->tr->start_tag('general');

        if ($this->type == 'imscp') {// tag ordering and naming is slightly different here
            $lom .= $this->tr->lang_tag('title',array($this->course->language=>$this->course->fullname));

            $lom .= $this->tr->start_tag('catalogentry');
            $lom .= $this->tr->full_tag('catalog',$this->course->catalog);
            $lom .= $this->tr->lang_tag('entry',array(''=>$this->course->shortname));
            $lom .= $this->tr->end_tag('catalogentry');
        } else {
            $lom .= $this->tr->start_tag('identifier');
            $lom .= $this->tr->full_tag('catalog', $this->course->catalog);
            $lom .= $this->tr->full_tag('entry', $this->course->shortname);
            $lom .= $this->tr->end_tag('identifier');

            $lom .= $this->tr->lang_tag('title',array($this->course->language=>$this->course->fullname));
        }

        if ($this->course->language != "") { // empty tag not supported for this one
            $lom .= $this->tr->full_tag('language',$this->course->language);
        }

<<<<<<< HEAD
        if(!empty($this->course->summary)) {
            $lom .= $this->tr->lang_tag('description',array($this->course->language=>$this->course->summary));
        }

        if(!empty($this->course->categoryname)) {
=======
        if (!empty($this->course->summary)) {
            $lom .= $this->tr->lang_tag('description',array($this->course->language=>$this->course->summary));
        }

        if (!empty($this->course->categoryname)) {
>>>>>>> MOODLE_33_STABLE
            $lom .= $this->tr->lang_tag('keyword',array($this->course->language=>$this->course->categoryname));
        }

        if ($this->course->tags) {
            foreach ($this->course->tags as $folkstag) {
                $lom .= $this->tr->lang_tag('keyword',array($this->course->language=>$folkstag->rawname));
            }
        }

        if ($this->type != 'imscc') {
            $lom .= $this->vocab_tag('structure','linear','structureValues');

            $al_tag = ($this->type == 'imscp') ? 'aggregationlevel' : 'aggregationLevel';
            $lom .= $this->vocab_tag($al_tag,'2','aggregationLevelValues');
        }

        $lom .= $this->tr->end_tag('general');

        return $lom;
    }

    private function get_lifecycle() {
        $tag = ($this->type == 'imscp') ? 'lifecycle' : 'lifeCycle';
        $lom = $this->tr->start_tag($tag);

        if ($this->type != "imscc") {  // these tags not supported in CC
            $lom .= $this->tr->lang_tag('version', array('' => $this->course->version));
            $status = (!isset($this->course->draft) || $this->course->draft === true) ? 'draft' : 'final';
            $lom .= $this->vocab_tag('status', $status, 'statusValues');
        }

        if ($this->course->public) {
<<<<<<< HEAD
            foreach($this->course->contributed as $name => $true) {
=======
            foreach ($this->course->contributed as $name => $true) {
>>>>>>> MOODLE_33_STABLE
                $person = array("name" => $name);
                $lom .= $this->build_contribute_tag('unknown', $person);
            }
        } else {
            $lom .= $this->build_contribute_tag('author');
        }

        // OU is always the publisher
        $lom .= $this->build_contribute_tag('publisher');

        $lom .= $this->tr->end_tag($tag);

        return $lom;
    }

    private function build_contribute_tag($role,$person=null) {
        $str = $this->tr->start_tag('contribute');
        $str .= $this->vocab_tag('role', $role, 'roleValues');
        if ($this->type == 'imscp') {
            $str .= $this->tr->start_tag('centity');
            $str .= $this->tr->full_tag('vcard', $this->build_vcard($person));
            $str .= $this->tr->end_tag('centity');
        } else {
            $str .= $this->tr->start_tag('entity');
            $str .= "<![CDATA[" . $this->build_vcard($person) . "]]>";
            $str .= $this->tr->end_tag('entity');
        }
        $str .= $this->tr->end_tag('contribute');

        return $str;
    }

    private function build_vcard($person = null) {
        if (is_null($person)) {
            $person = array("name" => "Open University", "institution" => "Open University",
            "address" => "Walton Hall, Milton Keynes, Buckinghamshire, MK7 6AA, United Kingdom");
        }

        $vcard = "BEGIN:VCARD\n";
        if (isset($person['name'])&& $person['name']!='') {
            $vcard .= "FN:$person[name]\n";
            $vcard .= "N:$person[name]\n";
        }
        if (isset($person['institution'])&& $person['institution']!='') {
            $vcard .= "ORG:$person[institution]\n";
        }
        if (isset($person['address'])&& $person['address']!='') {
            $vcard .= "ADD:$person[address]\n";
        }
        $vcard .= "VERSION:3.0\n";
        $vcard .= "END:VCARD";

        return $vcard;
    }

    private function get_metameta() {

        if ($this->type == 'imscp') {
            $mtag = strtolower($mtag);
            $stag = 'metadatascheme';
        } else if ($this->type == 'imscc') {
            return '';
        } else {
            $mtag = 'metaMetadata';
            $stag = 'metadataSchema';
        }

        $lom = $this->tr->start_tag($mtag);
        $tag = ($this->type == 'imscp') ? 'catalogentry' : 'identifier';
        $lom .= $this->tr->start_tag($tag);

        $lom .= $this->tr->full_tag('catalog', $this->course->catalog);

<<<<<<< HEAD
        if ($this->type == 'imscp'){
=======
        if ($this->type == 'imscp') {
>>>>>>> MOODLE_33_STABLE
            $lom .= $this->tr->lang_tag('entry',array('' => $this->course->shortname));
        } else {
            $lom .= $this->tr->full_tag('entry', $this->course->shortname);
        }

        $lom .= $this->tr->end_tag($tag);
        $lom .= $this->build_contribute_tag('creator');
        $lom .= $this->tr->full_tag($stag, $this->lommode);

        if ($this->course->language != "") { // empty tag not supported for this one
            $lom .= $this->tr->full_tag('language', $this->course->language);
        }

        $lom .= $this->tr->end_tag($mtag);

        return $lom;
    }

    private function get_technical() {
<<<<<<< HEAD
        //if($this->lommode=='LREv4.0') { return '';} // technical not used for LRE, leave in hope they won't mind!
=======
        //if ($this->lommode=='LREv4.0') { return '';} // technical not used for LRE, leave in hope they won't mind!
>>>>>>> MOODLE_33_STABLE
        global $CFG;
        $lom = $this->tr->start_tag('technical');
        $lom .= $this->tr->full_tag('format','text/html');
        $url = $CFG->wwwroot."/".$this->course->shortname;
        $lom .= $this->tr->full_tag('location', $url);
        $lom .= $this->tr->end_tag('technical');

        return $lom;
    }

    private function get_educational() {

        // imscp has some tag names different
        $lrt_tag = 'learningResourceType';
        $it_tag = 'interactivityType';
        $il_tag = 'interactivityLevel';
        $eur_tag = 'intendedEndUserRole';
        $tlt_tag = 'typicalLearningTime';
        $tar_tag = 'typicalAgeRange';
        $d_tag = 'duration';
<<<<<<< HEAD
        if($this->type == 'imscp') {
=======
        if ($this->type == 'imscp') {
>>>>>>> MOODLE_33_STABLE
            $lrt_tag = strtolower($lrt_tag);
            $it_tag = strtolower($it_tag);
            $il_tag = strtolower($il_tag);
            $eur_tag = strtolower($eur_tag);
            $tlt_tag = strtolower($tlt_tag);
            $tar_tag = strtolower($tar_tag);
            $d_tag = "datetime";
        }

        $lom = $this->tr->start_tag('educational');

        if ($this->type != "imscc") { // tag not supported in CC
            $lom .= $this->vocab_tag($it_tag,'mixed','interactivityTypeValues');
        }
<<<<<<< HEAD
        if($this->type == 'imscc') {
=======
        if ($this->type == 'imscc') {
>>>>>>> MOODLE_33_STABLE
            $lom .= $this->vocab_tag($lrt_tag,'IMS Common Cartridge','learningResourceTypeValues');
        } else if ($this->type == 'scorm' || $this->type== 'oai') {
            $lom .= $this->vocab_tag($lrt_tag,'narrative text','LOMv1.0');
        } else if ($this->type == 'lre') {
            $lom .= $this->vocab_tag($lrt_tag,'text','LRE.learningResourceTypeValues');
        } else {
            $lom .= $this->vocab_tag($lrt_tag,'Narrative Text','learningResourceTypeValues');
        }

        if ($this->type != "imscc") { // tag not supported in CC
            $lom .= $this->vocab_tag($il_tag,'low','interactivityLevelValues');

            $rolevocab = ($this->type=='lre') ? 'LRE.intendedEndUserRoleValues' : 'intendedEndUserRoleValues';
            $lom .= $this->vocab_tag($eur_tag,'learner',$rolevocab);
            $lom .= $this->vocab_tag($eur_tag,'teacher',$rolevocab);

            $contextvocab = ($this->type=='lre') ? 'LRE.contextValues' : 'contextValues';
            $lom .= $this->vocab_tag('context','higher education',$contextvocab);

            $lom .= $this->tr->lang_tag($tar_tag,array('en-gb'=>'18+','x-t-lre'=>'18-U'));

            if (isset($this->course->edlevel)) { // not available for oucontent previews
                switch ($this->course->edlevel) {
                    case 'introductory' : $difficulty = 'easy'; break;
                    case 'intermediate' : $difficulty = 'medium'; break;
                    case 'advanced' : $difficulty = 'difficult'; break;
                    case 'masters' : $difficulty = 'very difficult'; break;
                    default: $difficulty = '';
                }
                $lom .= $this->vocab_tag('difficulty',$difficulty,'difficultyValues');

                $lom .= $this->tr->start_tag($tlt_tag);
                $lom .= $this->tr->full_tag($d_tag,'PT'.$this->course->duration.'H');
                $lom .= $this->tr->end_tag($tlt_tag);
            }

            $lom .= $this->tr->lang_tag('description',array('en-gb'=>'Independent self-study'));

            if ($this->course->language != "") { // empty tag not supported for this one
                $lom .= $this->tr->full_tag('language',$this->course->language);
            }
        }

        $lom .= $this->tr->end_tag('educational');

        return $lom;
    }

    private function get_rights() {

        // there are a few things that are different for the various LOM flavours
        $cror_tag   = 'copyrightAndOtherRestrictions';
<<<<<<< HEAD
        if($this->type == 'imscp') {
=======
        if ($this->type == 'imscp') {
>>>>>>> MOODLE_33_STABLE
            $cror_tag   = strtolower('copyrightandotherrestrictions');
        }

        $lom = $this->tr->start_tag('rights');

        $lom .= $this->vocab_tag('cost','no','costValues');

        $lom .= $this->vocab_tag($cror_tag,'yes','copyrightAndOtherRestrictionsValues');

        $licences = array('en-gb'=>$this->course->licence);
        if (isset($this->course->licenceurl)) { $licences['x-t-cc-url']=$this->course->licenceurl;}
        $lom .= $this->tr->lang_tag('description',$licences);

        $lom .= $this->tr->end_tag('rights');

        return $lom;
    }

    private function get_relation() {
        $lom = "";
        $kindvalues =  ($this->type == 'lre') ? 'LRE.kindValues' : 'KindValues';

<<<<<<< HEAD
        if( $this->course->groupings) {
            foreach( $this->course->groupings as $id => $relation ) {
=======
        if ( $this->course->groupings) {
            foreach ( $this->course->groupings as $id => $relation ) {
>>>>>>> MOODLE_33_STABLE

                $lom .= $this->tr->start_tag('relation');

                $lom .= $this->vocab_tag('kind','references',$kindvalues);

                $lom .= $this->tr->start_tag('resource');

                $lom .= $this->tr->lang_tag('description',array($this->course->language=>$relation['title']));

                $tag = ($this->type == 'imscp') ? 'catalogentry' : 'identifier';
                $lom .= $this->tr->start_tag($tag);
                $lom .= $this->tr->full_tag('catalog','URL');
<<<<<<< HEAD
                if ($this->type=='imscp'){
=======
                if ($this->type=='imscp') {
>>>>>>> MOODLE_33_STABLE
                    $lom .= $this->tr->lang_tag('entry',array('x-t-url'=>$relation['url']));
                } else {
                    $lom .= $this->tr->full_tag('entry',$relation['url']);
                }
                $lom .= $this->tr->end_tag($tag);

                $lom .= $this->tr->end_tag('resource');

                $lom .= $this->tr->end_tag('relation');
            }
        }

        //  Write out the parent course relation
<<<<<<< HEAD
        if(!empty($this->course->source)) {
=======
        if (!empty($this->course->source)) {
>>>>>>> MOODLE_33_STABLE
            $lom .= $this->tr->start_tag('relation');

            $lom .= $this->vocab_tag('kind','isbasedon',$kindvalues);

            $lom .= $this->tr->start_tag('resource');

            $lom .= $this->tr->lang_tag('description',array('en-gb'=>'This is the title and course code of the source course material'));

            $tag = ($this->type == 'imscp') ? 'catalogentry' : 'identifier';
            $lom .= $this->tr->start_tag($tag);
            $lom .= $this->tr->full_tag('catalog','Open University course');
<<<<<<< HEAD
            if ($this->type=='imscp'){
=======
            if ($this->type=='imscp') {
>>>>>>> MOODLE_33_STABLE
                $lom .= $this->tr->lang_tag('entry',array('en-gb'=>$this->course->source));
            } else {
                $lom .= $this->tr->full_tag('entry',$this->course->source);
            }
            $lom .= $this->tr->end_tag($tag);

            $lom .= $this->tr->end_tag('resource');

            $lom .= $this->tr->end_tag('relation');
        }

        return $lom;
    }

    public function get_dc($course=null) {
        if (isset($course)) {
            $this->set_course($course);
        }

        $dc = "";
        if ($this->type != 'rdf') {
            $dc = $this->tr->start_tag('dc',$this->attributes);
            $oldprefix = $this->tr->get_prefix();
            $this->tr->set_prefix('dc:');
        }

        $dc .= $this->tr->full_tag('title',$this->course->fullname);

        $dc .= $this->tr->full_tag('subject',$this->course->categoryname);

        if (!empty($this->course->tags)) {
            foreach ($this->course->tags as $tag) {
                $dc .= $this->tr->full_tag('subject',$tag->name);
            }
        }
        $dc .= $this->tr->full_tag('description',$this->course->summary);

        $dc .= $this->tr->full_tag('publisher','The Open University');

        if ($this->course->public) {
<<<<<<< HEAD
            foreach($this->course->contributed as $name => $true) {
=======
            foreach ($this->course->contributed as $name => $true) {
>>>>>>> MOODLE_33_STABLE
                $dc.= $this->tr->full_tag('contributor',$name);
            }
        } else {
            $dc .= $this->tr->full_tag('creator', 'The Open University');
        }

        $dc .= $this->tr->full_tag('type','Course');

        $dc .= $this->tr->full_tag('format', 'text/html');

        $dc .= $this->tr->full_tag('identifier',$this->course->shortname);

        $dc .= $this->tr->full_tag('source', $this->course->source);

        $dc .= $this->tr->full_tag('language',$this->course->language);

        if (!empty($this->course->groupings)) {
            foreach ($this->course->groupings as $relation) {
                $dc .= $this->tr->full_tag('relation',$relation['url']);
            }
        }

        $dc .= $this->tr->full_tag('rights',$this->course->licence);

        if ($this->type != 'rdf') {
            $this->tr->set_prefix($oldprefix);
            $dc .= $this->tr->end_tag('dc');
        }
        return $dc;
    }
    /*
     * Sets the course variable for this class
     * Means it doesn't have to be passed into the get routine for each
     * LOM top-level element
     *
     * Also checks if the course is a public contribution and sets a few extra
     * fields on the course object accordingly
     */
    private function set_course($course) {
        global $CFG;
        require_once($CFG->dirroot."/local/ocilib.php");
        require_once( $CFG->dirroot.'/tag/coursetagslib.php' );
        require_once( $CFG->dirroot.'/blocks/related_units/lib.php' );

        if (is_array($course)) {
            $course = (object) $course; // sometimes $course is an array, not an object
        }
        $this->course = $course;

        // pull in some extra data
        $site=get_site();
        $this->course->catalog = $site->shortname;
        if (!isset($this->course->itemflag) && !isset($this->course->categoryname) && isset($this->course->category)) {
            $course->categoryname = get_field('course_categories','name','id',$this->course->category);
        }

        $this->course->groupings = array();
        $this->course->tags = array();
        if (isset($this->course->categoryname)) { // shows its a real course
            $this->course->tags = coursetag_get_official_keywords($this->course->id, true);
            $groupings = block_ru_get_links( $this->course->id ); //  Load this courses related educational resourses
            // process course groupings to skip public contributed ones
<<<<<<< HEAD
            if( $groupings) {
                foreach( $groupings as $k => $grouping ) {
=======
            if ( $groupings) {
                foreach ( $groupings as $k => $grouping ) {
>>>>>>> MOODLE_33_STABLE
                    switch( $k ) {
                        case 'ollinks':
                            $main_title = get_string( 'oltitle', 'block_related_units' );
                            break;
                        case 'oulinks':
                            $main_title = get_string( 'outitle', 'block_related_units' );
                            break;
                    }

                    switch( $k ) {
                        case 'ollinks':
                        case 'oulinks':
<<<<<<< HEAD
                            foreach( $grouping as $key => $link ) {
=======
                            foreach ( $grouping as $key => $link ) {
>>>>>>> MOODLE_33_STABLE
                                $this->course->groupings[$key]=$link;
                            }
                    }
                }
            }

<<<<<<< HEAD
            if(empty($this->course->source)) { // might come from oucontent module
=======
            if (empty($this->course->source)) { // might come from oucontent module
>>>>>>> MOODLE_33_STABLE
                $this->course->source = oci_get_parent_course_from_xml( $this->course->id, $this->course->shortname);
            }
        }

        // and set up some defaults for public contributions
        $this->course->public = oci_check_public_course($this->course->id);
        $licence = 'Licensed under a Creative Commons Attribution - NonCommercial-ShareAlike 2.0 Licence - see http://creativecommons.org/licenses/by-nc-sa/2.0/uk/';

        if (!$this->course->public) {
            $this->course->language = 'en-gb';
            $this->course->version='1.0';
            if (!isset($this->course->licence)) {
                $this->course->licence = $licence . ' - Original copyright The Open University';
                $this->course->licenceurl = 'http://creativecommons.org/licenses/by-nc-sa/2.0/uk/';
            }
            if (isset($this->course->itemid) && strpos($this->course->itemid,'OER')===false) {
                // VLE export
                $this->course->catalog = 'Open University';
            }
        } else {
            $this->course->language='';
            preg_match('/^.*_(\d+\.\d+)$/',$this->course->shortname,$matches);
            $this->course->version = $matches[1];
            if (!isset($this->course->licence)) {
                $this->course->licence = $licence;
                $this->course->licenceurl = 'http://creativecommons.org/licenses/by-nc-sa/2.0/uk/';
            }

            // public contributors names
            $this->course->contributed = array();
            $contributorrole   = get_records_list('role','shortname',"'uploader','revisioneditor'");

<<<<<<< HEAD
            if( $contributorrole) {
                $context = get_context_instance(CONTEXT_COURSE,$this->course->id);
                foreach($contributorrole as $role) {
                    $users = get_role_users($role->id,$context);
                    if( !empty($users) ) {
                        foreach( $users as $user ) {
=======
            if ( $contributorrole) {
                $context = context_course::instance($this->course->id);
                foreach ($contributorrole as $role) {
                    $users = get_role_users($role->id,$context);
                    if ( !empty($users) ) {
                        foreach ( $users as $user ) {
>>>>>>> MOODLE_33_STABLE
                            $this->course->contributed[$user->firstname.' '.$user->lastname] = 1;
                        }
                    }
                }
            }
        }
    }

    // not in tag renderer class because vocab handled differently for each type of metadata
    private function vocab_tag($tag,$content,$vocab) {
        $lom = $this->tr->start_tag($tag);

        if ($this->type == 'imscp') { // ims cp requires language strings
            $lom .= $this->tr->lang_tag('source',array('en-gb'=>$vocab));
            $lom .= $this->tr->lang_tag('value',array('en-gb'=>$content));
        } else if ($this->type == 'scorm' || $this->type == 'oai') {
            $lom .= $this->tr->full_tag('source','LOMv1.0'); // only one valid entry!
            $lom .= $this->tr->full_tag('value',$content);
        } else {
            $lom .= $this->tr->full_tag('source',$vocab);
            $lom .= $this->tr->full_tag('value',$content);
        }

        $lom .= $this->tr->end_tag($tag);

        return $lom;
    }
}

// Tag creation functions used by formats block and oucontent module exporter also
class tag_renderer {
    private $indent;
    private $prefix;
    private $langstring;

<<<<<<< HEAD
    function __construct($p='',$indent = 0,$l='string'){
=======
    function __construct($p='',$indent = 0,$l='string') {
>>>>>>> MOODLE_33_STABLE
        $this->prefix = $p;
        if ($this->prefix && substr($this->prefix,strlen($this->prefix))!=":") {
            $this->prefix .= ':';
        }

        $this->indent = $indent;
        $this->langstring = $l;
    }

    /*
     * Creates a string for an opening tag
     * @param $tag the tag name
     * @param $attributes array of attribuate key value pairs to add to the tag name
     * @param $oneline true if the return string should not inclue a line return
     * @param $empty true if the tag is empty and so should also be closed
     * @returns $string
     */
    public function start_tag($tag,$attributes=null,$oneline = false, $empty = false) {
        $this->indent++;

        $attrstring = '';
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attrstring .= " ".$this->xml_tag_safe_content($key)."=\"".
                $this->xml_tag_safe_content($value)."\"";
            }
        }

        $str =  str_repeat(" ",$this->indent*2)."<".$this->prefix.$tag.$attrstring;

        if ($empty) {
            $str .= "/";
            --$this->indent;
        }
        $str .= $oneline ? ">" : ">\n";

        return $str;
    }

    /*
     * Return the xml end tag, works in a pair with start_tag
     * @param $tag the tag name
     * @returns string
     */
    public function end_tag($tag,$oneline = false) {
        $str = $oneline ? "" : str_repeat(" ",$this->indent*2);

        $str .="</".$this->prefix.$tag.">"."\n";

        --$this->indent;
        return $str;
    }

    /*
     * Return the start tag, the contents and the end tag using start_tag and end_tag functions
     * @param $tag the tag name
     * @param $content the text to be held between the start and end tags
     * @param $attributes array of attribuate key value pairs to add to the tag name
     * @returns $string
     */
    public function full_tag($tag,$content,$attributes=null) {
<<<<<<< HEAD
        if(empty($content)){
=======
        if (empty($content)) {
>>>>>>> MOODLE_33_STABLE
            $tag = $this->start_tag($tag,$attributes,false, true);
        }
        else{
            $st = $this->start_tag($tag,$attributes,true);
            $co = $this->xml_tag_safe_content($content);
            $et = $this->end_tag($tag,true);
            $tag = $st.$co.$et;
        }

        return $tag;
    }

    public function lang_tag($tag,$content) {
        $lom = $this->start_tag($tag);
        foreach ($content as $lang => $value) {
            $attributes = array();
            if ($lang != '') {
                $langtype = ($this->langstring == 'langstring')? 'xml:lang' : 'language'; // IMS CP has different attribute name
                $attributes[$langtype]=$lang;
            }
            $lom .= $this->full_tag($this->langstring,$value,$attributes);
        }
        $lom .= $this->end_tag($tag);

        return $lom;
    }

    public function set_prefix($p='') {
        $this->prefix = $p;
    }

    public function get_prefix() {
        return $this->prefix;
    }

    /*
     * strips all the control chars (\x0-\x1f) from the text but tabs (\x9),
     newlines (\xa) and returns (\xd). The delete control char (\x7f) is also included.
     because they are forbiden in XML 1.0 specs. The expression below seems to be
     UTF-8 safe too because it simply ignores the rest of characters.
     Called by full_tag()
     @param $content text to be processed
     @returns $string
     */
    private function xml_tag_safe_content($content) {
        $content = preg_replace("/[\x-\x8\xb-\xc\xe-\x1f\x7f]/is","",$content);
        $content = preg_replace("/\r\n|\r/", "\n", htmlspecialchars($content));
        return $content;
    }
}
<<<<<<< HEAD
?>
=======
>>>>>>> MOODLE_33_STABLE
