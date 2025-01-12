# Desafio Backend (Captação)

O desafio consiste em criar uma aplicação capaz de buscar dados na [página da Wikipédia](https://pt.wikipedia.org/wiki/Lista_das_maiores_empresas_do_Brasil) que contém a lista das maiores empresas do Brasil. O objetivo é filtrar as empresas pelo lucro, com base nos parâmetros enviados na requisição.

## Instalação
1. Realize um git clone git@github.com:fabiogwnet/come.git
2. Execute **docker-compose -f docker-compose-dev.yml up -d** para subir os containers
3. Executar os 2 .sql do diretório migration em um terminal SQL
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
1. Para rodar as validações phpcs, phpunit, phpparser e phpstan
    - Criar um arquivo de **pre-commit**
    ```bash
    vi .git/hooks/pre-commit
    ```
    - Insira as linhas abaixo para deixar automatizado e executar a cada commit
    ```bash
    #!/bin/sh
    DIFF=$(git -c diff.mnemonicprefix=false --no-pager diff -r -p -m -M --full-index --no-color --staged | cat)
    export GRUMPHP_GIT_WORKING_DIR=$(git rev-parse --show-toplevel)
    (cd "./" && printf "%s\n" "${DIFF}" | docker exec -t comexio_php vendor/bin/grumphp git:pre-commit --ansi --skip-success-output)
    ```

## Exemplos de uso
1. Retornar empresas com lucro maior que o valor especificado
    ```bash
        curl --location --globoff 'http://www.comexio.com.br/api/company/list?filters[rule]=greater&filters[billions]=0.2' \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL3dzLmdpdmVyLmNvbS5iciIsImp0aSI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyIsImF1ZCI6Imh0dHBzOi8vY2xpLmdpdmVyLmNvbS5iciIsImlhdCI6MTczNjQ1MjU0MC45OTQ5NDYsImV4cCI6MTczNjUzODk0MC45OTQ5NDYsInVpZCI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyJ9.ih4nSFYKanWUJWRZuDFM2BlkUhdnYOVP68dC2F1kfFZTWvR-Ct_B5-a_scsKgmM6krp1VY8WA7LtF5PKAUBkGoxxk_rQPbZwnllMLsU1g9qQRQjckIVpPljnRMnr8FYjkc4Yj5Rq0G_tphoHxRonLwSk9quyhvOtO9gUg4R7OKKnSxxsh81mv9-58h1saTJ1kGD0FVKI2UCdJQaJCGOv09YFW5AUQ8fKwSd9VS9Hpp2UMerxjvnEq8-ZR80pw_wNUVndR4KwlUCr5SJBKRo7ErTYo8B0lN0XaihZ-W5VjC4Ov7atXkDj8Z1_MTSKuVtBuDneXJI13cQr4nEyFgMcmw'
    ```