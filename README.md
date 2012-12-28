Note: Most of this document applies, but parts of it are outdated.

Table of Contents
=================

1. Introduction

2. Features
	a) Existing Ones
	b) To be Implemented

3. How It Works?
    a) Blocks
    b) Menus
    c) Pages
    d) Settings
    e) Users
    f) Groups

4. Database File Format.
	a) Database File Example

5. Install
	a) Requirements
    b) Url Rewriting
	c) Running with PHP HipHop HHVM

6. Contact Info


------------------------
1. Introduction
------------------------

Thanks for downloading Jaris CMS.

Jaris CMS is a content management system that doesn't requires a RDBMS like mysql
since it is based on a file structure that stores all the content of the website.
The file structure is based on directories for each page and all it's data, making
a page query really fast, since pages are not stored in a huge flat file. The
idea of jaris was during the development of Jaris FLV player website on source
forge. since I couldn't install a CMS easilly I decided to write my own.
Also I had expirienced so much slowness with well known CMS based on mysql in
shared webhosting eviroments, that I wanted something lite and fast. But with
almost all the features of existing well known CMS. Also I wanted something
were I could launch my favorite text editor and make modifications with out
using complicated sql queries.

------------------------
2. Features
------------------------

	a)Existing ones

		* Menus
		* Themes
		* Users
		* User Picture
		* Groups and Permissions
 		* Clean Url's
 		* Translations
 		* Global Blocks
 		* Page Images Uploads
 		* Page Files Uploads
 		* Page blocks
 		* Content Search
                * Modules

 	b)To be Implemented
    
 		* Advanced Search (almost done)

------------------------
3. How It Works?
------------------------

Each page on the CMS is stored on the data directory. Inside the data directory
you will find 6 folders: blocks, menus, pages, users, groups and settings.
Each of this directories store sub-directories or database files (discussed
later on this readme file) with parts of the website. Lets take a look into each
folder.

	a) Blocks
       -------------------------------------------------------------------------

		The blocks folder has 5 files in it. These files are:

			* left.php 		(stores the global left blocks)
			* right.php 	(stores the global right blocks)
			* header.php 	(stores the global header blocks)
			* footer.php 	(stores the global footer blocks)
			* none.php 		(stores the deactivated blocks)

	b) Menus
	   -------------------------------------------------------------------------

		The menus folders store a default set of 2 menus and all the menus created
		by the user in different files. For example if the user created the menu
		music it is stored inside the menus folder in the file music.php. Default
		menu files shipped with the CMS are:

			* primary.php 	(stores primary links)
			* secondary.php (stores secondary links)

	c) Pages
	   -------------------------------------------------------------------------

		The pages directoy has 2 folders, sections and singles. The sections folder
		stores all the pages that belong to a section for example:

	    	* http://mywebsite.com/docs/install

		In the example docs is the section and install is the page. The singles
		directory as it says stores files that doesnt belong to a sections like for
		example:

			* http://mywebsite.com/contact-us

		In this way we ensure high performance when visiting a page. Now lets see
		how every page is stored in the sections folder. Lets take the first example
		http://mywebsite.com/docs/install the path will look like this:

			* data/pages/sections/docs/i/in/install/data.php

		Like you see the section docs is created and an alfabethical structure
		in case there are thousand of pages. As you see the data.php is the database
		file that stores the title and content of the page. There are more files
		in each page folder like:

			* image.php (stores the list of images uploaded for the page)
			* files.php (stores the list of any kind of file uploaded to the page)
			* blocks 	(folder that stores individual blocks for that page only)
			* images 	(folder that stores images binaries)
			* files 	(folder that stores files binaries)

		Now the singles folder use the same semantics but without sections lets
		take the example http://mywebsite.com/contact-us its path would look like
		this:

			* data/pages/singles/c/co/contact-us/data.php

		Like you see is the same thing but without sections. We just want to mark
		a difference betewen both for more easier navigation of content. In this
		way pointing your browser to a page created with jaris cms just takes some
		micro seconds to query on a system with thousands of pages since it use
		a folder structure with database files and not a flat file database to
		store all the pages. We only need to translate the uri into a valid data
		path and retrieve the data from the data.php database file.

	d) Settings
	   -------------------------------------------------------------------------

		The settings folder is used to store configuration files known as tables
		on the configurations functions available on Jaris CMS. The configuration
		table that stores the website title, base url, default theme, etc is the
		main table with the database file name main.php So in the future when
		I implement a module system all your configuration options should go
		on a file with your module name in the settings directory.

	e) Users
	   -------------------------------------------------------------------------

	   In the users you will find a group folder that classifieds users and inside
	   each user that belongs to that group in the same format for content. For
	   example users/administrator/m/my/myusername/data.php

	f) Groups
	   -------------------------------------------------------------------------

	   On the groups directory is stored a folder with the machine name of each
	   group and inside a data.php file with the description and Human readable
	   name of the group. Also a permissions.php file is stored on the groups
	   folders with a list of all the permissions for that group.

------------------------
4. Database File Format
------------------------

Now that you have some understanding of the file stucture used to store the CMS
data I'm going to explain you the database file format implemented to store information.
Since it's much easier a visual example first than teaching theory here it is:

	a) Database File Example
	   -------------------------------------------------------------------------

		<?php exit; ?>
		row: id
			field: name
				value
			field;
		row;

       -------------------------------------------------------------------------

Lets explain each line:

1 | <?php exit; ?> - This line protects the content of the file from prying eyes.
2 | row: id - As you see a row of data in the file were the id is a numerical value.
3 | field: name - This is a field on the row were the name is a string.
4 | value - The actual value of the field
5 | field; - The fields ending
6 | row; - The rows ending

It is a really simple syntax and easy to parse with php built in functions so im
sure you understand the database file format. Is simple and easy.

------------------------
5. Install
------------------------

For installing Jaris CMS this is what you want to read :-)

	a) Requirements
	   -------------------------------------------------------------------------

		* PHP 5 or greater (It could be fixed to work with php 4 too)
		* PHP GD library to manage images.
		* Linux OS (or set of unix tools for windows like rm, mv, cp, etc)
		* Write permission for the data directory (at least 755)
		* Apache with mod rewrite for clean url system (you can disable clean url)
		* Modify settings.php to meet your needs.
        
    b) Url Rewrtiting
       -------------------------------------------------------------------------
       
       Url rewriting is the method used to convert from a http://domain.com/index.php?p=page
       to a more human readable a search engine friendly format http://domain.com/page
       In apache if mod_rewrite is enabled theres nothing you have to do to enable this
       but in lighttpd you have to manually edit the config file in order to enable
       good looking url's here is the lines you need to add on lighttpd:
       
            url.rewrite-once = (
                "^/([^.?]*)\?(.*)$" => "/index.php?p=$1&$2",
                "^/([^.?]*)$" => "/index.php?p=$1"
            )
	
	c) Running with PHP HipHop HHVM
	   -------------------------------------------------------------------------
		On the current jariscms directory execute the hhvm binary as follows:

		hhvm -m server --config hhvm.hdf

After having all the requirements met be sure to upload or copy all the files you
downloaded from http://jariscms.sourceforge.net to your web hosting account. As
said before make the data directory writable by the php engine or user account is
running php, if you want to edit content while online. Chmod 755 should work and
in case it doesn't chmod 777.

A default administrator account is provided for now until the installer is made
with the following acces info:

username: admin
password: pass

After logging to the system change the admin password as soon as possible.

------------------------
6. Contact Info
------------------------

Website: 		http://jariscms.sourceforge.net
Project page: 	http://sourceforge.net/projects/jariscms/
SVN: 			https://jariscms.svn.sourceforge.net/svnroot/jariscms

Note:
-----
If you want to become a developer and you have made some good patches and modifications
to the system that it was lacking I will add you as a developer. Also you can submit
patches at our project page as bug reports, etc.
