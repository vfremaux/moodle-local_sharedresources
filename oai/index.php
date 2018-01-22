<?php
<<<<<<< HEAD
include '../../../config.php';
require_once('oaidp-config.php');
$MY_URI = 'oai2.php';

?>
<html>
<head>
<title>phpoai2 Data Provider</title>
</head>
<body bgcolor="#ffffff">
<blockquote>
<h3>phpoai2 Data Provider</h3>
This is an implementation for an OAI-PMH 2.0 Data Provider, written in PHP.
<p>
This implementation completely complies to OAI-PMH 2.0, including
the support of on-the-fly output compression which may significantly
reduce the amount of data being transfered.
<p>
Database support is supported through PEAR (PHP Extension and
Application Repository, included in the PHP distribution), so almost
any popular SQL-database can be used without any changes in the code. 
<p>
The repository can be quite easily configured by just editing 
oai2/oaidp-config.php, most possible values and options are explained. 
For requirements and instructions to install, please see the 
<a href="doc/README">README</a> file.
<p>
Once you have setup your Data Provider, you can the easiliy check the 
generated answers (it will be XML) of your Data Provider
by clicking on the <a href="#tests">test links below</a>. 
<p>
For simple visual tests set <em>$SHOW_QUERY_ERROR</em> to <em>TRUE</em> 
and <em>$CONTENT_TYPE</em> to <em>text/plain</em>, so you can easily read
the generated XML-answers in your browser. 

<dl>
<dt>Simple Documentation
  <dd><a href="doc/README">README</a></dd>
  <dd><a href="doc/CHANGES">Changes</a></dd>
</dt>
<dt>Example Tables
  <dd><a href="doc/oai_records_mysql.sql">OAI Records (mysql)</a></dd>
  <dd><a href="doc/oai_records_pgsql.sql">OAI Records (pgsql)</a></dd>
</dt>
<dt><a name="tests" />Query and check your Data-Provider</dt>
  <dd><a href="<?php echo $MY_URI ?>?verb=Identify">Identify</a></dd>
  <dd><a href="<?php echo $MY_URI?>?verb=ListMetadataFormats">ListMetadataFormats</a></dd>
  <dd><a href="<?php echo $MY_URI?>?verb=ListSets">ListSets</a></dd>
  <dd><a href="<?php echo $MY_URI?>?verb=ListIdentifiers&amp;metadataPrefix=oai_dc">ListIdentifiers</a></dd>
  <dd><a href="<?php echo $MY_URI?>?verb=ListRecords&amp;metadataPrefix=oai_lom">ListRecords</a></dd>
</dt>
<p>
For detailed tests use the <a href="http://re.cs.uct.ac.za/">Repository Explorer</a>.
<p>
Any comments or questions are welcome.
<p/>	
Heinrich Stamerjohanns<br />
Institute for Science Networking<br />
stamer#AT#uni-oldenburg.de<br />
<p>
	This version has been redrawn for Moodle Shared Resource system by:<br/>
	Valery Fremaux<br/>
	VF Consulting / MyLearningFactory<br/>
	valery.fremaux@gmail.com<br/>
</blockquote>
</body>
</html>

=======
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require('../../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/oai/oaidp-config.php');

$MY_URI = 'oai2.php';
$url = new moodle_url('/local/sharedresources/oai/index.php');

$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Moodle OAI Data Provider');
$PAGE->set_heading('Moodle OAI Data Provider');

echo $OUTPUT->header();
?>
<blockquote>
<h3>MOODLE OAI Data Provider</h3>
<p>This is an implementation for an OAI-PMH 2.0 Data Provider, written in PHP for Moodle, exposing
the central shared reosurce library public indexes.</p>

<p>
This implementation completely complies to OAI-PMH 2.0, including
the support of on-the-fly output compression which may significantly
reduce the amount of data being transfered.</p>

<p>You can the easily check the 
generated answers (it will be XML) of your Data Provider
by clicking on the <a href="#tests">test links below</a>.</p>

<dl class="oai-doc">
<dt>Technical Simple Documentation
    <dd><a href="doc/README">README</a></dd>
    <dd><a href="doc/CHANGES">Changes</a></dd>
</dt>
<dt>Query and check Moodle Integrated Data Provider</dt>
    <dd><p>
        "Identify" query provides you with a global Catalog metadata fragment that identifies the resource providing volume.
        </p><a target="_blank" href="<?php echo $MY_URI ?>?verb=Identify">Identify</a></dd>
    <dd><p>
        "ListMetadataformats" query expose the metadata output formatters that are implemented in this Data Provider.
        </p><a target="_blank" href="<?php echo $MY_URI?>?verb=ListMetadataFormats">ListMetadataFormats</a></dd>
    <dd><p>
        "ListSets" query provides a top level information about resource sets that are available in this Data Provider. A 
        Resource Set denotes a library top level sub volume organisation.
        </p><a target="_blank" href="<?php echo $MY_URI?>?verb=ListSets">ListSets</a></dd>
    <dd>
        <form name="mtdform">
                <?php
                $mtdformat = optional_param('mtdformat', 'oai_'.$CFG->pluginchoice, PARAM_TEXT);
                $formatmenu = array();
                foreach($METADATAFORMATS as $f) {
                    $formatmenu[$f['metadataPrefix']] = $f['metadataPrefix'];
                }
                echo 'Choose a metadata format for index and record queries: '.html_writer::select($formatmenu, 'mtdformat', $mtdformat);
                ?>
        </form>
    </dd>
    <dd><p>
        "ListIdentifiers" query provides the list of new, existing or updated resource indexes. It will not provide any metadata details.
        </p><a target="_blank" href="<?php echo $MY_URI?>?verb=ListIdentifiers&amp;metadataPrefix=<?php echo $mtdformat ?>">ListIdentifiers</a></dd>
    <dd><p>
        "ListRecords" query provides the full metadata tree for the required indexes, in the required metadata format.
        </p><a target="_blank" href="<?php echo $MY_URI?>?verb=ListRecords&amp;metadataPrefix=<?php echo $mtdformat ?>">ListRecords</a>
    </dd>
</dt>

<p>For detailed tests use the <a href="http://re.cs.uct.ac.za/">Repository Explorer</a>.</p>

<p>Any comments or questions are welcome.</p>

<p>The originating implementation was made by : 
<br/>
Heinrich Stamerjohanns<br />
Institute for Science Networking<br />
stamer#AT#uni-oldenburg.de</p>

<p>This version has been redrawn for Moodle Shared Resource system by:<br/>
<br/>
    Valery Fremaux<br/>
    VF Consulting / MyLearningFactory<br/>
    valery.fremaux@gmail.com<br/>
</p>
</blockquote>

<?php

echo $OUTPUT->footer();
>>>>>>> MOODLE_33_STABLE


