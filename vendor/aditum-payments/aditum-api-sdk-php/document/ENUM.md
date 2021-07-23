# ChargeStatus

### Status da transação
```php
AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED;        // Todas as transações da cobrança foram aprovadas.
AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED;    // Todas as transações da cobrança foram pré-autorizadas.
AditumPayments\ApiSDK\Enum\ChargeStatus::CANCELED;          // Todas as transações da cobrança foram canceladas.
AditumPayments\ApiSDK\Enum\ChargeStatus::PARTIAL;           // As transações da cobrança diferem em status. Verificar o status de cada transação individualmente.
AditumPayments\ApiSDK\Enum\ChargeStatus::_AUTHORIZED;       // Todas as transações da cobrança foram negadas.
AditumPayments\ApiSDK\Enum\ChargeStatus::NOT_PENDING_CANCEL;// Todas as transações da cobrança estão com cancelamento pendente.
```

#

# PaymentType

###  Tipos de pagamentos
```php
AditumPayments\ApiSDK\Enum\PaymentType::UNDEFINED;  // Indefinido.
AditumPayments\ApiSDK\Enum\PaymentType::DEBIT;      // Débito.
AditumPayments\ApiSDK\Enum\PaymentType::CREDIT;     // Crédito.
AditumPayments\ApiSDK\Enum\PaymentType::VOUCHER;    // Voucher.
AditumPayments\ApiSDK\Enum\PaymentType::BOLETO;     // Boleto.
AditumPayments\ApiSDK\Enum\PaymentType::TED ;       // Transferência Eletrônica de Fundos.
AditumPayments\ApiSDK\Enum\PaymentType::DOC;        // Documento de Ordem de Crédito.
AditumPayments\ApiSDK\Enum\PaymentType::SAFETY_PAY; // SafetyPay.
```
#

# CardBrand

### Nomes das bandeiras
```php
AditumPayments\ApiSDK\Enum\CardBrand::VISA;
AditumPayments\ApiSDK\Enum\CardBrand::MASTER_CARD;
AditumPayments\ApiSDK\Enum\CardBrand::AMEX;
AditumPayments\ApiSDK\Enum\CardBrand::ELO;
AditumPayments\ApiSDK\Enum\CardBrand::AURA;
AditumPayments\ApiSDK\Enum\CardBrand::JCB;
AditumPayments\ApiSDK\Enum\CardBrand::DINERS;
AditumPayments\ApiSDK\Enum\CardBrand::DISCOVER;
AditumPayments\ApiSDK\Enum\CardBrand::HIPERCARD;
AditumPayments\ApiSDK\Enum\CardBrand::ENROUTE;
AditumPayments\ApiSDK\Enum\CardBrand::TICKET;
AditumPayments\ApiSDK\Enum\CardBrand::SODEXO;
AditumPayments\ApiSDK\Enum\CardBrand::VR;
AditumPayments\ApiSDK\Enum\CardBrand::ALELO;
AditumPayments\ApiSDK\Enum\CardBrand::SETRA;
AditumPayments\ApiSDK\Enum\CardBrand::VERO;
AditumPayments\ApiSDK\Enum\CardBrand::SOROCRED;
AditumPayments\ApiSDK\Enum\CardBrand::GREEN_CARD;
AditumPayments\ApiSDK\Enum\CardBrand::CABAL;
AditumPayments\ApiSDK\Enum\CardBrand::BANESCARD;
AditumPayments\ApiSDK\Enum\CardBrand::VERDE_CARD;
AditumPayments\ApiSDK\Enum\CardBrand::VALE_CARD;
AditumPayments\ApiSDK\Enum\CardBrand::UNION_PAY;
AditumPayments\ApiSDK\Enum\CardBrand::UP;
AditumPayments\ApiSDK\Enum\CardBrand::TRICARD;
AditumPayments\ApiSDK\Enum\CardBrand::BIGCARD;
AditumPayments\ApiSDK\Enum\CardBrand::BEN;
AditumPayments\ApiSDK\Enum\CardBrand::REDE_COMPRAS;
```

#

# AcquirerCode

### Adquirentes
```php
AditumPayments\ApiSDK\Enum\AcquirerCode::CIELO;
AditumPayments\ApiSDK\Enum\AcquirerCode::REDE;
AditumPayments\ApiSDK\Enum\AcquirerCode::STONE;
AditumPayments\ApiSDK\Enum\AcquirerCode::VBI;
AditumPayments\ApiSDK\Enum\AcquirerCode::GRANITO;
AditumPayments\ApiSDK\Enum\AcquirerCode::INFINITE_PAY;
AditumPayments\ApiSDK\Enum\AcquirerCode::SAFRA_PAY;
AditumPayments\ApiSDK\Enum\AcquirerCode::ADITUM_ECOM;
AditumPayments\ApiSDK\Enum\AcquirerCode::PAGSEGURO;
AditumPayments\ApiSDK\Enum\AcquirerCode::ADITUM_TEF;
AditumPayments\ApiSDK\Enum\AcquirerCode::SAFRAPAYTEF;
AditumPayments\ApiSDK\Enum\AcquirerCode::VR_BENEFITS;
AditumPayments\ApiSDK\Enum\AcquirerCode::SIMULADOR;
```
#

# PhoneType

### Tipos de telefones
```php
AditumPayments\ApiSDK\Enum\PhoneType::RESIDENCIAL; // Telefone Residencial
AditumPayments\ApiSDK\Enum\PhoneType::COMERCIAL;   // Telefone Comercial
AditumPayments\ApiSDK\Enum\PhoneType::VOICEMAIL;   // Correio de Voz
AditumPayments\ApiSDK\Enum\PhoneType::TEMPORARY;   // Telefone Temporário
AditumPayments\ApiSDK\Enum\PhoneType::MOBILE;      // Celular
```
# DocumentType

### Tipos de documentos
```php
AditumPayments\ApiSDK\Enum\DocumentType::CPF;
AditumPayments\ApiSDK\Enum\DocumentType::CNPJ;
```

# DiscountType

### Tipos de documentos
```php
AditumPayments\ApiSDK\Enum\DiscountType::UNDEFINED; // Indefinido
AditumPayments\ApiSDK\Enum\DiscountType::PERCENTUAL;// Aplicará desconto percentual com base em uma data de pagamento.
AditumPayments\ApiSDK\Enum\DiscountType::FIXED;     // Aplicará um valor fixo em centavos com base na data de pagamento.
AditumPayments\ApiSDK\Enum\DiscountType::DAILY;     // Aplicará um valor diário em centavos com base na data de pagamento.
```