# Desafio Backend (Captação)

O desafio consiste em criar uma aplicação capaz de buscar dados na [página da Wikipédia](https://pt.wikipedia.org/wiki/Lista_das_maiores_empresas_do_Brasil) que contém a lista das maiores empresas do Brasil. O objetivo é filtrar as empresas pelo lucro, com base nos parâmetros enviados na requisição.

## Instalação
1. Realize um git clone git@github.com:fabiogwnet/come.git
2. Execute docker-compose -f docker-compose-dev.yml up -d
3. Executar os 2 .sql do diretório migration 
    - 20250101143200_capture_company.sql
    - 20250101143300_company.sql
4. Por se tratar de um teste, deixei as configurações nos arquivos abaixo
    - .env
    - .env.testing

## Dependências

Por se tratar de um teste, deixei as dependências baixadas e versionadas

## Execução
1. Acessando o container:
    - No terminal, execute o seguinte comando para entrar no container:
    ```bash
    docker exec -it comexio_php bash
    ```
2. Executando a task:
    - Dentro do container **comexio_php**, execute o comando abaixo para buscar dados na [página da Wikipédia](https://pt.wikipedia.org/wiki/Lista_das_maiores_empresas_do_Brasil) e popular as tabelas correspondentes:
    ```bash
    php tasker wiki:import-largest-companies-brazil
    ```
3. Automatização:
    - Para garantir a execução periódica da task, configure uma cron job para ser executada diariamente ou semanalmente, conforme a necessidade.

## GrumPHP
1. Para rodas as validações phpcs, phpunit, phpparser e phpstan
    - Crei um arquivo de pre-commit
    ```bash
    vi .git/hooks/pre-commit
    ```
    - Insira as linhas abaixo para deixar automatizado
     ```bash
    #!/bin/sh
    DIFF=$(git -c diff.mnemonicprefix=false --no-pager diff -r -p -m -M --full-index --no-color --staged | cat)
    export GRUMPHP_GIT_WORKING_DIR=$(git rev-parse --show-toplevel)
    (cd "./" && printf "%s\n" "${DIFF}" | docker exec -t comexio_php vendor/bin/grumphp git:pre-commit --ansi --skip-success-output)
    ```

## Exemplos de uso