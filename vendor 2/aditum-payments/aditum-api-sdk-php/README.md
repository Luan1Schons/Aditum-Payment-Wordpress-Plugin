Biblioteca de integração Aditum para PHP
===========================================

[![Packagist](https://img.shields.io/packagist/v/aditum-payments/aditum-api-sdk-php.svg?maxAge=2592000)](https://packagist.org/packages/aditum-payments/aditum-api-sdk-php)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1.3-blue.svg?style=flat-square)](https://php.net/)

Descrição
---------
Toda a implementação foi baseada na documentação da **Aditum**, pelo link  [documentação api](https://sandbox.aditum.com.br/#primeiros-passos).

 - [Criar Autorização de pagamento via cartão](https://sandbox.aditum.com.br/#post-v2-charge-authorization)
 - [Criar Autorização de pagamento via boleto](https://sandbox.aditum.com.br/#post-v2-charge-boleto)
 - [Consultar informação de um cobrança](https://sandbox.aditum.com.br/#get-v2-charge-chargeid)

Requisitos
----------

 - [PHP] ^7.1.3
 - [cURL]
 - [Composer]

 Instalação
----------
> Nota: Recomendamos a instalação via **Composer**. Você também pode baixar o repositório como [arquivo zip] ou fazer um clone via Git.
 
 ### Instalação via Composer
> Para baixar e instalar o Composer no seu ambiente acesse https://getcomposer.org/download/ e caso tenha dúvidas de como utilizá-lo consulte a [documentação oficial do Composer].

É possível instalar a biblioteca aditum-api-sdk-php([aditum-payments/aditum-api-sdk-php](aditum-payments/aditum-api-sdk-php)) via Composer de duas maneiras:

- Executando o comando para adicionar a dependência automaticamente
  ```
  php composer.phar require aditum-payments/aditum-api-sdk-php/
  ```
**OU**

- Adicionando a dependência ao seu arquivo ```composer.json```
  ```composer.json
  {
      "require": {
         "aditum-payments/aditum-api-sdk-php" : "^1.1"
      }
  }
  ```
 
### Instalação manual
 - Baixe o repositório como [arquivo zip] ou faça um clone;
 - Descompacte os arquivos em seu computador;
 - Execute o comando ```php composer.phar install``` no local onde extraiu os arquivos.
 
 
 Como usar
 ---------
O diretório *[public](https://github.com/aditum-payments/aditum-api-sdk-php/tree/main/public)* contém exemplos das mais diversas chamadas à API da Aditum e o diretório *[src](https://github.com/aditum-payments/aditum-api-sdk-php/tree/main/src)* contém a biblioteca propriamente dita (código fonte).

Seguir os passos a seguir:
- [Configuração do pacote](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/CONFIG.md)
- [Pagamento por cartão](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/PAYCC.md)
- [Pagamento por boleto](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/PAYBOLETO.md)
- [Consultar transação](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/PAYSTATUS.md)
- [Recursos de ajuda](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/HELPER.md)
- [Enum](https://github.com/aditum-payments/aditum-api-sdk-php/blob/main/document/ENUM.md)


