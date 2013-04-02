<?php

/**
*
*/

require $CFG->dirroot.'/local/lib/simpleXmlDeserializer.php';

/**
* given an XML result bulk, get the interesting data in and 
* makes a hit array. Implenting a Saxe parser
* @param string $results
* @return array
*/
function lre_parse_xml_results($results){
    global $CFG;

    $hits = array();
    $i = 0;

    $parser = new SimpleXmlDeserializer();
    $tree = $parser->parse($results);
    
    // print_object($tree);
    if (isset($tree['STRICTLRERESULTS'][0]['LOM'])){
        foreach($tree['STRICTLRERESULTS'][0]['LOM'] as $LOM){
            $hit = new StdClass;
            $IDENTIFIERS = $LOM['GENERAL'][0]['IDENTIFIER'];
            if (count($IDENTIFIERS) == 1){
                $hit->remoteid = $IDENTIFIERS[0]['ENTRY'][0]['value'];
            } else {
                foreach($IDENTIFIERS as $IDENTIFIER){
                    if ($IDENTIFIER['CATALOG'][0]['value'] == 'oai'){
                        $hit->remoteid = $IDENTIFIER['ENTRY'][0]['value'];
                    }
                }
            }
            $hit->language = $LOM['GENERAL'][0]['LANGUAGE'][0]['value'];
            $TITLES = $LOM['GENERAL'][0]['TITLE'];
            foreach($TITLES as $TITLE){
                // scan available strings and choose our language than resource own language than defaults to last known
                $hit->title = $TITLE['STRING'][0]['value'];
                if ($TITLE['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)){
                    break;
                }
                if ($TITLE['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language){
                    break;
                }
            }
            $hit->description = '';
            $DESCRIPTIONS = @$LOM['GENERAL'][0]['DESCRIPTION'];
            if (!empty($DESCRIPTIONS)){
                foreach($DESCRIPTIONS as $DESCRIPTION){
                    // scan available strings and choose our language than resource own language than defaults to last known
                    $hit->description = $DESCRIPTION['STRING'][0]['value'];
                    if ($DESCRIPTION['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)){
                        break;
                    }
                    if ($DESCRIPTION['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language){
                        break;
                    }
                }
            }
            $hit->url = $LOM['TECHNICAL'][0]['LOCATION'][0]['value'];
            $keywordset = array();
            $KEYWORDSTRINGS = @$LOM['GENERAL'][0]['KEYWORD'][0]['STRING'];
            if (!empty($KEYWORDSTRINGS)){
                foreach($KEYWORDSTRINGS as $KEYWORDSTRING){
                    $keywordLanguage = $KEYWORDSTRING['attributes']['LANGUAGE'];
                    if ($keywordLanguage == substr(current_language(), 0, 2) || $keywordLanguage == $hit->language){
                        $keywordset[] = $KEYWORDSTRING['value'];
                    }
                }
            }
            $hit->keywords = implode(', ', $keywordset);
            $hit->i = $i;
            $hits[] = $hit;
            $i++;
        }
        
        return $hits;
    } else {
        return array();
    }
}

/**
* prints a single result slot.
* @param object $hit
* @param int $courseid
* @param int $page the pagecount of this page of results
*/
function lre_print_search_result($hit, $courseid, $page){
    global $CFG, $SESSION;
    
    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';
    
    $seedetailstr = get_string('viewthenotice', 'lre', '', $stringlocationurl);
    $keywordsstr = get_string('keywords', 'lre', '', $stringlocationurl);
    $addtocourse = get_string('addtocourse', 'lre', '', $stringlocationurl);
    $num = $hit->i + (($page - 1) * $SESSION->SQI->resultSetSize) + 1;

    echo '<li class="searchresult">';
    echo "$num. <a href=\"{$hit->url}\" target=\"_blank\">{$hit->title}</a> ";
    echo "<img src=\"{$CFG->wwwroot}/resources/plugins/lre/pix/lremark.png\" /><br/>";
    echo "<a class=\"indexing_hiturl\" href=\"{$hit->url}\" target=\"_blank\">{$hit->url}</a><br/>";
    // echo "<a style=\"font-size:0.8em\" href=\"{$hit->urldesc}\" target=\"_blank\">{$seedetailstr}</a><br/>";
    echo "<span class=\"desc\">".mb_convert_encoding($hit->description, 'utf-8', 'auto').'</span><br/>';
    echo "<div style=\"font-size:0.8em;margin-top:3px\" class=\"keywords\"><b>$keywordsstr</b>: {$hit->keywords}</div><br/>";
    if ($courseid > SITEID && $CFG->taomode != 'main'){
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        if (has_capability('moodle/course:manageactivities', $context)){
            echo "<form name=\"add{$hit->i}\" action=\"{$CFG->wwwroot}/mod/taoresource/addremotetocourse.php\" style=\"display:inline\">";
            $entry = get_record('taoresource_entry', 'remoteid', $hit->remoteid, 'provider', 'lre');
            if (empty($entry)){
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
                echo "<input type=\"hidden\" name=\"title\" value=\"".htmlentities($hit->title, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"description\" value=\"".htmlentities($hit->description, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"url\" value=\"".htmlentities($hit->url, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"keywords\" value=\"".htmlentities($hit->keywords, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"provider\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"remoteid\" value=\"".htmlentities($hit->remoteid, ENT_QUOTES, 'UTF-8')."\" />";
            } else {
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"identifier\" value=\"{$entry->identifier}\" />";
            }
            echo "</form>";
            echo "<div style=\"text-align:right\" class=\"commands\"><a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
        }
    }
    echo "</li>";
}

function lre_print_paging($page, $maxpage, $courseid, $fullquery){
    global $CFG;
    
    $links = array();
    
    $maxpageceil = min($maxpage, 20);
    for($i = 1 ; $i <= $maxpageceil ; $i++){
        if ($page != $i){
            $links[] = "<a href=\"{$CFG->wwwroot}/resources/results.php?repo=lre&amp;id=$courseid&amp;p={$i}&query=".urlencode($fullquery)."\">{$i}</a>";
        } else {
            $links[] = "<b><u>$i</u></b>";
        }
    }
    if ($maxpage > $maxpageceil) $links[] = '...';
    
    echo '<center>';
    echo implode(' ', $links);
    echo '</center>';
}

?>