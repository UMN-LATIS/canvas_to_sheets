# Canvas to Google Sheets Sample

This repository provides a sample app that harvests data from Canvas reports and loads it into Google Sheets.  You'll need to run this in a terminal window, and you'll need a basic php environment with [Composer](https://getcomposer.org).  Run `composer install` to install the dependencies.

If you'd like to experiment, populate the values within the `import.php` file.  Within your Google Sheets document, create sheets for each report, and add those names to the source file.  They don't need any other content.

The first time you run `php import.php`, you should be prompted to approve the Google integration.  After that, those credentials will be saved in `client_secret.json` and you won't need to enter them again. 