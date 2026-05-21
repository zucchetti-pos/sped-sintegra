# sped-sintegra — Constituição do Módulo

> SDK PHP para geração de arquivos SINTEGRA (Sistema Integrado de Informações sobre Operações Interestaduais com Mercadorias e Serviços). SDK externo — não modificar sem aprovação.

## Identidade
- Módulo: sped-sintegra — SDK geração de arquivos SINTEGRA
- Fork: `nfephp-org/sped-sintegra`
- Parte do monorepo: zweb-projects

## Stack
- **Linguagem:** PHP ≥7.4
- **Dependências:** `nfephp-org/sped-common` · `brazanation/documents` · `nfephp-org/sped-gtin`
- **Testes:** PHPUnit ^9.3 + phpcs (PSR-2) + phpstan ^1.4
- **Gerenciador:** Composer
- **CI:** GitHub Actions

## Estrutura de pastas
```
sped-sintegra/
├── src/
│   ├── Blocks/     # Blocos do arquivo SINTEGRA (Bloco 0, 1, 5, 6, 7...)
│   ├── Common/     # Classes comuns (linha, registro base)
│   ├── Elements/   # Elementos de cada registro
│   └── Sintegra.php # Classe principal de geração
├── tests/          # Testes PHPUnit
└── phpunit.xml
```

## Comandos do projeto
```bash
# Instalar dependências
composer install

# Executar testes
composer test
# ou diretamente:
vendor/bin/phpunit

# Lint PSR-2
vendor/bin/phpcs --standard=psr2 src/
vendor/bin/phpcbf --standard=psr2 src/

# Análise estática (nível 6)
vendor/bin/phpstan analyse --level=6 src/
```

## Restrições
- **CRÍTICO:** SINTEGRA é obrigação fiscal estadual — arquivos inválidos geram autuação
- SDK externo (fork nfephp-org) — modificações exigem aprovação explícita
- PHP mínimo: 7.4
- Dados fiscais (CNPJ, IE, valores de operações) nunca em logs
- Leiaute de registros é normativo — não alterar sem validação fiscal junto à SEFAZ
