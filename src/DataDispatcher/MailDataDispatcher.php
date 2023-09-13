<?php

namespace DigitalMarketingFramework\Distributor\Mail\DataDispatcher;

use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;

class MailDataDispatcher extends AbstractMailDataDispatcher
{
    protected string $valueDelimiter = '\s=\s';
    protected string $lineDelimiter = '\n';

    public function getValueDelimiter(): string
    {
        return $this->valueDelimiter;
    }

    public function setValueDelimiter(string $valueDelimiter): void
    {
        $this->valueDelimiter = $valueDelimiter;
    }

    public function getLineDelimiter(): string
    {
        return $this->lineDelimiter;
    }

    public function setLineDelimiter(string $lineDelimiter): void
    {
        $this->lineDelimiter = $lineDelimiter;
    }

    /**
     * @param array<mixed> $data
     */
    protected function getPlainBody(array $data): string
    {
        if ($this->getAttachUploadedFiles()) {
            $data = array_filter($data, function ($a) { return !$a instanceof FileValue; });
        }
        $valueDelimiter = GeneralUtility::parseSeparatorString($this->valueDelimiter);
        $lineDelimiter = GeneralUtility::parseSeparatorString($this->lineDelimiter);
        $content = '';
        foreach ($data as $field => $value) {
            $content .= $field . $valueDelimiter . $value . $lineDelimiter;
        }
        return $content;
    }

    /**
     * @param array<mixed> $data
     */
    protected function getHtmlBody(array $data): string
    {
        return '';
    }
}