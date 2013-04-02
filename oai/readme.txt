OAI-PMH Implementation for TAO/Moodle
#################################################

The OAI-PMH implementation wraps special sets
of resources to the "taoresource_entry" repository.
It implements a minimal LRE metadata model overlapping
the LOM model.

The OAI-PMH querying allows sets to be defined. The default
set is "taoresources" getting entries from the taoresource_entry
records.

The second set is "globalsearch", intending to get records from
the Lucene internal search engine. This set is not fully implemented yet.

Querying the OAI-PMH exposure : 
#####################################################

The base OAI-PMH service querying URL is : 

<%%TOAWWWROOT%%>/local/oai/oai2.php?<%%OAI_QUERY_STRING%%>

The usual form of a OAI query string is using a set of standard CGI arguments : 

verb

Depending on Verb, additional parameters will complete the query

verb = ListRecords :
metadataPrefix
set
from
until
resumptionToken

verb = GetRecord
metadataPrefix
set
identifier

verb = ListIdentifiers :
metadataPrefix
set
from
until
resumptionToken

verb = ListMetadataFormats :
metadataPrefix
set

MetadataFormats 

Provided 'oai_dc' for Dublin Core minimal format envelopes.
Provided 'oai_lre' for European Schoolnet LRE metadata format envelopes.
Provided 'oai_lom' for Standard LOM metadata format envelopes.
Provided 'oai_lomfr' for Standard LOM metadata format envelopes.

Provision for other schemas have been provided but not completed :

moodlecore : A metadata schema proposal from Moodle H.Q.  

Impact on "sharedresource" 
###################################################

- LOM metadata record extraction from a resource entry

the effective construction of the LOM metadata record was defered
to the /mod/taoresource/plugins/local plugin, extracting known fields
of the taoresource_metadata implementation and wrapping values
in matching LOM entries. 

- Resource exposability / validation

although all resources in taoresource repository are intended to be public,
there is no insurance that every entry entered by authors be checked against
publicability. This has needed an additional switch to be added to taoresource_entry
record that will be able to select only validated resources. 

The field has been added in the provided taoresource version but defaults to 1 and
has no actual provision for validation management GUI.

