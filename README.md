# larabol74

API para a geração da impressão do boleto bancario em PDF

Você pode colocar isso para rodar ouvindo apenas a maquina local, pelo apache ou rodando o php artisan serv na porta 8000

para usar o comando "php artisan serv" e esse serviço não cair terá de usar o supervisor (um aplicativo python que supervisiona esse tipo de serviços na maquina)


## Requisitos

Do PHP

- ext-zlib
- ext-intl
- ext-mbstring

```
sudo apt install php7.4-zlib
sudo apt install php7.4-intl
sudo apt install php7.4-mbstring
```

Da aplicação

eduardokum/laravel-boleto

```
composer require eduardokum/laravel-boleto
```


## Métodos

### [POST] http://localhost:8000/api/boleto/pdf

Payload

```json
{
    "banco": "Sicredi",
    "logo": "",
    "beneficiario": {
        "documento": "12.000.000\/0000-00",
        "nome": "Company co.",
        "cep": "00000-000",
        "endereco": "Street name, 123",
        "bairro": "district",
        "uf": "UF",
        "cidade": "City"
    },
    "pagador": {
        "documento": "00.000.000\/0000-00",
        "nome": "Company co.",
        "cep": "00000-000",
        "endereco": "Street name, 123",
        "bairro": "district",
        "uf": "UF",
        "cidade": "City"
    },
    "boleto": {
        "dataVencimento": "2023-01-19",
        "valor": 100,
        "multa": 1,
        "juros": 1,
        "jurosApos": 0,
        "numero": 1,
        "numeroDocumento": 1,
        "carteira": "1",
        "posto": "11",
        "byte": "2",
        "agencia": "1111",
        "convenio": "123456",
        "conta": "99999",
        "contaDv": "2",
        "range": "99999",
        "codigoCliente": "222222",
        "descricaoDemonstrativo": [
            "demonstrativo 1",
            "demonstrativo 2",
            "demonstrativo 3"
        ],
        "instrucoes": [
            "instrucao 1",
            "instrucao 2",
            "instrucao 3"
        ],
        "aceite": "S",
        "especieDoc": "DM"
    }
}
```

### Campos

#### banco
Opções
|Banco|Nome|
!:---|:---:|
|Bancoob|Bancoob|
|Banrisul|Banrisul|
|Banco do Brasil|Bb|
|Banco do Nordeste|Bnb|
|Bradesco|Bradesco|
|Caixa Econômica Federal|Caixa|
|HSBC|Hsbc|
|Itaú|Itau|
|Santader|Santander|
|Sicredi|Sicredi|

#### logo

O logotipo da empresa que está gerando o boleto, em PNG ou JPG, comprimido e em base64
Caso o logotipo seja passado e for um tipo aceitável será colocado no PDF.
Os logotipos são salvos em disco localmente, e serão usados mesmo se não for passado o campo "logo"
Para não inserir o logotipo passe logo = "SEMLOGO", e o logotipo mesmo existindo em arquivo será ignorado e não incluso no pdf

Ex. base64_encode(gzencode(conteúdo da imagem))

#### beneficiario


#### pagador


#### boleto

A exigencia de campos no bloco do boleto dependem do banco para o qual o boleto é gerado, vide [documentação](https://laravel-boleto.readthedocs.io/en/latest/usage/boleto/index.html)  


## Exemplo de montagem do PAYLOAD

```php
    $beneficiario = [
        'documento' => '00.000.000/0000-00',
        'nome' => 'Company co.',
        'cep' => '00000-000',
        'endereco' => 'Street name, 123',
        'bairro' => 'district',
        'uf' => 'UF',
        'cidade' => 'City',
    ];

    $pagador = [
        'documento' => '00.000.000/0000-00',
        'nome' => 'Company co.',
        'cep' => '00000-000',
        'endereco' => 'Street name, 123',
        'bairro' => 'district',
        'uf' => 'UF',
        'cidade' => 'City',
    ];

    $boleto = [
        "dataVencimento"            => "2023-01-19",
        "valor"                     => 100,
        "multa"                     => 1,
        "juros"                     => 1,
        "jurosApos"                 => 0,
        "numero"                    => 1,
        "numeroDocumento"           => 1,
        "carteira"                  => "1",
        "posto"                     => "11",
        "byte"                      => "2",
        "agencia"                   => "1111",
        "convenio"                  => "123456",
        "conta"                     => "99999",
        "contaDv"                   => "2",
        "range"                     => "99999",
        "codigoCliente"             => "222222",
        'descricaoDemonstrativo'    => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'                => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                    => 'S',
        'especieDoc'                => 'DM',
    ];

    $payload = [
        'banco' => 'itau',
        'logo' => base64_encode(gzencode(Storage::get('palmeiras-logo.png'))),
        'beneficiario' => $beneficiario,
        'pagador' => $pagador,
        'boleto' => $boleto
    ];

    $json = json_encode($payload, JSON_PRETTY_PRINT);
```
