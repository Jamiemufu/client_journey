# README

The intu Retailer Event 2016 landing page is built on Simple Site Template / Whiskey Framework.

### Where is the project hosted?

The site will reside on the 2016 Q1 shared Amazon server (52.48.251.100).

### How do I get set up?

* Ensure that Node.js and all of the required packages in gulpfile.js are installed;
* Run 'composer install' from the terminal;
* Create a .env file based upon the .env.example file in the application's root directory and populate any missing values;
* Visit the application in a web browser.

### How do I compile LESS and minify JavaScript files?
Simply run the command 'gulp' from the terminal for the following to occur:

* src/css/styles.less compiled and minified to _public/css/styles.min.css
* src/js/scripts.js compiled and minified to _public/js/scripts.min.js

Gulp will watch the source files for changes and recompile/minify them until the 'gulp' terminal process is aborted.