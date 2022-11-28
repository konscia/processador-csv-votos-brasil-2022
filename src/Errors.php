<?php

namespace Konscia\CSVBrazil2022VotesProcessor;

class Errors
{
    public const ERROR_FILE_NOT_FOUND = 1;
    public const ERROR_REMOVE_COLUMNS_PROCESS = 2;
    public const ERROR_REMOVE_QUOTES = 3;
    public const ERROR_CONVERT_UTF8 = 4;
    public const ERROR_ORIGINAL_AND_FINAL_FILES_INCOMPATIBLE = 5;
    public const ERROR_CSV2MYSQL = 6;
    public const ERROR_REMOVE_FILES = 7;
    public const ERROR_INVALID_ARGUMENTS = 8;
    public const ERROR_ARGUMENT_ALL_WITHOUT_FILES = 9;

    public static function getMessage(int $errorCode)
    {
        return match ($errorCode) {
            self::ERROR_FILE_NOT_FOUND => 'Arquivo não localizado',
            self::ERROR_REMOVE_COLUMNS_PROCESS => 'Erro ao tentar remover colunas sem uso',
            self::ERROR_REMOVE_QUOTES => 'Erro ao tentar remover aspas desnecessárias',
            self::ERROR_CONVERT_UTF8 => 'Erro ao converter o arquivo para UTF-8',
            self::ERROR_ORIGINAL_AND_FINAL_FILES_INCOMPATIBLE => 'Erro no número de linhas entre arquivo original e final',
            self::ERROR_CSV2MYSQL => 'Erro ao tentar importar para o banco de dados com o csv2mysql',
            self::ERROR_REMOVE_FILES => 'Erro ao tentar remover os arquivos temporários finais',
            self::ERROR_INVALID_ARGUMENTS => 'Informe "all" ou nome do arquivo na pasta "input" para iniciar o processamento',
            self::ERROR_ARGUMENT_ALL_WITHOUT_FILES => 'Nenhum arquivo .csv na pasta "input" para processamento',
            default => throw new \RuntimeException("Erro {$errorCode} não identificado")
        };
    }
}