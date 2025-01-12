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
5. Configurando proxy Nginx:
    - No terminal, execute o seguinte comando para criar o domínio **www.comexio.com.br* no Nginx:
    ```bash
    vi /opt/homebrew/etc/nginx/servers/0-proxy.conf
    ```
    - Adicione o seginte conteúdo:
    ```vi
    upstream @comexio {
        ip_hash;
        server 127.0.0.1:8005;
    }

    server {
    listen 80;
    #listen 443 ssl http2;
    server_name www.comexio.com.br;

    #listen [::]:80 ipv6only=on;
    #listen [::]:443 ipv6only=on http2;

    ssl_ciphers HIGH:!aNULL:!MD5:!kEDH;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:512m;
    ssl_session_timeout 0;

    location / {
        proxy_pass http://@comexio;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-HTTPS-Protocol $ssl_protocol;
        proxy_set_header Connection "";
        proxy_buffering off;

        client_max_body_size 250M;
        keepalive_timeout 300s;

        fastcgi_buffers 8 1600k;
        fastcgi_buffer_size 1600k;
        fastcgi_busy_buffers_size 1600k;
        fastcgi_temp_file_write_size 1600k;
        fastcgi_connect_timeout 24h;
        fastcgi_send_timeout 24h;
        fastcgi_read_timeout 24h;

        proxy_connect_timeout 24h;
        proxy_read_timeout 24h;
        proxy_send_timeout 24h;
        send_timeout 24h;

        reset_timedout_connection on;
    }

    error_log /tmp/error.log;
    log_not_found off;
    access_log off;
    }
    ```
    - Edite o hosts:
    ```bash
    vi /etc/hosts
    ```
    - Adicione a seguinte linha no arquivo /etc/hosts:
    ```vi
    127.0.0.1 www.comexio.com.br
    ```

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
    - Será retornado os seguintes dados
    ```json
    [
        {
            "company_name": "Petrobras",
            "profit": "36.470",
            "rank": 1
        },
        {
            "company_name": "Itaú Unibanco",
            "profit": "5.750",
            "rank": 2
        },
        {
            "company_name": "Vale",
            "profit": "15.980",
            "rank": 3
        },
        {
            "company_name": "Banco do Brasil",
            "profit": "5.760",
            "rank": 4
        },
        {
            "company_name": "Bradesco",
            "profit": "3.490",
            "rank": 5
        },
        {
            "company_name": "BTG Pactual",
            "profit": "1.520",
            "rank": 6
        },
        {
            "company_name": "JBS",
            "profit": "2.990",
            "rank": 7
        },
        {
            "company_name": "Suzano",
            "profit": "3.550",
            "rank": 8
        },
        {
            "company_name": "Eletrobras",
            "profit": "0.705",
            "rank": 9
        },
        {
            "company_name": "Gerdau",
            "profit": "2.270",
            "rank": 10
        },
        {
            "company_name": "Marfrig",
            "profit": "0.804",
            "rank": 11
        },
        {
            "company_name": "Itaúsa",
            "profit": "2.650",
            "rank": 12
        },
        {
            "company_name": "WEG",
            "profit": "0.886",
            "rank": 13
        },
        {
            "company_name": "Raízen",
            "profit": "18.700",
            "rank": 14
        },
        {
            "company_name": "Metalúrgica Gerdau",
            "profit": "0.751",
            "rank": 15
        },
        {
            "company_name": "B3",
            "profit": "0.818",
            "rank": 16
        },
        {
            "company_name": "Vibra Energia",
            "profit": "0.297",
            "rank": 17
        },
        {
            "company_name": "CPFL Energia",
            "profit": "0.987",
            "rank": 19
        },
        {
            "company_name": "Grupo Ultra",
            "profit": "0.312",
            "rank": 20
        },
        {
            "company_name": "Cosan",
            "profit": "0.228",
            "rank": 21
        }
    ]
    ```
2. Retornar empresas com lucro menor que o valor especificado
    ```bash
        curl --location --globoff 'http://www.comexio.com.br/api/company/list?filters[rule]=smaller&filters[billions]=0.2' \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL3dzLmdpdmVyLmNvbS5iciIsImp0aSI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyIsImF1ZCI6Imh0dHBzOi8vY2xpLmdpdmVyLmNvbS5iciIsImlhdCI6MTczNjQ1MjU0MC45OTQ5NDYsImV4cCI6MTczNjUzODk0MC45OTQ5NDYsInVpZCI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyJ9.ih4nSFYKanWUJWRZuDFM2BlkUhdnYOVP68dC2F1kfFZTWvR-Ct_B5-a_scsKgmM6krp1VY8WA7LtF5PKAUBkGoxxk_rQPbZwnllMLsU1g9qQRQjckIVpPljnRMnr8FYjkc4Yj5Rq0G_tphoHxRonLwSk9quyhvOtO9gUg4R7OKKnSxxsh81mv9-58h1saTJ1kGD0FVKI2UCdJQaJCGOv09YFW5AUQ8fKwSd9VS9Hpp2UMerxjvnEq8-ZR80pw_wNUVndR4KwlUCr5SJBKRo7ErTYo8B0lN0XaihZ-W5VjC4Ov7atXkDj8Z1_MTSKuVtBuDneXJI13cQr4nEyFgMcmw'
    ```
    - Será retornado os seguintes dados
    ```json
    [
        {
            "company_name": "Braskem",
            "profit": "0.065",
            "rank": 18
        }
    ]
    ```
3. Retornar empresas com lucro dentro do intervalo especificado em "range"
    ```bash
        curl --location --globoff 'http://www.comexio.com.br/api/company/list?filters[rule]=between&filters[range][]=1&filters[range][]=2' \
        --header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL3dzLmdpdmVyLmNvbS5iciIsImp0aSI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyIsImF1ZCI6Imh0dHBzOi8vY2xpLmdpdmVyLmNvbS5iciIsImlhdCI6MTczNjQ1MjU0MC45OTQ5NDYsImV4cCI6MTczNjUzODk0MC45OTQ5NDYsInVpZCI6ImM5YTNmN2NmLTczYzEtNGQ3Yi04MTJkLTQ1YTc4NmIwN2M5NyJ9.ih4nSFYKanWUJWRZuDFM2BlkUhdnYOVP68dC2F1kfFZTWvR-Ct_B5-a_scsKgmM6krp1VY8WA7LtF5PKAUBkGoxxk_rQPbZwnllMLsU1g9qQRQjckIVpPljnRMnr8FYjkc4Yj5Rq0G_tphoHxRonLwSk9quyhvOtO9gUg4R7OKKnSxxsh81mv9-58h1saTJ1kGD0FVKI2UCdJQaJCGOv09YFW5AUQ8fKwSd9VS9Hpp2UMerxjvnEq8-ZR80pw_wNUVndR4KwlUCr5SJBKRo7ErTYo8B0lN0XaihZ-W5VjC4Ov7atXkDj8Z1_MTSKuVtBuDneXJI13cQr4nEyFgMcmw'
    ```
    - Será retornado os seguintes dados
    ```json
    [
        {
            "company_name": "BTG Pactual",
            "profit": "1.520",
            "rank": 6
        }
    ]
    ```
## Prints
2. Executando tasl no container **comexio_php**
![Img1](storage/doc_file/1.jpeg)
