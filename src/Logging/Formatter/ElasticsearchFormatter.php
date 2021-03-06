<?php

namespace Si6\Base\Logging\Formatter;

use Monolog\Formatter\ElasticsearchFormatter as BaseElasticsearchFormatter;

class ElasticsearchFormatter extends BaseElasticsearchFormatter
{
    public function format(array $record)
    {
        if (
            $this->recordHasContext($record) &&
            $this->contextHasException($record['context']) &&
            property_exists($record['context']['exception'], 'request')
        ) {
            $record['extra']['request'] = $record['context']['exception']->request;
        }

        $record = parent::format($record);

        if ($this->recordHasContext($record) && $this->contextHasException($record['context'])) {
            $record['context']['exception'] = $this->getContextException($record['context']['exception']);
        }

        return $this->getDocument($record);
    }

    protected function getContextException(array $recordContext)
    {
        return [
            'class'         => $recordContext['class'] ?? '',
            'message'       => $recordContext['message'] ?? '',
            'code'          => intval($recordContext['code']) ?? '',
            'file'          => $recordContext['file'] ?? '',
            'trace'         => $recordContext['trace'] ?? '',
        ];
    }

    protected function recordHasContext(array $record): bool
    {
        return (
            array_key_exists('context', $record)
        );
    }

    protected function contextHasException($context): bool
    {
        return (
            is_array($context)
            && array_key_exists('exception', $context)
        );
    }
}
