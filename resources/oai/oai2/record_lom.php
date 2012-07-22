<?php
global $CFG,$METADATAFORMATS;

// require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
// require_once($CFG->dirroot.'/blocks/formats/block_formats.php');
require_once($CFG->dirroot.'/resources/oai/metadata/metadata.class.php');

$prefix = 'oai_lom';
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
        'xsi:schemaLocation' => $METADATAFORMATS['shared_lom']['metadataNamespace'].'       http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd',
        'xmlns'=>$METADATAFORMATS['shared_lom']['metadataNamespace']
    );
}

$tr = new tag_renderer();

$output .= $tr->start_tag('metadata');

// load light-lom plugin
include_once $CFG->dirroot.'/mod/sharedresource/sharedresource_plugin_base.class.php';
include_once $CFG->dirroot.'/mod/sharedresource/plugins/lom/plugin.class.php';

$plugin = new sharedresource_plugin_lom();
$sharedresource_entry = get_record('sharedresource_entry', 'identifier', $record['identifier']);
$output .= $plugin->get_metadata($sharedresource_entry);

$output .= $tr->end_tag('metadata');
?>