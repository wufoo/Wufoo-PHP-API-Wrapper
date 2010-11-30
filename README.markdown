###Wufoo PHP API Wrapper

The Wufoo PHP API plugin is meant to help make working with the Wufoo API easier for PHP developers. It doesn't do anything that working directly with the API can't do, it just provides an abstraction layer making getting the information you need easier.

### WufooApiExamples.php

The download comes with a file called WufooApiExamples.php. This file is simply shows how each API call is made.  To test out Wufoo APIs, instantiate this class with your API Key and Subdomain, then try making calls to the public methods.  You can `print_r` the resulting values to see what comes back from a successful (or failing) API call. 

### Basics

The API Wrapper is a collection of functions each for using a specific API. For example, this is how you would get data on your account's users:

	$wrapper = new WufooApiWrapper($apiKey, $subdomain);
	print_r($wrapper->getUsers());
	
Some APIs need more information to be able to return the information they are supposed to. For example, the Entries API needs to know what form to return data from. Each will be documented below.
    
### Full API Documentation

Available here: http://wufoo.com/docs/api/v3/

Each API returns its own set of specific information which is all documented on Wufoo.com for reference.
    
### Users

Information about all users:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain')); //create the class
	print_r($wrapper->getUsers());
	
Full documentation: http://wufoo.com/docs/api/v3/users/

### Forms

Information about all forms:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getForms($identifier = null)); //No identifier needed to retrieve all forms, otherwise pass in a form URL or hash
    
Information about a specific form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getForms($identifier = 'k4j9jw')); //Identifier can be either a form hash or form URL.

Full documentation: http://wufoo.com/docs/api/v3/forms/

### Entries

Entries from a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getEntries('k4j9jw', 'forms', 'Filter1=EntryId+Is_equal_to+1')); //Notice the filter
        
Entries from a report:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getReportEntries('k4j9jw', 'Filter1=EntryId+Is_equal_to+1')); //Notice the filter

Entries POST to a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	//NOTE: Create WufooSubmitFields for the $postArray values
	$postArray = array(new WufooSubmitField('Field1', 'Booyah!'), new WufooSubmitField('Field1', '/files/myFile.txt', $isFile = true));
	print_r($wrapper->entryPost('f83u4d', $postArray));

	Full documentation: http://wufoo.com/docs/api/v3/forms/post/
Full documentation: http://wufoo.com/docs/api/v3/entries/

### Fields

Fields of a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getFields('j9js9r')); //Identifier is a form URL or hash
    
Fields of a report:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getReportFields('j9js9r')); //Identifier is a reporyt URL or hash

Bear in mind that fields may have `SubFields`, as is the case when using Wufoo-provided fields like Name, which has First and Last as subfields. Testing for SubFields and looping through those within the main loop while processing the data is a good idea.

Full documentation: http://wufoo.com/docs/api/v3/fields/

### Comments

Comments are entered in the Wufoo.com Entry Manager. 
    
Get comments from a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getComments('j9js9r', $entryId = '1')); //You may remove the $entryId parameter to get all comments for a form by EntryId.
    
Full documentation: http://wufoo.com/docs/api/v3/comments/
    
### Reports

Information about all reports:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getReports());

Information about single form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->getReports('a5u8r9'));
	
Full documentation: http://wufoo.com/docs/api/v3/reports/
    
###Web Hook 

Add a Web Hook to a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->webHookPut('a5u8r9', 'http://coolguy.com/webhooker/', 'key', $metadata = false);
	
Delete a Web Hook from a form:

	$wrapper = new WufooApiWrapper('KUUI-22JI-ENID-IREW', 'yoursubdomain'); //create the class
	print_r($wrapper->webHookDelete($formIdentifier = '432j83j', $hash = 'a5u8r9'));
	
Full documentation: http://wufoo.com/docs/api/v3/webhooks/
