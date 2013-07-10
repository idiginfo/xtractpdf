xtractpdf
=========

XTRACT PDF 

1. Introduction

    About XtractPDF

        XtractPDF is a tool for getting information out of PDF articles and curating it.  It automatically
        attempts to extract and classify content into structured data, but also provides a handy interface
        for humans to clean-up and perfect the data.  Documents can then be exported to 
        JATS-XML (http://dtd.nlm.nih.gov).

        XtractPDF currently acts as a client to PDFX (http://pdfx.cs.man.ac.uk)
        for automatic conversion, but can be extended to support other libraries and APIs.

    Technologies

    XtractPDF is built entirely on open source technologies (with the exception of PDFX):

        PHP Silex Framework
        Monolog
        Guzzle
        MongoDB &amp; Doctrine ODM
        Jquery
        KnockoutJS
        PDFX
        Jquery Autosize
        Jquery Form
        Prettify
        Twitter Bootstrap
        Fontawesome

    Author

        XtractPDF was written by Casey McLaughlin (http://caseymclaughlin.com) at the 
        iDigInfo Institute (http://idiginfo.org) at Florida State University (http://fsu.edu) for 
        work on projects related to the Jailbreaking the PDF (http://pdfjailbreak.com) collaboration.

2. Requirements (in recommended order of install):

    - Apache2 (sudo apt-get install apache2)
        NOTE: XtractPDF utilizes Apache's mod_rewrite engine.  If you have not done so yet, you will need to enable mod_rewrite
        by typing "sudo a2enmod rewrite" and then restarting Apache with "sudo service apache2 restart", both commands without 
        quotes.

    - PHP 5.3+ with PEAR (sudo apt-get install php5 php-pear)

    - PHP Mongo PECL Extension (sudo pecl install mongo)
  NOTE: After you run the install of this package, you are likely to receive a message at the end advising you 
        to manually update the php.ini configuration file by inserting the "extension=mongo.so" parameter into it, without
        the quotes.  It is recommended that the new line be inserted into the "Dynamic Extensions" section in php.ini.

        In Ubuntu installs you will need to update two php.ini files located in /etc/php5/apache2/php.ini and in
        /etc/php5/cli/php.ini, assuming default installation directories were used.  After both files have been updated, 
        run "sudo service apache2 restart".  Verify that mongo has been successfully loaded as an extention by running 
        "php5 -m".  In the list of recognized extensions, mongo should be listed.  If it is, PHP is ready to use mongo!

    - MongoDB (sudo apt-get install mongodb)

    - Composer (http://getcomposer.org/doc/00-intro.md#globally) using a Global install

    Optional:

    - Git (sudo apt-get install git)
        Having the Git source control solution will simplify acquisition of all files through a single clone operation.

3. Installation:

    Before proceeding: verify that all requirements mentioned in the previous section have been satisfied.

    1. If you have Git installed, you can simply run "git clone https://github.com/idiginfo/xtractpdf.git".
       Otherwise you can download the entire zip package and unpack all the files into a directory of your choosing.

    2. In your newly placed XtractPDF directory, you must have the log and uploads directory set with read and write permissions 
       for everybody in order for XtractPDF to process uploaded files and to write logs.

    2. Change directory to the XtractPDF working directory, then change directory to app.  Run "composer install".  If all of 
       your dependencies are configured properly, Composer will download all required packages necessary to run XtractPDF.  Wait
       until Composer finishes installing all packages before proceeding.

    3. Copy config/config.yml.dist to config/config.yml and verify that all settings match your environment's requirements.

    4. You are ready to run the app.  On your web browser navigate to the URL of your freshly installed XtractPDF.
