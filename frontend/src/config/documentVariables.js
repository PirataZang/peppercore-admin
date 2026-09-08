// Variáveis {{Chave}} disponíveis nos documentos — substituídas pelos dados do
// cliente/projeto vinculado no momento da emissão (ver DocumentService::emit
// no backend e a seção "Variáveis de documento" em design.md). Ao adicionar
// uma variável nova, espelhe a chave e o rótulo também no backend.
export const DOCUMENT_VARIABLES = [
  { key: 'Cliente.name', label: 'Cliente - Nome' },
  { key: 'Cliente.email', label: 'Cliente - E-mail' },
  { key: 'Cliente.phone', label: 'Cliente - Telefone' },
  { key: 'Cliente.address', label: 'Cliente - Endereço' },
  { key: 'Cliente.document', label: 'Cliente - CPF/CNPJ' },
  { key: 'Cliente.zip_code', label: 'Cliente - CEP' },
  { key: 'Cliente.street_name', label: 'Cliente - Rua' },
  { key: 'Cliente.street_number', label: 'Cliente - Número' },
  { key: 'Cliente.neighborhood', label: 'Cliente - Bairro' },
  { key: 'Cliente.city', label: 'Cliente - Cidade' },
  { key: 'Cliente.state', label: 'Cliente - UF' },
  { key: 'Cliente.description', label: 'Cliente - Descrição' },
  { key: 'Projeto.name', label: 'Projeto - Nome' },
  { key: 'Projeto.type', label: 'Projeto - Tipo' },
  { key: 'Projeto.domain', label: 'Projeto - Domínio' },
  { key: 'Projeto.client_name', label: 'Projeto - Cliente Vinculado' },
  { key: 'Projeto.client_contact', label: 'Projeto - Contato do Cliente' },
  { key: 'Projeto.monthly_value', label: 'Projeto - Mensalidade' },
  { key: 'Projeto.due_day', label: 'Projeto - Dia de Vencimento' },
  { key: 'Projeto.payment_status', label: 'Projeto - Situação de Pagamento' },
  { key: 'Projeto.description', label: 'Projeto - Descrição' },
  { key: 'Valor', label: 'Valor (informado na emissão)' },
]
