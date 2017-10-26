<?php
global $CFG, $METADATAFORMATS, $DB;

require_once($CFG->dirroot.'/local/sharedresources/oai/metadata/metadata.class.php');

$prefix = 'oai_suplomfr';
$myformat = $METADATAFORMATS[$prefix];
$atts = array(
            'xmlns:xsi' => $XMLSCHEMA,
            'xsi:schemaLocation' => $myformat['metadataNamespace'].'       '.$myformat['schema']
);

if (!$myformat['defaultnamespace']) {
    $atts['xmlns'] = $myformat['metadataNamespace'];
}

if (!isset($lom)) {
    // Allows reuse of the class for listrecords.
    $lomatts = array(
        'xsi:schemaLocation' => $METADATAFORMATS['shared_lom']['metadataNamespace'].'      http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd',
        'xmlns' => $METADATAFORMATS['shared_lom']['metadataNamespace']
    );
}

$tr = new tag_renderer();

$output .= $tr->start_tag('metadata');

// Load light-suplomfr plugin.
include_once $CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php';
include_once $CFG->dirroot.'/mod/sharedresource/plugins/suplomfr/plugin.class.php';

$plugin = new \mod_sharedresource\plugin_suplomfr();
$sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier' => $record['identifier']));
$output .= $plugin->get_metadata($sharedresource_entry);

$output .= $tr->end_tag('metadata');
