# Processador do CSV de votos TSE 2022 por seção eleitoral

O TSE disponibiliza o resultado das eleições com dados de voto em cada candidato em cada seção eleitoral. Estes arquivos são no formato "csv" e possuem colunas de código e colunas descritivas, que fazem com que os arquivos fiquem com grande tamanho.

Este script transformações nos arquivos CSV oficiais do TSE dos votos das eleições 2022 para diminuir o tamanho dos mesmos e automaticamente os importa para o banco de dados utilizando o software [csv2mysql](https://github.com/konscia/csv2mysql)

## Configurações

Copie o arquivo `config.php.dist` para `config.php` e informa os valores das duas variáveis pedidas:

* **absolute_path_to_csv2mysql**: Caminho completo para a instalação local do diretório do aplicativo [csv2mysql](https://github.com/konscia/csv2mysql);
* **max_rows_by_insert**: Número de linhas do CSV a serem enviadas para o banco de dados em cada pacote de inserções. Este valor depende da variável `max_allowed_packet` do MySQL. Veja abaixo sobre ela.

A velocidade do script de importação depende do volume de linhas do CSV que podem ser enviadas em cada pacote para o MySQL. Com um valor de 512MB conforme exemplo abaixo, é possível configurar o valor de `max_rows_by_insert` em até 1 milhão de linhas. Você pode fazer alguns testes e perceber o que funciona melhor na sua máquina.

```mysql
# Exemplo de como ver o valor em bytes configurado em max_allowed_packet
show GLOBAL variables like 'max_allowed_packet';

# Configura o valore da variável do MySQL em 512MB
SET GLOBAL max_allowed_packet=512 * 1024 * 1024;
```
## Exemplo de execução

Para um arquivo .csv ser processado ele deve estar dentro da pasta `input`.
Como exemplo, em `samples`, há o arquivo de votação no ACRE, de 2022, compactado com _brotli_. Para usar este arquivo, descompacte e envie para a pasta input:

```bash
cd ./samples
brotli -d votacao_secao_2022_AC.csv.br
mv votacao_secao_2022_AC.csv ../input/
cd ../
```

Com o arquivo na pasta `input`, você pode processá-lo com o comando:

```
php php process-votes-by-section.php votacao_secao_2022_AC.csv
```

O processamento irá limpar o arquivo, criar um CSV intermediário com aproximadamente 88% de redução do tamanho e enviar o arquivo já reduzido para o banco de dados com o csv2mysql. Uma tabela com o mesmo nome do arquivo será criada.

Você também pode executar em lote todos os arquivos do diretório utilizando como nome do arquivo a palavra 'all':

```
php php process-votes-by-section.php all
```