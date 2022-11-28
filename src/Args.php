<?php

namespace Konscia\CSVBrazil2022VotesProcessor;

use League\CLImate\CLImate;

class Args
{
    private string $filename;

    public function __construct(App $app, array $arguments)
    {
        if(count($arguments) <> 2) {
            $app->exitWithError(Errors::ERROR_INVALID_ARGUMENTS);
        }

        $this->filename = $arguments[1];
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function isAll(): bool
    {
        return $this->filename === 'all';
    }
}