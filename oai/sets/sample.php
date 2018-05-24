<?php

// Here are a couple of queries which might need to be adjusted to 
// your needs. Normally, if you have correctly named the columns above,
// this does not need to be done.

global $SQL;

$SQL['table'] = 'tablename';
$SQL['id_column'] = 'id';
$SQL['identifier'] = 'identifier';
$SQL['set'] = 'set0';
$SQL['datestamp'] = 'timecreated';

// this function should generate a query which will return
// all records
// the useless condition id_column = id_column is just there to ease
// further extensions to the query, please leave it as it is.
function selectallQuery ($id = '') {
    global $SQL;

    $query = 'SELECT * FROM '.$SQL['table'].' WHERE ';
    if ($id == '') {
        $query .= $SQL['id_column'].' = '.$SQL['id_column'];
    }
    else {
        $query .= $SQL['identifier']." ='$id'";
    }
    return $query;
}

// this function will return identifier and datestamp for all records
function idQuery ($id = '') {
    global $SQL;

    if ($SQL['set'] != '') {
        $query = 'select '.$SQL['identifier'].','.$SQL['datestamp'].','.$SQL['set'].' FROM '.$SQL['table'].' WHERE ';
    } else {
        $query = 'select '.$SQL['identifier'].','.$SQL['datestamp'].' FROM '.$SQL['table'].' WHERE ';
    }
    
    if ($id == '') {
        $query .= $SQL['id_column'].' = '.$SQL['id_column'];
    }
    else {
        $query .= $SQL['identifier']." = '$id'";
    }

    return $query;
}

// filter for until
function untilQuery($until) {
    global $SQL;

    return ' and '.$SQL['datestamp']." <= '$until'";
}

// filter for from
function fromQuery($from) {
    global $SQL;

    return ' and '.$SQL['datestamp']." >= '$from'";
}

// filter for sets
function setQuery($set) {
    global $SQL;

    return ' and '.$SQL['set']." LIKE '%$set%'";
}


?>