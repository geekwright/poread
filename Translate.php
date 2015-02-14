<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

//namespace Xoops\Core;

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
 * @copyright 2013-2015 The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license   GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @version   Release: 1.0
 * @link      http://xoops.org
 * @since     1.0
 */
class Translate
{
    /**
     * @var array $catalogs array of loaded po files, keyed by domain and language
     */
    protected $catalogs = array();

    /**
     * @var atring[] $domains array of current domains
     */
    protected $domains = array();

    /**
     * @var string $language current language
     */
    protected $language = null;

    /**
     * @var object $pool cache pool instance
     */
    protected $pool = null;

    /**
     * Constructor
     *
     * @todo This should be provided by the system cache object
     */
    protected function __construct()
    {
        // set up cache driver
        $options = array('path' => dirname(__FILE__) . '/cache/');
        $driver = new \Stash\Driver\FileSystem();
        $driver->setOptions($options);
        //$driver = new Stash\Driver\Sqlite($options);

        // Create the actual cache object, injecting the backend
        $this->pool = new \Stash\Pool($driver);
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
     * @param string $domain   optional override domain  for this call only
     * @param string $language optional override language code for this call only
     *
     * @return string translated string, of untranslated string if no translation available
     */
    public function gettext($msgid, $domain = null, $language = null)
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
            $this->textdomain($domain, $language, false);
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
     * @param string $domain   optional override domain  for this call only
     * @param string $language optional override language code for this call only
     *
     * @return type
     */
    public function pgettext($msgctxt, $msgid, $domain = null, $language = null)
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
            $this->textdomain($domain, $language, false);
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
     * @param string $domain   optional override domain for this call only
     * @param string $language optional override language code for this call only
     *
     * @return type
     */
    public function ngettext($msgid, $msgid2, $n, $domain = null, $language = null)
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
            $this->textdomain($domain, $language, false);
        }

        $n = intval(abs($n)); // should be an unsigned integer
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
     * gettext_noop - identify string to be translated when used elsewhere
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
                $cacheKey = array('po',$dom,$language);
                //$msgCat = $xoops->cache()->cacheRead($cacheKey, $callback, 30, $dom, $language);
                $msgCat = $this->cacheRead($cacheKey, $callback, 30, $dom, $language);
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
    protected function loadPoForDomain($domain, $language)
    {
        // Map a domain and language to a file name
        //
        // Ultimately trigger a translation request event to look for domain and language
        // which will find the best available match for domain language code and country code.
        // For now, we have just build a fixed directory :(

        $file = 'locale/'.$domain.'/'.$language.'.po';
        if (!is_readable($file)) {
            return false; // no provider
        }

        try {
            $parser = \Sepia\PoParser::parseFile($file);
            $entries = $parser->getEntries();
        } catch (\Exception $e) {
            \Kint::dump($e);
            return false;
        }

        // get plural rule from headers
        $headers = $parser->getHeaders();
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
     * cache block wrapper -- this should be system supplied
     *
     * If the cache read for $key is a miss, call the $regenFunction to update it.
     * With the PRECOMPUTE strategy, it  will trigger a miss on a read on one caller
     * before the cache expires, so it will be done in advance.
     *
     * @param string|string[]   $cacheKey      Identifier for the cache item
     * @param callable          $regenFunction function to generate cached content
     * @param int|DateTime|null $ttl           time to live, number ofseconds as integer,
     *                                         DateTime to expire at a specific time,
     *                                         or null for default
     * @param mixed             $args          variable argument list for $regenFunction
     *
     * @return mixed
     */
    public function cacheRead($cacheKey, $regenFunction, $ttl = null, $args = null)
    {
        if (is_null($args)) {
            $varArgs = array();
        } else {
            $varArgs = func_get_args();
            array_shift($varArgs); // pull off $key
            array_shift($varArgs); // pull off $regenFunction
            array_shift($varArgs); // pull off $ttl
        }

        $item = $this->pool->getItem($cacheKey);

        // Get the data from cache using the Stash\Invalidation::PRECOMPUTE technique
        // for dealing with stampedes
        $cachedContent = $item->get(\Stash\Invalidation::OLD);

        // Check to see if the cache missed, which could mean that it either didn't exist or was stale.
        if ($item->isMiss()) {
            // Mark this instance as the one regenerating the cache.
            $item->lock();

            // Run the relatively expensive code.
            $cachedContent = call_user_func_array($regenFunction, $varArgs);

            // save result
            $item->set($cachedContent, $ttl);
        }

        return $cachedContent;
    }

    /**
     * Sanitize plural form expression for use in PHP eval() call.
     *
     * This function started from code in PHP-gettext, which is Copyright (c) 2003, 2009
     * Danilo Segan <danilo@kvota.net> and 2005 Nico Kaiser <nico@siriux.net>
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

        // tokenize result to look for mischief - this string will used in be eval()!
        $source = '<?php ' . $res; // add open tag to make this PHP
        $tokens = token_get_all($source);
        array_shift($tokens); // get rid of T_OPEN_TAG we added above.

        foreach ($tokens as $token) {
            //var_dump($token);
            if (is_string($token)) {
                // check for accepted simple 1-character tokens
                if (!in_array($token, array('=', '%', '(', ')', '<', '>', '?', ':', ';'))) {
                    Kint::dump($res);
                    trigger_error(sprintf('Illegal token (%s) in .po file plural rule', $token));
                    $res = '';
                }
            } else {
                switch ($token[0]) {
                    case T_VARIABLE:
                        // only three variables allowed
                        if (!in_array($token[1], array('$nplurals', '$plural', '$n'))) {
                            Kint::dump($res);
                            trigger_error('Illegal variable name in .po file plural rule');
                            $res = '';
                        }
                        break;
                    case T_LNUMBER:
                    case T_WHITESPACE:
                    case T_IS_EQUAL:
                    case T_IS_NOT_EQUAL:
                    case T_BOOLEAN_AND:
                    case T_BOOLEAN_OR:
                    case T_IS_SMALLER_OR_EQUAL:
                    case T_IS_GREATER_OR_EQUAL:
                        // expected tokens
                        break;
                    default:
                        Kint::dump($res);
                        trigger_error(sprintf('Illegal token %s in .po file plural rule', token_name($token[0])));
                        $res = '';
                        break;
                }
            }
            if (empty($res)) {
                break;
            }
        }

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
