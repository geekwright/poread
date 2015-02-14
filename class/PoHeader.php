<?php

class PoHeader extends PoEntry
{
    protected $structuredHeaders = null;

    protected function buildStructuredHeaders()
    {
        $this->structuredHeaders = array();
        $headers = $this->entry[PoTokens::TRANSLATED];
        $full = implode('', $headers);
        $headers = explode("\n", $full);
        // split on ':'
        $pattern = '/([a-z0-9\-]+):\s*(.*)/i';
        foreach ($headers as $h) {
            if (preg_match($pattern, trim($h), $matches)) {
                $this->structuredHeaders[strtolower($matches[1])] = array(
                    'key' => $matches[1],
                    'value' => $matches[2],
                );
            }
        }
    }

    protected function storeStructuredHeader()
    {
        if (empty($this->structuredHeaders)) {
            return false;
        }
        $headers = array("");

        foreach ($this->structuredHeaders as $h) {
            $headers[] = $h['key'] . ': ' . $h['value'] . "\n";
        }
        $this->entry[PoTokens::TRANSLATED] = $headers;
        return true;
    }

    public function getHeader($key)
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        $header = false;
        if (isset($this->structuredHeaders[$lkey]['value'])) {
            $header = $this->structuredHeaders[$lkey]['value'];
        }
        return $header;
    }

    public function setHeader($key, $value)
    {
        $this->buildStructuredHeaders();
        $lkey = strtolower($key);
        if (isset($this->structuredHeaders[$lkey])) {
            $this->structuredHeaders[$lkey]['value'] = $value;
        } else {
            $newHeader = array('key' => $key, 'value' => $value);
            $this->structuredHeaders[$lkey] = $newHeader;
        }
        $this->storeStructuredHeader();
    }

    public function setCreateDate($time = null)
    {
        $this->setHeader('POT-Creation-Date', $this->formatTimestamp($time));
    }

    public function setRevisionDate($time = null)
    {
        $this->setHeader('PO-Revision-Date', $this->formatTimestamp($time));
    }

    protected function formatTimestamp($time = null)
    {
        if (empty($time)) {
            $time = time();
        }
        return gmdate('Y-m-d H:iO', $time);
    }
}
