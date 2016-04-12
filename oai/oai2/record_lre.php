<?php
global $CFG, $METADATAFORMATS, $DB;

require_once($CFG->dirroot.'/local/sharedresources/oai/metadata/metadata.class.php');

$prefix = 'oai_lre';
$myformat = $METADATAFORMATS[$prefix];
$atts = array(
            'xmlns:xsi' => $XMLSCHEMA,
            'xsi:schemaLocation' => $myformat['metadataNamespace'].'       '.$myformat['schema']
);

if (!$myformat['defaultnamespace']) {
    $atts['xmlns'] = $myformat['metadataNamespace'];
}

if (!isset($lom)) { // allows reuse of the class for listrecords
    $lomatts = array(
        'xsi:schemaLocation' => $METADATAFORMATS['tao_lom']['metadataNamespace'].'       http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd',
        'xmlns'=>$METADATAFORMATS['tao_lom']['metadataNamespace']
    );
}

// and set up the tag renderer first time round
if (!isset($tr)) {
    class ilox_tag_renderer extends tag_renderer {
        public function ilox_vocab_tag($tag, $content, $vocab) {

            $lom = $this->start_tag($tag);
            $lom .= $this->full_tag('vocabularyID', $vocab);
            $lom .= $this->full_tag('value', $content);
            $lom .= $this->end_tag($tag);
            return $lom;
        }
    }
    $tr = new ilox_tag_renderer();
}

$output .= $tr->start_tag('metadata');

//FIRST SET UP THE ILOX WRAPPER
$output .= $tr->start_tag('expression',$atts);

$output .= $tr->start_tag('identifier');
$output .= $tr->full_tag('catalog', $repositoryName);
$output .= $tr->full_tag('entry', $record['identifier']);
$output .= $tr->end_tag('identifier');

$output .= $tr->start_tag('description');
$output .= $tr->ilox_vocab_tag('facet','main','LRE.expressionDescriptionFacetValues');

// THEN THE LOM METADATA
$output .= $tr->start_tag('metadata');
$output .= $tr->full_tag('schema','http://ltsc.ieee.org/xsd/LOM');

// load local-light-lom plugin
include_once $CFG->dirroot.'/mod/taoresource/taoresource_plugin_base.class.php';
include_once $CFG->dirroot.'/mod/taoresource/plugins/local/plugin.class.php';

$plugin = new taoresource_plugin_local();
$taoresource_entry = $DB->get_record('taoresource_entry', array('identifier' => $record['identifier']));
$output .= $plugin->get_metadata($taoresource_entry);
$output .= $tr->end_tag('metadata');

$output .= $tr->end_tag('description');

// NOW THE KEY MANIFESTATIONS SECTION
$manifestations = array('web'=>'','imscc'=>'imscc_v1p0','imscp'=>'imscp_v1p1p4','scorm'=>'scorm_v1p2');
// $formats = get_formats_list((object)$record);
foreach ($manifestations as $format => $param) {
    // first some variables set up
    if ($format == 'web') {
        $name = 'experience';
        $title = 'web site';
        $url = $resourceBaseURL.$record['identifier'];
    } else {
        if (!isset($formats[$format])) {continue;} // file doesn't exist
        $name = 'package in';
        $url = $formats[$format]['url'];
        $title = $formats[$format]['title'];
    }

    $output .= $tr->start_tag('manifestation');

    $identifier = $tr->start_tag('identifier');
    $identifier .= $tr->full_tag('catalog', $SITE->shortname);
    $identifier .= $tr->full_tag('entry',$record['shortname']." - $title");
    $identifier .= $tr->end_tag('identifier');
    $output .= $identifier;

    $output .= $tr->ilox_vocab_tag('name',$name,'LRE.manifestationNameValues');

    if ($format != 'web') {
        $output .= $tr->ilox_vocab_tag('parameter',$param,'LRE.packageInValues');
    }

    $output .= $tr->start_tag('item');

    $output .= $identifier;

    $output .= $tr->start_tag('location');
    $output .= $tr->full_tag('uri', $url);
    $output .= $tr->end_tag('location');

    $output .= $tr->end_tag('item');

    $output .= $tr->end_tag('manifestation');
}

//THEN END THE ILOX WRAPPER
$output .= $tr->end_tag('expression');
$output .= $tr->end_tag('metadata');
?>