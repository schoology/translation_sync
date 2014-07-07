Smartling/Zendesk Translation Sync
=========

This application is a simple script that is intended to be run by a cron job. The application takes two possible directives - upload and download. 
  - **upload** will download the entirety of your Zendesk Help center and send it to all configured Smartling. Usage
        
        php main.php upload /path/to/application/directory

  - **download** will download all Smartling translations and create or update translations in Zendesk. Usage
        
        php main.php download /path/to/application/directory

The intent of the application is to have a two way sync between the applications. Depending on the frequency of new content in your Zendesk help center and translations in Smartling, configure the two commands above in your crontab to run at an appropriate interval.

Setup
----
In the application there is a config.ini.sample file with the expected structure of needed settings. Copy config.ini.sample to create a new file, config.ini. This value is ignore by git as it will contain sensitive information that you may not want committed to a git repository. 

You will need to locate pieces of information from Smarlting and Zendesk in order to instruct the application how to interact with the two APIs.
### Zendesk
Zendesk has good [API documentation](http://developer.zendesk.com/documentation/rest_api/introduction.html) that will help you find the appropriate pieces of information the application will need
  
  - domain - the subdomain your Zendesk runs at
  - email - the email of the Zendesk verified user
  - token - the token of the Zendesk verified user

### Smartling
Smartling also has good [API documentation](https://docs.smartling.com/display/docs/Projects+API) where it explains how to interact with Projects. The application expects one project per language and does not handle creating the projects programmatically. That is left up to you. Once you have created a project you can find the needed information at [https://dashboard.smartling.com/settings/api](https://dashboard.smartling.com/settings/api).

