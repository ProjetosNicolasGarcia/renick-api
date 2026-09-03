# Contexto e Padrões Globais: Back-end (Renick Kids)
<!-- AUDIT_TOKEN: RENICK_BACKEND_GLOBAL_VERIFIED -->

## 1. Stack e Infraestrutura
* **Framework:** Laravel (PHP 8.3+) com arquitetura em camadas e desacoplamento de responsabilidade.
* **Ambiente:** Docker via WSL2, banco de dados MySQL, Redis para filas e caching.
* **Comunicação:** API RESTful stateless, respostas estruturadas exclusivamente via `JsonResource`, CORS rigorosamente configurado.
* **Testes:** PHPUnit (Feature Tests mandatórios cobrindo endpoints contra o contrato).

## 2. Contrato da API e Fronteiras de Escopo
* Toda implementação deve seguir estritamente a especificação OpenAPI (`openapi.yaml`).
* É proibido criar rotas, parâmetros de query ou chaves de payload que não constem na especificação a menos que sob ordem explícita.


## 3. Padrões de Código e Arquitetura (SRP)
* **Nomenclatura:**
  * Classes e Interfaces: `PascalCase`.
  * Métodos, propriedades e variáveis: `camelCase`.
  * Colunas de tabelas, chaves de payload e parâmetros de rota: `snake_case`.
  * Constantes: `SCREAMING_SNAKE_CASE` (eliminação total de números mágicos).
  * Métodos booleanos: formulados como perguntas diretas (`isActive()`, `hasPermission()`).
* **Responsabilidades de Camada:**
  * `Routes/api.php`: Apenas definição de URI, métodos HTTP e middlewares aplicados.
  * `FormRequest`: Sanitização, regras de validação e early return com status `422`. Proibido realizar queries de negócio.
  * `Controller`: Orquestração pura. Recebe dados validados, invoca o `Service` correspondente e retorna `JsonResource` com o HTTP Status Code adequado (`200`, `201`, `204`). Proibido queries diretas no controller.
  * `Service`: Camada de domínio. Executa cálculos, regras de validação de negócio, transações atômicas (`DB::transaction`) e despacho de eventos/jobs.
  * `Model/Eloquent`: Mapeamento relacional, casts de dados e scopes reutilizáveis. Proibido acoplar regras de negócio de múltiplos domínios dentro do Model.
  * `Queue / Jobs`: Uso mandatório para processos assíncronos (e-mails, webhooks, integrações de APIs externas).

## 4. Complexidade de Código e Comentários
* **Cláusulas Guarda:** Uso mandatório de *Early Return* para validações prévias, eliminando aninhamentos de blocos `else` ou `elseif`.
* **Complexidade Ciclomática:** Máximo de 2 níveis de indentação por método.
* **Comentários:** Para cada método, comentários apenas em texto minúsculo, objetivos, sem decorações visuais ou separadores estilísticos, explicando a função deles.

## 5. Diretrizes de Testes Automatizados (PHPUnit)
* Toda nova rota/funcionalidade exige Feature Tests cobrindo:
  1. Caminho feliz com asserção da estrutura do JSON retornado (`assertJsonStructure`).
  2. Validações de payload inválido (esperado status `422`).
  3. Erros de integridade de negócio (ex.: itens sem estoque, duplicidades, status inválido).