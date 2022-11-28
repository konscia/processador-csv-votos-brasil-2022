<?php

namespace Konscia\CSVBrazil2022VotesProcessor;

use League\CLImate\CLImate;

class App
{
    private const EXIT_STATUS_SUCCESS = 0;

    private CLImate $cli;

    public function __construct()
    {
        $this->cli = new CLImate();
    }

    public function getCli(): CLImate
    {
        return $this->cli;
    }

    public function exitIfCmdReturnIsError(int $responseError, int $exitCode): void
    {
        if ($responseError === self::EXIT_STATUS_SUCCESS) {
            return;
        }

        $this->exitWithError($exitCode);
    }

    public function exitWithError(int $exitCode): void
    {
        $this->cli->red(Errors::getMessage($exitCode));
        exit($exitCode);
    }

    public function getNumberOfLinesFromFilepath(string $filepath)
    {
        $response = shell_exec("wc -l {$filepath}");
        if($response === NULL) {
            $this->cli->red("Erro na execução do comando para checagem do número de linhas do arquivo");
            exit(1);
        }

        return (int)$response;
    }

    public static function toMb(int $sizeInBytes): string
    {
        return number_format($sizeInBytes / 1024 / 1024, 1, '.', '_') . 'MB';
    }
}