<?php

/**
* Implements an SQI querier
*/
    include_once 'sqilib.php';
    include_once 'form_remote_search.class.php';

    print ($OUTPUT->heading(get_string('lresearch', 'local_sharedresources')));
    echo $OUTPUT->box_start(true, 'emptyleftspace');
    
    $searchform = new Remote_Search_Form($CFG->wwwroot."/local/sharedresources/results.php?id={$courseid}&repo={$repo}");

    echo "<table width=\"95%\" style=\"position:relative;left:-60px\"><tr><td width=\"120\"><img src=\"$CFG->wwwroot/local/sharedresources/plugins/lre/pix/lre.jpg\"/></td><td width=\"70%\">";    
    $searchform->display();
    echo '</td></tr></table>';
    echo $OUTPUT->box_end('emptyleftspace');
