<?php
    
    require_once('locallib.php');
    if (empty($CFG->cndpindexing_searchurl)){
        error('Remote Search URL is not configured');
    }

    $start = required_param('start', PARAM_INT);    
    
    // converts query to ISO-5589-1 for CNDP engine input and recompose QUERY_STRING
    $GET = $_GET;

    array_walk($GET, 'convert_encoding');
    
    function convert_encoding(&$a, $k){
        $a = urlencode(mb_convert_encoding($a, 'ISO-8859-1', 'UTF-8'));
    }
    
    foreach($GET as $k => $v){
        $RQ[] = "$k=$v";
    }

    if (!empty($RQ)){
        $rq = implode('&', $RQ);
    } else {
        $rq = '';
    }

    $searchquery = $CFG->cndpindexing_searchurl.'?'.$rq;

    add_to_log(0, 'cndp', 'results', $searchquery, 0);

    if (function_exists('perf_punchin')) perf_punchin('rpccalls');
    
    $ch = curl_init($searchquery);
    
    $timeout = 150;
    
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'RectStra');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost);
    curl_setopt($ch, CURLOPT_PROXYPORT, $CFG->proxyport);
    curl_setopt($ch, CURLOPT_PROXYTYPE, $CFG->proxytype);
    if (!empty($CFG->proxyuser)){
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
    }

    $timestamp_send    = time();
    $rawresponse = curl_exec($ch);
    $timestamp_receive = time();
    
    if (function_exists('perf_punchout')) perf_punchout('rpccalls');

    if ($rawresponse === false) {
        echo get_string('noresultsadvice', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/');
        if (debugging()){
        	print_object(curl_getinfo($ch));
        } ;
    } else {
        $rescount = cndpindexing_parse_xml_getcount($rawresponse);
        $results = cndpindexing_parse_xml_results($rawresponse);
        if ($results === false){
            echo get_string('noresultsadvice', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/');
            if (debugging()) notice(get_string('httpsearchfaileddecode', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'), $CFG->wwwroot.'/resources/search.php?repo='.$repo);            
        } else {
            if (empty($results)){
                echo get_string('noresultsadvice', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/');
            } else {
                echo get_string('resultsadvice', 'cndp', $rescount, $CFG->dirroot.'/resources/plugins/cndp/lang/');
                echo "<div class=\"search\"><ol start=\"".($start + 1)."\">";
                $i = 0;
                foreach($results as $result){
                    $result->i = $i;
                    cndpindexing_print_result($result, $course->id);
                    $i++;
                }
                echo "</ol></div>";
                echo cndpindexing_results_pager($rescount, $start);
            }
        }
    }
?>