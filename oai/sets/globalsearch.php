<?php

// Here are a couple of queries which might need to be adjusted to 
// your needs. Normally, if you have correctly named the columns above,
// this does not need to be done.

// this function should generate a query which will return
// all records
// the useless condition id_column = id_column is just there to ease
// further extensions to the query, please leave it as it is.
function selectallQuery ($id = '') {
    global $CFG;

    $query = "
        SELECT 
            *,
            docdate as datestamp,
            MD5(url) as oaiid,
            'globalsearch' as `set`
        FROM 
            {$CFG->prefix}block_search_documents
        WHERE 
    ";

    if ($id == '') {
        $query .= 'id = id';
    } else {
        $query .= " MD5(url) = '$id' ";
    }
    return $query;
}

// this function will return identifier and datestamp for all records
function idQuery ($id = '') {
    global $CFG;
    global $OAI;

    $query = "
        SELECT 
            MD5(url) as oaiid,
            doctype,
            itemtype,
            docdate as datestamp,
            'globalsearch' as `set`
        FROM 
            {$CFG->prefix}block_search_documents 
        WHERE 
    ";

    if ($id == '') {
        $query .= 'id = id';
    } else {
        $query .= " MD5(url) = '$id' ";
    }

    return $query;
}

// filter for until
function untilQuery($until) {
    return " AND docdate <= '$OAI->until' ";
}

// filter for from
function fromQuery($from) {
    return " AND docdate >= '$OAI->from' ";
}

// filter for sets
function setQuery($set) {
    return '';
}

/**
* tels if
*/
function isDeleted($id) {
    return false;
}

?>