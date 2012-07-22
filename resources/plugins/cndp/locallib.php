<?php

/**
*
*
*/
function cndpindexing_parse_xml_results($results){

    $doc = DOMDocument::loadXML($results);
    
    $hits = array();

    if ($doc){        
        $nodelist = $doc->getElementsByTagName('ressource');
        if ($nodelist->length){
            for($i = 0 ; $i < $nodelist->length ; $i++){

                $result = new StdClass;

                // parsing id attribute
                $aresource = $nodelist->item($i);
                $remoteidattr = $aresource->attributes->getNamedItem('id');
                $result->remoteid = $remoteidattr->nodeValue;
                
                // parsing all other subs and make a result object
                
                $childs = $aresource->childNodes;
                for($j = 0 ; $j < $childs->length ; $j++){
                    $subnode = $childs->item($j);
                    $fieldname = $subnode->nodeName;
                    $result->$fieldname = $subnode->textContent;
                }
                $hits[] = $result;
            }            
            return $hits;
        }
    }
    return false;
}

/**
* prints a single result slot.
* @param object $hit
* @param int $courseid
*/
function cndpindexing_print_result($hit, $courseid){
    global $CFG;
    
    $seedetailstr = get_string('viewthenotice', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/');
    $keywordsstr = get_string('keywords', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/');
    $addtocourse = get_string('addtocourse', 'resources', '', $CFG->dirroot.'/resources/lang/');

    echo '<li class="searchresult">';
    echo '<a href="'.@$hit->url.' target="_blank">'.@$hit->title.'</a> ';
	echo "<img src=\"{$CFG->wwwroot}/resources/plugins/cndp/pix/educasourcemark.jpg\" /><br/>";
    // echo "<a class=\"indexing_hiturl\" href=\"{$hit->url}\" target=\"_blank\">{$hit->url}</a><br/>";
    echo "<a style=\"font-size:0.8em\" href=\"{$hit->urldesc}\" target=\"_blank\">{$seedetailstr}</a><br/>";
    echo "<span class=\"desc\">{$hit->description}</span><br/>";
    echo "<div style=\"font-size:0.8em;margin-top:3px\" class=\"keywords\"><b>$keywordsstr</b>: {$hit->motsclefs}</div><br/>";
    if ($courseid > SITEID){
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        if (has_capability('moodle/course:manageactivities', $context)){
            echo "<form name=\"add{$hit->i}\" action=\"{$CFG->wwwroot}/mod/sharedresource/addremotetocourse.php\" style=\"display:inline\">";
            $entry = get_record('sharedresource_entry', 'remoteid', $hit->remoteid, 'provider', 'educasource');
            if (empty($entry)){
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
                echo "<input type=\"hidden\" name=\"title\" value=\"".htmlentities(@$hit->title, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"description\" value=\"".htmlentities($hit->description, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"url\" value=\"".htmlentities(@$hit->url, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"keywords\" value=\"".htmlentities($hit->motsclefs, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"provider\" value=\"educasource\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"cndp\" />";
                echo "<input type=\"hidden\" name=\"remoteid\" value=\"".htmlentities($hit->remoteid, ENT_QUOTES, 'UTF-8')."\" />";
            } else {
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"cndp\" />";
                echo "<input type=\"hidden\" name=\"identifier\" value=\"".p($entry->identifier)."\" />";
            }
            echo "</form>";
            echo "<div style=\"text-align:right\" class=\"commands\"><a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
        }
    }
    echo "</li>";
}

/**
* extract(s the number of results from a row result string
*
*/
function cndpindexing_parse_xml_getcount($rawresponse){

    $doc = DOMDocument::loadXML($rawresponse);
    
    if ($doc){        
        $nodelist = $doc->getElementsByTagName('ressources');
        if ($nodelist->length){
            $resourcesset = $nodelist->item(0);
            $remotecountattr = $resourcesset->attributes->getNamedItem('num');
            return $remotecountattr->nodeValue ;
        }
        return 0;
    }
    return 0;
}

/**
* extract(s the number of results from a row result string
*
*/
function cndpindexing_results_pager($count, $start){
    global $CFG;
    
    if (!isset($CFG->extsearchresultspagesize)) set_config('extsearchresultspagesize',  10);

    $rq = $_SERVER['QUERY_STRING'];
    $pagecount = ceil($count / $CFG->extsearchresultspagesize);
    
    if ($count > 10){
        for($p = 0 ; $p < $pagecount ; $p++){
            if ($p == floor($start / $CFG->extsearchresultspagesize)){
                $pages[] = '<b>'.($p + 1).'</b>';
            } else {
                $pageurl = '?' . preg_replace('/start=(\\d+)/', 'start=' . $p * 10, $rq);
                $pages[] = "<a href='$pageurl'>" . ($p + 1) . '</a>';
            }
        }
    }
    
    if (!empty($pages))
        return '<center>'.implode (' - ', $pages).'</center>';

    return '';
}

?>