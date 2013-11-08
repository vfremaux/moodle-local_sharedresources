Local Shared Resource Library Site Front End
================================================

This component implements a Library Front End for accessing, browsing, searching
in sharedresources. It will provide also a Librarian backoffice to manange, list
update resource indexation information, among with some other site level services
such as OAI exposure. 

When browsed from a course context, the library keeps awareness of originating 
context and will allow direct resource publication from the library search results. 

Shared resource can be an alternative to "common Pot" filesystem repository, adding
possiblity to all teachers to feed collectively the resource base. 

Install
==============================

Shared resource module is the key part of a full indexed public resource center that 
will come as 4 complementary parts : 

- Shared resource module : master part 
- Shared resources block : Utilities to access to central library and make some resource conversions or feeding
- Shared resource repository : A Repository view of the shared resource storage area, so shared resource can also be used and picked
as standard resource instances, or in other publication contexts
- Shared Resource Local Component (this package) : provides a front-end to librarian to search, browse and get some site level services around shared resources.
 