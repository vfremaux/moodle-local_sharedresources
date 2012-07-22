<?php

/**
* Implements an SQI querier
*/
    include_once 'sqilib.php';
    include_once 'form_remote_search.class.php';

    print_heading(get_string('lresearch', 'lre', '', $CFG->dirroot.'/resources/plugins/lre/lang/'));
    print_container_start(true, 'emptyleftspace');
    
    $searchform = new Remote_Search_Form($CFG->wwwroot."/resources/results.php?id={$courseid}&repo={$repo}");

    echo "<table width=\"95%\" style=\"position:relative;left:-60px\"><tr><td width=\"120\"><img src=\"$CFG->wwwroot/resources/plugins/lre/pix/lre.jpg\"/></td><td width=\"70%\">";    
    $searchform->display();
    echo '</td></tr></table>';
    print_container_end('emptyleftspace');
?>