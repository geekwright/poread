<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

//namespace Xmf;

/**
 * Translate
 *
 * A class which implements the basic translation functions used in GNU gettext
 *  - gettext - translate a string
 *  - pgettext - translate a string using a particular context
 *  - ngettext - translate a string using plural rules
 *  - textdomain - set a domain and language as default
 *  - gettext_noop - does no translation, but identifies a string to be included in a po file
 *
 * In addition to standard arguments, gettext, pgettext and ngettext can accept optional
 * language and domain arguments which will override the language and/or domain for the
 * requested translation. The textdomain method accepts an optional language argument.
 *
 * TODO fix
 *
 * @category  Translate
 * @package   Translate
 * @author    Richard Griffith <richard@geekwright.com>
 * @copyright 2013 The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @version   Release: 1.0
 * @link      http://xoops.org
 * @since     1.0
 */
class Translate
{
    /** @type array $catalogs array of loaded po files, keyed by domain and language */
    protected $catalogs = array();

    /** @type array $domains array of current domains */
    protected $domains = array();

    /** @type string $language current language */
    protected $language = null;

    /** @type object $cache cache pool instance */
    protected $cache = null;

    /**
     * Constructor
     *
     * @todo This should be provided by the system cache object
     */
    protected function __construct()
    {
        // set up cache driver
        $options = array('path' => dirname(__FILE__) . '/cache/');
        $driver = new Stash\Driver\FileSystem($options);

        // Create the actual cache object, injecting the backend
        $this->cache = new Stash\Pool($driver);
    }

    /**
     * Get the Translater instance
     *
     * @return Translate object
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $class = get_called_class();
            $instance = new $class();
        }

        return $instance;
    }


    /**
     * gettext - get translation of msgid using previously set domain and language
     *
     * @param string $msgid    string to translate
     * @param string $language optional override language code for this call only
     * @param string $domain   optional override domain  for this call only
     *
     * @return string translated string, of untranslated string if no translation available
     */
    public function gettext($msgid, $language = null, $domain = null)
    {
        if ($language === null && $domain === null) {
            $language = $this->language;
            $domain   = $this->domains;
        } else {
            if ($language === null) {
                $language = $this->language;
                $domain = (array) $domain;
            } elseif ($domain === null) {
                $domain = $this->domains;
            }
            textdomain($domain, $language, false);
        }

        $msgstr = '';
        foreach ($domain as $dom) {
            if (isset($this->catalogs[$dom][$language]['poEntries'][$msgid]['msgstr'][0])) {
                $msgstr = $this->catalogs[$dom][$language]['poEntries'][$msgid]['msgstr'][0];
                break;
            }
        }
        if (empty($msgstr)) {
            $msgstr = $msgid;
        }
        return $msgstr;
    }

    /**
     * pgettext - 'particular' gettext - choose an ambiguious msgid using context clue
     *
     * @param string $msgctxt  context clue
     * @param string $msgid    string to translate
     * @param string $language optional override language code for this call only
     * @param string $domain   optional override domain  for this call only
     *
     * @return type
     */
    public function pgettext($msgctxt, $msgid, $language = null, $domain = null)
    {
        if ($language === null && $domain === null) {
            $language = $this->language;
            $domain   = $this->domains;
        } else {
            if ($language === null) {
                $language = $this->language;
                $domain = (array) $domain;
            } elseif ($domain === null) {
                $domain = $this->domains;
            }
            textdomain($domain, $language, false);
        }

        $key = $msgctxt . '!' . $msgid;
        $msgstr = '';
        foreach ($domain as $dom) {
            if (isset($this->catalogs[$dom][$language]['poEntries'][$key]['msgstr'][0])) {
                $msgstr = $this->catalogs[$dom][$language]['poEntries'][$key]['msgstr'][0];
                break;
            }
        }
        if (empty($msgstr)) {
            $msgstr = $msgid;
        }
        return $msgstr;
    }

    /**
     * ngettext - number gettext - handle plural forms for a given number
     *
     * @param type   $msgid    untranslated singular form
     * @param type   $msgid2   untranslated plural form
     * @param type   $n        number to use a basis for message selection
     * @param string $language optional override language code for this call only
     * @param string $domain   optional override domain  for this call only
     *
     * @return type
     */
    public function ngettext($msgid, $msgid2, $n, $language = null, $domain = null)
    {
        if ($language === null && $domain === null) {
            $language = $this->language;
            $domain   = $this->domains;
        } else {
            if ($language === null) {
                $language = $this->language;
                $domain = (array) $domain;
            } elseif ($domain === null) {
                $domain = $this->domains;
            }
            textdomain($domain, $language, false);
        }

        $msgstr = '';
        foreach ($domain as $dom) {
            if (isset($this->catalogs[$dom][$language]['pluralRule'])) {
                $pluralRule = $this->catalogs[$dom][$language]['pluralRule'];
                $i=$this->selectPluralIndex($n, $pluralRule);
                if (isset($this->catalogs[$dom][$language]['poEntries'][$msgid]['msgstr'][$i])) {
                    $msgstr = $this->catalogs[$dom][$language]['poEntries'][$msgid]['msgstr'][$i];
                    break;
                }
            }
        }
        if (empty($msgstr)) {
            $msgstr = $n==1 ? $msgid : $msgid2;
        }
        return $msgstr;
    }

    /**
     * gettext_noop - identify string that will be translated elsewhere
     *
     * @param string $msgid string to identify
     *
     * @return string $msgid
     */
    public function gettext_noop($msgid)
    {
        return $msgid;
    }

    /**
     * textdomain - set the domain, and optionally language
     *
     * Set domain(s) to be used in subsequent calls. Multiple domains can be specified as an
     * array. The array will be treated as a path for gettext lookup, with first match chosen.
     * The domain is typically the module name.
     *
     * Any required po files will be loaded.
     *
     * @param mixed   $domain         domain(s) to search.
     * @param string  $language       language code. If null current or default language will be used.
     * @param boolean $set_properties if true set domains and language properties, otherwise just load
     *                                to catalog
     *
     * @return void
     */
    public function textdomain($domain, $language = null, $set_properties = true)
    {
        // create a callback to load the domain+language into the cache
        $callback = array($this, 'loadPoForDomain');

        if (empty($language)) {
            if (isset($this->language)) {
                $language = $this->language;
            } else {
                $language = 'en_US';  // should look up default
            }
        }

        if (empty($domain)) {
            if (isset($this->domains)) {
                $domain = $this->domains;
            } else {
                $domain = 'system';
            }
        }
        $domain = (array) $domain;

        foreach ($domain as $dom) {
            if (!isset($this->catalogs[$dom][$language])) {
                $msgCat = false;
                $msgCat = $this->cacheReadSpOld($callback, 30, 'po', $dom, $language);
                $this->catalogs[$dom][$language] = $msgCat;
            }
        }

        if ($set_properties) {
            $this->domains = $domain;
            $this->language = $language;
        }
    }

    /**
     * load message for a domain and language
     *
     * First, locate the best match po file, then parse and return message catalog entry
     *
     * @param string $domain   domain to load
     * @param string $language prefered language code
     *
     * @return mixed array message catalog entry, or false if no suitable po could be loaded
     */
    public function loadPoForDomain($domain, $language)
    {
        // Map a domain and language to a file name
        //
        // Ultimately trigger a translation request event to look for domain and language
        // which will find the best available match for domain language code and country code.
        // For now, we have just build a fixed directory :(

        $file = 'locale/'.$domain.'/'.$language.'.po';
        if (!file_exists($file)) {
            return false; // no provider
        }

        $parser = new \Sepia\poparser();
        try {
            $entries = $parser->read($file);
        } catch (\Exception $e) {
            \Kint::dump($e);
            return false;
        }

        // get plural rule from headers
        $headers = $parser->headers();
        $header = '';
        // undo the quoting and escaping done in poparser - bleah!!
        foreach ($headers as $x) {
            if ($x[0]=='"') {
                $x = substr($x, 1, -1);
            }
            $header .= stripcslashes($x);
        }
        if (preg_match("/(^|\n)plural-forms: ([^\n]*)\n/i", $header, $regs)) {
            $pluralRule = $regs[2];
        } else {
            $pluralRule = "nplurals=2; plural=n == 1 ? 0 : 1;";
        }
        $pluralRule = $this->sanitizePluralExpression($pluralRule);

        // get the message entries
        $poEntries = array();
        foreach ($entries as $key => $entry) {
            if (isset($entry['msgctxt'])) {
                $msgid = implode('', (array) $entry['msgctxt']).'!'.implode('', (array) $entry['msgid']);
            } else {
                $msgid = implode('', (array) $entry['msgid']);
            }
            if (isset($entry['flags'])) {
                $poEntries[$msgid]['flags'] = $entry['flags'];
            }
            if (isset($entry['msgid_plural'])) {
                $poEntries[$msgid]['msgid_plural'] = $entry['msgid_plural'];
                $poEntries[$msgid]['msgstr'] = $entry['msgstr'];
            } else {
                $poEntries[$msgid]['msgstr'][0] = implode('', (array) $entry['msgstr']);
            }
        }

        // bundle into message catalog
        $msgCat = array(
            'poEntries'  => $poEntries,
            'pluralRule' => $pluralRule,
        );

        return $msgCat;
    }

    /**
     * cache block wrapper using Stash SP_OLD invalidation
     *
     * @param callable $regenFunction function to generate cached content
     * @param int      $ttl           cache time to live in seconds
     * @param mixed    $prefix        variable argument list of cachekey, starting with prefix.
     *                                All arguments after prefex are passed to regenFunction
     *
     * @return mixed
     *
     * @todo This should be a generic wrapper in the cache driver, not part of Translate
     */
    public function cacheReadSpOld($regenFunction, $ttl, $prefix)
    {
        // get arg list minus the first three arguments explicitly listed
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        $cacheKey = implode('/', $args);
        array_shift($args);

        $item = $this->cache->getItem($cacheKey);

        // Get the data from the cache using the "Item::SP_OLD" technique for dealing with stampedes
        $cachedContent = $item->get(Stash\Item::SP_OLD);

        // Check to see if the cache missed, which could mean that it either didn't exist or was stale.
        if ($item->isMiss()) {
            // Mark this instance as the one regenerating the cache. Because our
            // protection method is STASH_SP_OLD other Stash instances will use the
            // old value and count it as a hit.
            $item->lock();

            // Run the relatively expensive code.
            $cachedContent = call_user_func_array($regenFunction, $args);

            // save result
            $item->set($cachedContent, $ttl);
        }

        return $cachedContent;
    }

    /**
     * Sanitize plural form expression for use in PHP eval() call.
     *
     * This function taken largely from PHP-gettext, which is Copyright (c) 2003, 2009 Danilo
     * Segan <danilo@kvota.net> and 2005 Nico Kaiser <nico@siriux.net>
     *
     * @param string $expr raw plural expression
     *
     * @return string sanitized plural form expression
     */
    public function sanitizePluralExpression($expr)
    {
        // Get rid of disallowed characters.
        $expr = preg_replace('@[^a-zA-Z0-9 _:;\(\)\?\|\&=!<>+*/\%-]@', '', $expr);

        // Add parenthesis for tertiary '?' operator.
        if (substr($expr, -1) != ';') {
            $expr .= ';';
        }
        $res = '';
        $p = 0;
        for ($i = 0; $i < strlen($expr); $i++) {
            $ch = $expr[$i];
            switch ($ch) {
                case '?':
                    $res .= ' ? (';
                    $p++;
                    break;
                case ':':
                    $res .= ') : (';
                    break;
                case ';':
                    $res .= str_repeat(')', $p) . ';';
                    $p = 0;
                    break;
                default:
                    $res .= $ch;
            }
        }
        // convert C style to PHP -- add '$' prefix to the names 'nplurals', 'plural', and 'n'
        $res = str_replace('nplurals', '$temp', $res); // contains 'plural' and 'n' so use temp name
        $res = str_replace('n', '$n', $res);
        $res = str_replace('plural', '$plural', $res);
        $res = str_replace('$temp', '$nplurals', $res);

        return $res;
    }

    /**
     * Determine which plural form to take
     *
     * This function taken largely from PHP-gettext, which is Copyright (c) 2003, 2009 Danilo
     * Segan <danilo@kvota.net> and 2005 Nico Kaiser <nico@siriux.net>
     *
     * @param int    $n          count
     * @param string $pluralRule pre-sanitized plural rule
     *
     * @return int array index of the correct plural form, or -1 if invalid result
     */
    public function selectPluralIndex($n, $pluralRule)
    {
        $nplurals = 0;
        $plural = 0;

        eval("$pluralRule");

        if (is_bool($plural)) {
            $plural= (int) $plural;
        }
        $plural = (is_numeric($plural)) ? (int) $plural : (-1);
        if ($plural >= (int)$nplurals) {
            $plural = (-1); // should trigger null return
        }
        return $plural;
    }
}
