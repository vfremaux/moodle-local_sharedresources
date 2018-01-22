<?php
global $CFG, $METADATAFORMATS, $DB;

require_once($CFG->dirroot.'/local/sharedresources/oai/metadata/metadata.class.php');

$prefix = 'oai_lomfr';
$myformat = $METADATAFORMATS[$prefix];
$atts = array(
            'xmlns:xsi' => $XMLSCHEMA,
            'xsi:schemaLocation' => $myformat['metadataNamespace'].'       '.$myformat['schema']
);

if (!$myformat['defaultnamespace']) {
    $atts['xmlns'] = $myformat['metadataNamespace'];
}

<<<<<<< HEAD
if (!isset($lom)) { // allows reuse of the class for listrecords
    $lomatts = array(
        'xsi:schemaLocation' => $METADATAFORMATS['shared_lom']['metadataNamespace'].'       http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd',
        'xmlns' => $METADATAFORMATS['shared_lom']['metadataNamespace']
=======
if (!isset($lom)) {
    // allows reuse of the class for listrecords.
    $lomatts = array(
        'xsi:schemaLocation' => $METADATAFORMATS['oai_lom']['metadataNamespace'].'       http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd',
        'xmlns' => $METADATAFORMATS['oai_lom']['metadataNamespace']
>>>>>>> MOODLE_33_STABLE
    );
}

$tr = new tag_renderer();

$output .= $tr->start_tag('metadata');

// load light-lom plugin
<<<<<<< HEAD
include_once $CFG->dirroot.'/mod/sharedresource/sharedresource_plugin_base.class.php';
include_once $CFG->dirroot.'/mod/sharedresource/plugins/lomfr/plugin.class.php';

$plugin = new sharedresource_plugin_lomfr();
=======
include_once $CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php';
include_once $CFG->dirroot.'/mod/sharedresource/plugins/lomfr/plugin.class.php';

$plugin = new \mod_sharedresource\plugin_lomfr();
>>>>>>> MOODLE_33_STABLE
$sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier' => $record['identifier']));
$output .= $plugin->get_metadata($sharedresource_entry);

$output .= $tr->end_tag('metadata');
<<<<<<< HEAD
?>
=======
>>>>>>> MOODLE_33_STABLE
