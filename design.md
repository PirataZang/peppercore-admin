# PepperCore Admin — Design System & Arquitetura

Documento de referência para manter o projeto consistente: layout moderno, código limpo,
arquitetura limpa. Qualquer tela ou endpoint novo deve seguir isto antes de inventar um
padrão próprio. Se um padrão daqui não serve mais, atualize este arquivo junto com a mudança.

## Stack

- **Frontend**: Vue 3 (Composition API, `<script setup>`), Vue Router 4, Vite, SCSS, AG Grid, SweetAlert2.
- **Backend**: Laravel 13 (PHP 8.3), PostgreSQL, Redis, auth via bearer token custom (`AuthMiddleware`).

## 1. Design tokens

Fonte única de verdade: [`frontend/src/styles/_colors.scss`](frontend/src/styles/_colors.scss), exposta como
CSS vars em [`main.scss`](frontend/src/styles/main.scss) (`--color-*`). Nunca hardcode hex em componente novo —
use a var. Os aliases legados (`--primary`, `--text-secondary`, `--border-color`, etc.) existem só para telas
antigas (Dashboard, Modal) — não usar em código novo, preferir sempre `--color-*`.

| Token | Valor | Uso |
|---|---|---|
| `--color-primary` | `#e11d48` | Marca, ação primária, foco |
| `--color-secondary` | `#4f46e5` | Acentos secundários, links de destaque |
| `--color-success` / `-soft` | `#059669` / `#ecfdf5` | Estado positivo (pago, running, conectado) |
| `--color-warning` / `-soft` | `#d97706` / `#fffbeb` | Estado de atenção (pendente, restarting) |
| `--color-danger` / `-soft` | `#dc2626` / `#fef2f2` | Estado negativo (atrasado, erro, exclusão) |
| `--color-info` / `-soft` | `#2563eb` / `#eff6ff` | Ação neutra informativa (botão "Incluir") |
| `--color-bg-app` | `#f4f6f9` | Fundo da área de conteúdo |
| `--color-bg-card` / `-muted` / `-hover` | `#fff` / `#f1f5f9` / `#f8fafc` | Superfícies |
| `--color-text` / `-secondary` / `-muted` / `-faint` | escala de cinza-azulado | Hierarquia tipográfica |
| `--color-border` / `-strong` | `#e2e8f0` / `#cbd5e1` | Bordas |

Espaçamento: sem escala fixa em var — usar múltiplos de 4px (8/12/16/20/24). Raio: `--radius-md` (10px, controles),
`--radius-lg`/`--radius-xl` (12–16px, cards/painéis), `--radius-full` (pills/badges/avatares). Sombra:
`--shadow-sm` (cards em repouso), `--shadow-md` (hover/elevado), `--shadow-lg` (dropdowns/modais).
Fonte: `--font-sans` = Outfit.

## 2. Componentes base (`components/utils` e `components/ui`)

Reutilize antes de criar algo novo:

- **Button** (`utils/Button.vue`) — variants `primary | create | edit | danger | secondary | ghost`, sizes `sm | md | lg`.
  Convenção de cor por ação: Incluir = `create` (azul/info), Alterar = `edit` (verde/success), Excluir = `danger` (vermelho).
- **Input / Select / TextArea / Switch / ColorField** (`utils/`) — todos usam `defineModel`, prop `label`, mesmo
  `field-shell` visual (borda, foco com anel `--color-primary-soft`).
- **Campo booleano** (ativo/inativo, sim/não, ligado/desligado) sempre usa `Switch` (`utils/Switch.vue`) — nunca
  checkbox nativo, nunca `Select` com opções Sim/Não.
- **`[Model]Select`** (ex. `utils/DocumentSelect.vue`, `ClientSelect.vue`, `ProjectSelect.vue`) — padrão obrigatório
  sempre que for pedido um combo para escolher um registro de um model (Documento, Cliente, Projeto, etc.): um
  componente fino que só envolve `Select.vue`, busca as opções via `apiFetch` no `onMounted` (`{value: id, label:
  <campo mais legível>}`) e expõe `defineModel()`. Não redeclare `multiple`/`clearable`/`search`/`placeholder`/
  `label` como props próprias — esses atributos passados pelo componente pai caem direto no `Select` interno via
  fallthrough attrs (Vue já faz isso sozinho quando o template tem um único elemento raiz), então o `[Model]Select`
  fica sempre padrão e nunca precisa ser redigido pra suportar essas variações.
- **Modal** (`utils/Modal.vue`) — `v-model`, slots `default`/`footer`, fecha com Esc ou clique fora. Prop `size`
  (`sm` 440px padrão / `md` 720px·80vh / `lg` 960px·85vh) define largura/altura — só passe `width`/`height` direto
  para um caso realmente fora do padrão, eles sempre vencem `size`. Confirmações e formulários curtos usam `sm`
  (padrão); formulários maiores e listas usam `md`; nada usa `lg` ainda. Fundo do modal (corpo e rodapé de botões)
  é sempre branco sólido — nunca reintroduza overlay escuro no rodapé.
- **AgGrid** (`utils/AgGrid.vue`) — grid padrão para listagens, com paginação customizada embutida (não usar a
  paginação nativa do AG Grid). `columnTypes` prontos: `date`, `datetime`, `money`.
- **PageHeader** (`ui/PageHeader.vue`) — título + subtítulo + ícone, primeiro elemento de toda página.
- **FormActions** (`ui/FormActions.vue`) — par Salvar/Cancelar padrão de todo formulário.
- **StatusBadge** (`ui/StatusBadge.vue`) — pill de status (dot + texto) com `variant`
  `success | warning | danger | info | neutral`. Use para qualquer estado (pagamento, tipo de projeto, conectividade)
  em vez de reinventar uma badge — nunca comunique estado só por cor, sempre com texto junto.
- **ActivityLogModal** (`ui/ActivityLogModal.vue`) — modal genérico de "Histórico de Alterações" (busca, filtro por
  Criações/Edições/Exclusões, diff ANTES/DEPOIS por campo). Props `subject-type` (chave curta registrada no backend,
  ex. `project`, `client`, `transaction`) + `subject-id`. Consome `GET /api/activity-log`. Reaproveite em qualquer
  tela nova que precise mostrar o histórico de um registro — não recrie a busca/filtro/diff do zero.
- **EChart** (`ui/EChart.vue`) — wrapper fino sobre `echarts` (já em `package.json`, sem `vue-echarts`): prop
  `option` (spec do ECharts) + `height`, cuida de `init`/`resize`/`dispose`. Regras de gráfico: uma métrica por
  série usa **uma cor sólida só** (não pinte cada barra de uma cor — isso é trabalho do eixo/rótulo, não da cor);
  cores categóricas (uma por série real) só quando há de fato múltiplas séries. Nunca use as cores semânticas de
  status (`success`/`warning`/`danger`) como paleta categórica — elas são reservadas para estado. Rótulo direto no
  topo/ponta da barra, tooltip sempre ligado, sem eixo duplo.

## 3. Utilitários globais (`styles/form.scss`)

Já existem classes globais prontas — não recriar em `<style scoped>` de cada página:
`.page`/`.fade-in` (wrapper de página com fade), `.page-header` (usada por `PageHeader.vue`),
`.form-grid` + `.col-1`…`.col-12` (grid 12 colunas responsivo, colapsa para 1 coluna em telas pequenas),
`.form-card` (card branco padrão de formulário), `.form-actions`, `.form-error`, `.toolbar`/`.toolbar__actions`,
`.search-field`. `.field-label` e `.field-control` também são globais e usados pelos componentes de `utils/`.

## 4. Padrões de página

- Toda página começa com `<div class="page fade-in">` + `<PageHeader>`.
- **Listagem** (`[Model]List.vue`): toolbar com busca (`Input type="search"` + debounce de 400ms) à esquerda,
  ações (Incluir/Alterar/Excluir) à direita, depois `AgGrid` dentro de `.grid-wrap`. Clique na linha abre o
  recurso (form de edição, ou uma tela de detalhe quando o recurso tiver sub-áreas, como Projetos).
- **Formulário** (`[Model]Form.vue`): único componente para criar/editar (ver `.agents/AGENTS.md` — rotas
  `/model/form` e `/model/form/:id`, detecção de modo por `route.params.id`), campos em `.form-grid` com
  colunas `.col-N` (grid de 12), `FormActions` no fim.
- **Detalhe com sub-área única** (ex.: `ProjectDetail.vue`): `PageHeader` + card de resumo + um painel abaixo
  (KPIs + gráfico + lista) para a sub-área — sem abas quando só existe uma. Se um recurso ganhar uma segunda
  sub-área de verdade, aí sim introduza abas (carregando os dados de cada aba sob demanda, só ao ser aberta).
- Nunca coloque toggle/botão no layout que não faz nada (ex.: o antigo botão de esconder sidebar foi removido —
  o menu lateral fica sempre visível como rail e expande no hover).

## 5. Arquitetura backend (Laravel)

Controller fino → Service → Model. Nunca lógica de negócio no Controller.

- **Model**: atributos `#[Fillable([...])]` / `#[Hidden([...])]` (não usar `protected $fillable` tradicional
  neste projeto), casts via método `casts()`.
- **Service** (`app/Service/`): uma classe por recurso, métodos `list/index/create/update/delete` recebendo
  arrays já validados. Um recurso com sub-recursos (ex. `Project` → `Transaction`) ganha um Service próprio para
  o sub-recurso (`TransactionService`), não vira método extra no Service do pai.
- **Controller**: valida (`$request->validate`) e delega ao Service, devolve `response()->json(...)`.
- **Rotas**: REST simples sob `Route::middleware('auth')->prefix('recurso')`: `GET /`, `GET /{id}`, `POST /`,
  `PUT /{id}`, `DELETE /{id}`. Sub-recursos (ex. `/projects/{id}/transactions`) viram controller próprio
  (`[Recurso]Controller` do sub-recurso) quando o recurso principal já tem CRUD; rotas mais específicas (ex.
  `/{id}/transactions/summary`) sempre declaradas antes das genéricas com parâmetro (`/{id}`) no mesmo grupo —
  Laravel casa rotas por ordem de declaração, não por especificidade como o Vue Router.
- **Auditoria** (`app/Models/Concerns/Auditable.php`): trait fina sobre `spatie/laravel-activitylog` — qualquer
  model que precise de histórico de alterações usa `use Auditable;` e nada mais (loga só os atributos fillable
  que realmente mudaram, com quem fez e quando). Hoje em `Project`, `Client` e `Transaction`; para adicionar a um
  novo model, aplique a trait e registre a chave curta em `ActivityLogController::SUBJECT_MAP`. Para um model com
  campo sensível (ex. senha), sobrescreva `getActivitylogOptions()` no próprio model adicionando
  `->logExcept([...])`. O causer é resolvido via `Auth::guard()->user()` — por isso `AuthMiddleware` chama
  `Auth::setUser($user)` além de `setUserResolver`, mesmo sem sessão/cookie.

## 6. Arquitetura frontend (Vue)

- `pages/<recurso>/` — telas roteadas. `components/ui/` — componentes de composição de página (badges, headers,
  form actions). `components/utils/` — inputs e primitivos de formulário/grid. `components/layout/` — casca do
  app (sidebar, layout). `config/` — mapeamentos estáticos (ex. status → cor/label). `services/api.js` — único
  ponto de fetch autenticado (`apiFetch`, injeta Bearer token, trata 401 deslogando). `plugins/swal.js` — único
  ponto de confirm/alert/toast (`swal.confirm`, `swal.confirmDelete`, `swal.toastSuccess/Error`).
- Nunca `fetch()` direto numa página — sempre `apiFetch`. Nunca `window.confirm`/`alert` — sempre `swal`.

## 7. Variáveis de documento

Documentos (`pages/document/DocumentEditor.vue`) podem conter tokens `{{Chave}}` no meio do conteúdo, inseridos
pelo dropdown "Inserir variável" da toolbar. Na emissão (`GET /api/documents/{id}/emit`), se a requisição
informar `client_id` e/ou `project_id` (escolhidos no modal de emissão via `ClientSelect`/`ProjectSelect`), o
backend (`DocumentService::documentVariables()`) substitui cada token pelo dado real antes de gerar o PDF —
sempre com `e()` (HTML-escaped), então o valor aparece sempre como texto literal, nunca interpretado como marcação.

A lista de variáveis vive em dois lugares que precisam ficar em sincronia manual: o dropdown do editor
(`frontend/src/config/documentVariables.js`) e o mapa de substituição do backend (`DocumentService::documentVariables()`).
Ao adicionar uma variável nova, atualize os dois. A chave usa o nome do campo em inglês (igual à coluna/model);
o rótulo mostrado ao usuário é sempre traduzido:

| Variável | Rótulo exibido |
|---|---|
| `Cliente.name` | Cliente - Nome |
| `Cliente.email` | Cliente - E-mail |
| `Cliente.phone` | Cliente - Telefone |
| `Cliente.address` | Cliente - Endereço |
| `Cliente.document` | Cliente - CPF/CNPJ |
| `Cliente.zip_code` | Cliente - CEP |
| `Cliente.street_name` | Cliente - Rua |
| `Cliente.street_number` | Cliente - Número |
| `Cliente.neighborhood` | Cliente - Bairro |
| `Cliente.city` | Cliente - Cidade |
| `Cliente.state` | Cliente - UF |
| `Cliente.description` | Cliente - Descrição |
| `Projeto.name` | Projeto - Nome |
| `Projeto.type` | Projeto - Tipo |
| `Projeto.domain` | Projeto - Domínio |
| `Projeto.client_name` | Projeto - Cliente Vinculado |
| `Projeto.client_contact` | Projeto - Contato do Cliente |
| `Projeto.monthly_value` | Projeto - Mensalidade |
| `Projeto.due_day` | Projeto - Dia de Vencimento |
| `Projeto.payment_status` | Projeto - Situação de Pagamento |
| `Projeto.description` | Projeto - Descrição |
| `Valor` | Valor (informado na emissão) |

`Cliente.*`/`Projeto.*` cobrem praticamente todos os campos fillable dos models `Client`/`Project` — de propósito
ficam fora o `active` (flag interna, não é dado pra imprimir) e IDs/chaves estrangeiras. `Valor` não vem de
model nenhum: é digitado à mão no modal de emissão (campo livre para orçamento/cobrança), por isso não tem
prefixo de model.

## 8. Checklist antes de abrir PR

- [ ] Usa tokens `--color-*` existentes, sem hex novo solto no componente.
- [ ] Reaproveita `Button`/`Input`/`Select`/`Modal`/`StatusBadge`/`AgGrid` em vez de recriar.
- [ ] Todo campo booleano usa `Switch`, nunca checkbox nativo.
- [ ] Segue a convenção de nomes/rotas de `[Model]List.vue` + `[Model]Form.vue`.
- [ ] Controller fino, regra de negócio no Service.
- [ ] Nenhum botão ou ícone decorativo sem função real.
