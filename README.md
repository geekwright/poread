poread
======

Experimental Translate class using .po files

Clone the repository and run "composer install" in the base directory (where composer.json is) to create the vendor directory and bring in the required libraries.

This is a complete web application, just make it accessible through a web server and load index.php in your browser.

The Translate class provides the basic translation functions defined in GNU gettext
  - gettext - translate a string
  - pgettext - translate a string using a particular context
  - ngettext - translate a string using plural rules
  - textdomain - set a domain and language as default
  - gettext_noop - does no translation, but identifies a string to be included in a .po file

For more information on GNU gettext, see: http://www.gnu.org/software/gettext/

Translate just uses po files to provide translations. To create and edit po files you can use the gettext utilites, or any of a number of other compatible tools, such as PoEdit (http://www.poedit.net/).

Translate reads .po files and caches the result in a ready to use form. To test the caching, the ttl (time to live) for the cache entries is set very low (30 seconds) in this demo. The timing information is sent to the debugbar for inspection.

### Background

The basic concept with Translate is to work directly with the .po files, and depend on caching to store a serialized PHP representation. In gettext, a .po file represents a portable object, while an .mo file represents a machine dependent object. The traditional .mo file structure is more appropriate for a C environment than for a PHP environment. By choosing a PHP data structure we get a more optimized machine object, and by compiling as a part of the normal usage we simplify the distribution processes. In a production environment, the expectation is that the cache will be invalidated only when a new version of the .po file is installed, so the more time expensive compile step will only run once.

Translate is intended to become a standard part of XOOPS 2.6.0, replacing the traditional PHP defines, where it can offer several benefits:
 - Language support based on an industry standard
 - Multiple tool sets available for programmers and translators
 - Support for translating plural forms
 - Allows translations in multiple languages to be used concurrently if desired

The current static constant approach keeps many of the limitations of the system it was intended to replace. The expanded locale functions are a good step forward, but the translation aspects are unacceptably weak. By leveraging a standard, we solve many issues, and can benefit from the familiarity of use in other common systems such as Wordpress and Drupal.

This demo is intended to allow review and discussion of this approach before undertaking a conversion of the XOOPS core. Any feedback is appreciated.
