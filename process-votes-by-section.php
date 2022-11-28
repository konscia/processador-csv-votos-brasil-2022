<?php

use Konscia\CSVBrazil2022VotesProcessor\App;
use Konscia\CSVBrazil2022VotesProcessor\Args;
use Konscia\CSVBrazil2022VotesProcessor\Errors;

require 'vendor/autoload.php';

/* ****************************************************************** */
/* PREPARA APLICAÇÃO */
/* ****************************************************************** */

$app = new App();
$args = new Args($app, $argv);
$cli = $app->getCli();

$config = require(__DIR__ . '/config.php');
$maxRowsByInsert = $config['max_rows_by_insert'];
$importCsvPath = $config['absolute_path_to_csv2mysql'];

$cmdError = 0;
$cmdOutput = null;

$input = __DIR__ . '/input/';
$output = __DIR__ . '/tmp/';

/* ****************************************************************** */
/* PREPARA ARQUIVO */
/* ****************************************************************** */

$filesToProcess = [];
if ($args->isAll()) {
    $files = glob($input . '*.csv');
    if(count($files) === 0) {
        $app->exitWithError(Errors::ERROR_ARGUMENT_ALL_WITHOUT_FILES);
    }

    foreach ($files as $completefilepath) {
        $parts = explode('/', $completefilepath);
        $filesToProcess[] = array_pop($parts);
    }
} else {
    $filesToProcess[] = $input . $args->getFilename();
}

foreach ($filesToProcess as $filename) {
    $cli->green('Iniciando processamento: ' . $filename);

    $filepath = $input . $filename;
    $filenameToScript = strtolower(explode('.', $filename)[0]);

    if ( ! file_exists($filepath)) {
        $app->exitWithError(Errors::ERROR_FILE_NOT_FOUND);
    }

    $outputStep1 = $output . $filenameToScript . '_step_1.csv';
    $outputStep2 = $output . $filenameToScript . '_step_2.csv';
    $outputStep3 = $output . $filenameToScript . '_step_3.csv';

    /* ****************************************************************** */
    /* EXECUTA TRANSFORMAÇÕES */
    /* ****************************************************************** */

    $cli->dim('Remove colunas desnecessárias');
    exec("cut -d \";\" -f 4,6,7,11,14,16,17,18,20,22,23 {$filepath} > {$outputStep1}", $cmdOutput, $cmdError);
    $app->exitIfCmdReturnIsError($cmdError, Errors::ERROR_REMOVE_COLUMNS_PROCESS);

    $cli->dim('Remove aspas desnecessárias');
    exec('sed "s/\"//g" ' . "{$outputStep1} > {$outputStep2}", $cmdOutput, $responseError);
    $app->exitIfCmdReturnIsError($cmdError, Errors::ERROR_REMOVE_QUOTES);

    $cli->dim('Converte para UTF-8');
    exec("iconv -f ISO-8859-1 -t UTF-8 {$outputStep2} > {$outputStep3}", $cmdOutput, $responseError);
    $app->exitIfCmdReturnIsError($cmdError, Errors::ERROR_CONVERT_UTF8);

    /* ****************************************************************** */
    /* CHECAGENS */
    /* ****************************************************************** */

    $originalNumberOfLines = $app->getNumberOfLinesFromFilepath($filepath);
    $output3NumberOfLines = $app->getNumberOfLinesFromFilepath($outputStep3);
    if ($originalNumberOfLines !== $output3NumberOfLines) {
        $cli->yellow('Linhas no arquivo origem: ' . $originalNumberOfLines);
        $cli->yellow('Linhas no arquivo final: ' . $output3NumberOfLines);
        $app->exitWithError(Errors::ERROR_ORIGINAL_AND_FINAL_FILES_INCOMPATIBLE);
    }

    $originalSizeInB = filesize($filepath);
    $output3SizeInB = filesize($outputStep3);
    $percentageOfReduction = 1 - ($output3SizeInB / $originalSizeInB);

    $cli->dim('Tamanho do arquivo inicial: ' . App::toMb($originalSizeInB));
    $cli->dim('Tamanho do arquivo final: ' . App::toMb($output3SizeInB));
    $cli->dim("Redução do arquivo em <bold>" . number_format($percentageOfReduction * 100) . "%</bold>");

    /* ****************************************************************** */
    /* ENVIO PARA O BANCO DE DADOS */
    /* ****************************************************************** */

    $cli->dim('Importa para o banco de dados');
    $cli->dim("Serão processadas {$output3NumberOfLines} linhas");
    unset($cmdOutput);
    passthru("php {$importCsvPath} {$outputStep3} {$filenameToScript} {$maxRowsByInsert} \";\"", $responseError);
    $app->exitIfCmdReturnIsError($responseError, Errors::ERROR_CSV2MYSQL);

    /* ******************** */
    /* *** ENCERRAMENTO *** */
    /* ******************** */

    $cli->dim('Remove arquivos intermediários');
    $result1 = unlink($outputStep1);
    $result2 = unlink($outputStep2);
    $result3 = unlink($outputStep3);
    if ( ! $result1 or ! $result2 or ! $result3) {
        $app->exitWithError(Errors::ERROR_REMOVE_FILES);
    }

    $cli->green("Arquivo <bold>{$filename}</bold> processado com sucesso.");
}

$cli->bold()->green('Fim. Sucesso.');