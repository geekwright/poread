<?php

class PoEntry
{
    /**
     * @var array $entry
     */
    protected $entry = array();

    /**
     * __construct
     */
    public function __construct()
    {
        $this->entry[PoTokens::TRANSLATOR_COMMENTS] = null;
        $this->entry[PoTokens::EXTRACTED_COMMENTS] = null;
        $this->entry[PoTokens::REFERENCE] = null;
        $this->entry[PoTokens::FLAG] = null;
        $this->entry[PoTokens::PREVIOUS] = null;
        $this->entry[PoTokens::OBSOLETE] = null;
        $this->entry[PoTokens::CONTEXT] = null;
        $this->entry[PoTokens::MESSAGE] = null;
        $this->entry[PoTokens::PLURAL] = null;
        $this->entry[PoTokens::TRANSLATED] = null;
    }

    /**
     * add an entry to an array type entry
     * @param string $type  PoToken constant
     * @param string $value entry to store
     * @return void
     */
    public function add($type, $value)
    {
        if (is_null($this->entry[$type])) {
            $this->entry[$type] = array();
        }
        if (is_scalar($this->entry[$type])) {
            $this->entry[$type] = array($this->entry[$type]);
        }
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted entry to an array type entry
     * @param string $type  PoToken constant
     * @param string $value entry to store
     * @return void
     */
    public function addQuoted($type, $value)
    {
        if ($value[0]=='"') {
            $value = substr($value, 1, -1);
        }
        $value = stripcslashes($value);

        if (is_null($this->entry[$type])) {
            $this->entry[$type] = array();
        }
        if (is_scalar($this->entry[$type])) {
            $this->entry[$type] = array($this->entry[$type]);
        }
        $this->entry[$type][] = $value;
    }

    /**
     * add a quoted entry to a nested array
     *
     * This is mainly useful for translated plurals. Since the plural msgstr can have
     * continuation lines, the message is stored as an array of arrays.
     *
     * @param string  $type     PoToken constant
     * @param string  $value    entry to store
     * @param integer $position array position to store
     * @return void
     */
    public function addQuotedAtPosition($type, $value, $position)
    {
        if ($value[0]=='"') {
            $value = substr($value, 1, -1);
        }
        $value = stripcslashes($value);

        if (is_null($this->entry[$type])) {
            $this->entry[$type] = array();
        }
        if (is_scalar($this->entry[$type][$position])) {
            $this->entry[$type][$position] = array($this->entry[$type][$position]);
        }
        $this->entry[$type][$position][] = $value;
        \Kint::dump($this->entry[$type], $type, $value, $position);
    }

    /**
     * get an entry for a type
     * @param string $type PoToken constant
     * @return string|string[]|null
     */
    public function get($type)
    {
        return $this->entry[$type];
    }

    /**
     * set an entry to value
     * @param string $type  PoToken constant
     * @param string $value value to set
     * @return void
     */
    public function set($type, $value)
    {
        $this->entry[$type] = $value;
    }

    /**
     * Dump this entry as a po/pot file fragment
     * @return string
     */
    public function dumpEntry()
    {
        $commentKeys = array(
            PoTokens::TRANSLATOR_COMMENTS,
            PoTokens::EXTRACTED_COMMENTS,
            PoTokens::REFERENCE,
            PoTokens::FLAG,
            PoTokens::PREVIOUS,
            PoTokens::OBSOLETE,
        );

        $output = '';

        foreach ($commentKeys as $type) {
            $section = $this->entry[$type];
            if (is_array($section)) {
                foreach ($section as $comment) {
                    $output .= $type . ' ' . $comment . "\n";
                }
            } elseif (!is_null($section)) {
                $output .= $type . ' ' . $section . "\n";
            }
        }
        $key = PoTokens::CONTEXT;
        if (!is_null($this->entry[$key])) {
            $output .= $key . $this->formatQuotedString($this->entry[$key]);
        }
        $key = PoTokens::MESSAGE;
        if (!is_null($this->entry[$key])) {
            $output .= $key . $this->formatQuotedString($this->entry[$key]);
            $key = PoTokens::PLURAL;
            if (!is_null($this->entry[$key])) {
                $output .= $key . $this->formatQuotedString($this->entry[$key]);
                $key = PoTokens::TRANSLATED;
                $plurals = $this->entry[$key];
                $plurals = is_array($plurals) ? $plurals : array('', '');
                foreach ($plurals as $i => $value) {
                    $output .= "{$key}[{$i}]" . $this->formatQuotedString($value);
                }
            } else {
                $key = PoTokens::TRANSLATED;
                $output .= $key . $this->formatQuotedString($this->entry[$key]);
            }
        }
        $key = PoTokens::PLURAL;
        $key = PoTokens::TRANSLATED;

        $output .= "\n";
        return $output;
    }

    /**
     * formatQuotedString - format a string for output by escaping control and
     * double quote characters, and surrouding with quotes
     * and double quo
     * @param string|null $value string to prepare
     * @param boolean     $bare  true for bare output, default false adds leading
     *                           space and trailing newline
     * @return string
     */
    public function formatQuotedString($value, $bare = false)
    {
        if (is_array($value)) {
            $string = '';
            foreach ($value as $partial) {
                $string .= $this->formatQuotedString($partial, true) . "\n";
            }
            return $bare ? $string : ' ' . $string;
        } else {
            $string = (is_null($value)) ? '' : $value;
            $string = stripcslashes($string);
            $string = addcslashes($string, "\0..\37\"");
            $string = '"' . $string . '"';
            return $bare ? $string : ' ' . $string . "\n";
        }
    }
}
