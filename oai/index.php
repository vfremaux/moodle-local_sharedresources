<?php
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



