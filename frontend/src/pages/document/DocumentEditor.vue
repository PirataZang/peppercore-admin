<template>
  <div class="page fade-in editor-page">
    <PageHeader
      :title="isEdit ? 'Editar Documento' : 'Novo Documento'"
      :subtitle="isEdit ? 'Atualize o conteúdo deste documento.' : 'Crie um documento para reaproveitar depois.'"
      :icon="isEdit ? 'fa-solid fa-file-pen' : 'fa-solid fa-file-circle-plus'"
    />

    <div v-if="loading" class="loading-state">
      <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
      <span>Carregando documento...</span>
    </div>

    <template v-else>
      <div class="doc-card">
        <div class="form-grid">
          <Input
            class="col-12 doc-name"
            label="Título do documento"
            v-model="form.name"
            placeholder="Documento sem título"
          />
          <Input class="col-5" label="Assunto" v-model="form.subject" placeholder="Assunto do documento" />
          <Input
            class="col-5"
            label="Descrição breve"
            v-model="form.description"
            placeholder="Até 25 caracteres"
            maxlength="25"
          />
          <Switch class="col-2 doc-active" v-model="form.active" label="Situação" :text-label="form.active ? 'Ativo' : 'Inativo'" />
        </div>
      </div>

      <div class="doc-toolbar">
        <button type="button" class="tb-btn" title="Desfazer" @click="exec('undo')">
          <i class="fa-solid fa-rotate-left" />
        </button>
        <button type="button" class="tb-btn" title="Refazer" @click="exec('redo')">
          <i class="fa-solid fa-rotate-right" />
        </button>

        <span class="tb-sep" />

        <select class="tb-select" title="Estilo" @change="applyBlock($event.target.value)">
          <option value="p">Texto normal</option>
          <option value="h1">Título 1</option>
          <option value="h2">Título 2</option>
          <option value="h3">Título 3</option>
          <option value="blockquote">Citação</option>
        </select>

        <span class="tb-sep" />

        <button type="button" class="tb-btn" title="Negrito" @click="exec('bold')">
          <i class="fa-solid fa-bold" />
        </button>
        <button type="button" class="tb-btn" title="Itálico" @click="exec('italic')">
          <i class="fa-solid fa-italic" />
        </button>
        <button type="button" class="tb-btn" title="Sublinhado" @click="exec('underline')">
          <i class="fa-solid fa-underline" />
        </button>
        <button type="button" class="tb-btn" title="Tachado" @click="exec('strikeThrough')">
          <i class="fa-solid fa-strikethrough" />
        </button>

        <span class="tb-sep" />

        <label class="tb-btn tb-color" title="Cor do texto">
          <i class="fa-solid fa-font" />
          <input type="color" @input="exec('foreColor', $event.target.value)" />
        </label>
        <label class="tb-btn tb-color" title="Cor de destaque">
          <i class="fa-solid fa-highlighter" />
          <input type="color" @input="exec('hiliteColor', $event.target.value)" />
        </label>

        <span class="tb-sep" />

        <button type="button" class="tb-btn" title="Alinhar à esquerda" @click="exec('justifyLeft')">
          <i class="fa-solid fa-align-left" />
        </button>
        <button type="button" class="tb-btn" title="Centralizar" @click="exec('justifyCenter')">
          <i class="fa-solid fa-align-center" />
        </button>
        <button type="button" class="tb-btn" title="Alinhar à direita" @click="exec('justifyRight')">
          <i class="fa-solid fa-align-right" />
        </button>
        <button type="button" class="tb-btn" title="Justificar" @click="exec('justifyFull')">
          <i class="fa-solid fa-align-justify" />
        </button>

        <span class="tb-sep" />

        <button type="button" class="tb-btn" title="Lista com marcadores" @click="exec('insertUnorderedList')">
          <i class="fa-solid fa-list-ul" />
        </button>
        <button type="button" class="tb-btn" title="Lista numerada" @click="exec('insertOrderedList')">
          <i class="fa-solid fa-list-ol" />
        </button>
        <button type="button" class="tb-btn" title="Diminuir recuo" @click="exec('outdent')">
          <i class="fa-solid fa-outdent" />
        </button>
        <button type="button" class="tb-btn" title="Aumentar recuo" @click="exec('indent')">
          <i class="fa-solid fa-indent" />
        </button>

        <span class="tb-sep" />

        <button type="button" class="tb-btn" title="Inserir link" @click="insertLink">
          <i class="fa-solid fa-link" />
        </button>
        <button type="button" class="tb-btn" title="Linha horizontal" @click="exec('insertHorizontalRule')">
          <i class="fa-solid fa-ruler-horizontal" />
        </button>
        <button type="button" class="tb-btn" title="Limpar formatação" @click="exec('removeFormat')">
          <i class="fa-solid fa-text-slash" />
        </button>

        <span class="tb-sep" />

        <Select
          class="tb-variable-select"
          v-model="selectedVariable"
          :options="variableOptions"
          placeholder="Inserir variável..."
          search
          :clearable="false"
          @change="handleVariableSelect"
        />

        <span class="page-counter">
          <i class="fa-solid fa-file-lines" aria-hidden="true" />
          {{ pageCount }} {{ pageCount === 1 ? 'folha' : 'folhas' }}
        </span>
      </div>

      <div class="doc-sheet-wrap">
        <div class="doc-sheet-shell">
          <div
            ref="editorRef"
            class="doc-sheet"
            contenteditable="true"
            v-html="initialContent"
            @mouseup="saveSelection"
            @keyup="saveSelection"
          />
          <div class="page-breaks" aria-hidden="true">
            <div
              v-for="n in pageCount - 1"
              :key="n"
              class="page-break-line"
              :style="{ top: n * PAGE_HEIGHT + 'px' }"
            >
              <span class="page-break-label">Fim da folha {{ n }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="error" class="form-error">{{ error }}</div>

      <FormActions :saving="saving" @cancel="$router.push('/document')" @click="handleFormActionsClick" />
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Input from '@/components/utils/Input.vue'
import Select from '@/components/utils/Select.vue'
import Switch from '@/components/utils/Switch.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FormActions from '@/components/ui/FormActions.vue'
import { DOCUMENT_VARIABLES } from '@/config/documentVariables'

const router = useRouter()
const route = useRoute()

const documentId = computed(() => route.params.id)
const isEdit = computed(() => !!documentId.value)

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const editorRef = ref(null)
const initialContent = ref('')

const form = ref({ name: '', subject: '', description: '', active: true })

// A4 a 96dpi (297mm), só um guia visual de onde a impressão quebraria a
// folha — não faz reflow real de conteúdo entre "páginas" separadas.
const PAGE_HEIGHT = 1123
const sheetHeight = ref(0)
const pageCount = computed(() => Math.max(1, Math.ceil(sheetHeight.value / PAGE_HEIGHT)))
let resizeObserver = null

// A toolbar (principalmente o Select de variáveis, que foca um <input> de
// busca ao abrir) rouba o foco do editor e some com o cursor; guardamos o
// último Range colocado dentro do editor pra poder devolver o cursor pro
// lugar certo antes de qualquer comando, em vez de cair sempre no topo.
let savedRange = null

const saveSelection = () => {
  const selection = window.getSelection()
  if (selection && selection.rangeCount > 0 && editorRef.value?.contains(selection.anchorNode)) {
    savedRange = selection.getRangeAt(0).cloneRange()
  }
}

const restoreSelection = () => {
  editorRef.value?.focus()
  if (!savedRange) return
  const selection = window.getSelection()
  selection.removeAllRanges()
  selection.addRange(savedRange)
}

const exec = (command, value = null) => {
  restoreSelection()
  document.execCommand(command, false, value)
  saveSelection()
}

const applyBlock = (tag) => {
  restoreSelection()
  document.execCommand('formatBlock', false, tag)
  saveSelection()
}

const insertLink = () => {
  const url = window.prompt('URL do link:')
  if (!url) return
  exec('createLink', url)
}

// Select.vue não tem optgroup — o prefixo "Cliente - "/"Projeto - " no label já
// deixa a lista filtrável por grupo ao digitar, sem precisar de agrupamento visual.
const variableOptions = DOCUMENT_VARIABLES.map((v) => ({ value: v.key, label: v.label }))
const selectedVariable = ref(null)

const handleVariableSelect = (key) => {
  if (!key) return
  exec('insertText', `{{${key}}}`)
  selectedVariable.value = null
}

const fetchDocument = async () => {
  if (!isEdit.value) return

  loading.value = true
  error.value = ''
  try {
    const response = await apiFetch(`/api/documents/${documentId.value}`)
    if (!response.ok) {
      throw new Error('Não foi possível carregar este documento.')
    }
    const data = await response.json()
    form.value.name = data.name
    form.value.subject = data.subject || ''
    form.value.description = data.description || ''
    form.value.active = data.active ?? true
    initialContent.value = data.content || ''
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  saving.value = true
  error.value = ''

  const payload = {
    name: form.value.name || 'Documento sem título',
    subject: form.value.subject || null,
    description: form.value.description || null,
    active: form.value.active,
    content: editorRef.value?.innerHTML || '',
  }

  const url = isEdit.value ? `/api/documents/${documentId.value}` : '/api/documents'
  const method = isEdit.value ? 'PUT' : 'POST'

  try {
    const response = await apiFetch(url, {
      method,
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao salvar documento.')
    }

    router.push('/document')
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

// Sem <form> ao redor (a área editável não pode ficar dentro de um <form>,
// o Enter dispararia submit no meio da digitação) — o botão Salvar não tem
// nada para submeter, então o clique é pego aqui por delegação.
const handleFormActionsClick = (event) => {
  if (event.target.closest('button[type="submit"]')) submitForm()
}

onMounted(async () => {
  await fetchDocument()
  await nextTick()
  if (editorRef.value) {
    resizeObserver = new ResizeObserver(() => {
      sheetHeight.value = editorRef.value.offsetHeight
    })
    resizeObserver.observe(editorRef.value)
  }
})

onBeforeUnmount(() => {
  resizeObserver?.disconnect()
})
</script>

<style scoped>
.editor-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.doc-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 16px;
}

.doc-name :deep(.field-input) {
  padding: 10px 12px;
  font-size: 1.1rem;
  font-weight: 600;
}

.doc-active {
  justify-content: flex-end;
}

.doc-active :deep(.switch-wrapper) {
  height: 42px;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 24px;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.doc-toolbar {
  position: sticky;
  top: 0;
  z-index: 5;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 8px;
}

.tb-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: var(--color-text-secondary);
  cursor: pointer;
  font-size: 0.875rem;
  position: relative;
}

.tb-btn:hover {
  background: var(--color-bg-muted);
  color: var(--color-text);
}

.tb-color input[type='color'] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}

.tb-select {
  height: 34px;
  padding: 0 8px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  color: var(--color-text);
  font-family: var(--font-sans);
  font-size: 0.8125rem;
  cursor: pointer;
}

.tb-variable-select {
  width: 220px;
  flex-shrink: 0;
}

.tb-variable-select :deep(.select-control) {
  min-height: 34px;
}

.tb-sep {
  width: 1px;
  height: 22px;
  background: var(--color-border);
  margin: 0 4px;
}

.page-counter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
  padding: 0 10px;
  height: 34px;
  color: var(--color-text-muted);
  font-size: 0.8125rem;
  white-space: nowrap;
}

.doc-sheet-wrap {
  display: flex;
  justify-content: center;
  background: var(--color-bg-app);
  border-radius: 16px;
  padding: 24px;
}

.doc-sheet-shell {
  position: relative;
  width: 100%;
  max-width: 850px;
}

.doc-sheet {
  width: 100%;
  min-height: 1123px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  box-shadow: var(--shadow-md);
  padding: 64px 72px;
  color: var(--color-text);
  font-size: 0.9375rem;
  line-height: 1.6;
  outline: none;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.page-breaks {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.page-break-line {
  position: absolute;
  left: 0;
  right: 0;
  border-top: 2px dashed var(--color-border-strong);
}

.page-break-label {
  position: absolute;
  top: 4px;
  right: 0;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  background: var(--color-bg-app);
  color: var(--color-text-muted);
  font-size: 0.6875rem;
  font-weight: 600;
  white-space: nowrap;
}

.doc-sheet :deep(h1) {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 12px;
}

.doc-sheet :deep(h2) {
  font-size: 1.375rem;
  font-weight: 700;
  margin: 0 0 10px;
}

.doc-sheet :deep(h3) {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0 0 8px;
}

.doc-sheet :deep(blockquote) {
  margin: 0 0 12px;
  padding-left: 16px;
  border-left: 3px solid var(--color-border-strong);
  color: var(--color-text-secondary);
}

.doc-sheet :deep(p) {
  margin: 0 0 12px;
}

.doc-sheet :deep(a) {
  color: var(--color-secondary);
}
</style>
