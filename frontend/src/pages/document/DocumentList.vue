<template>
  <div class="page fade-in">
    <PageHeader
      title="Documentos"
      subtitle="Documentos salvos para consulta e reaproveitamento."
      icon="fa-solid fa-file-lines"
    />

    <div class="list-toolbar">
      <Input
        class="search-input"
        type="search"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Buscar documentos por nome..."
      >
        <template #prefix>
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
        </template>
      </Input>

      <div class="list-toolbar__actions">
        <Button
          variant="create"
          icon="fa-solid fa-plus"
          label="Novo Documento"
          @click="$router.push('/document/form')"
        />
        <Button
          variant="edit"
          icon="fa-solid fa-pen-to-square"
          label="Alterar"
          :disabled="selectedDocuments.length !== 1"
          @click="editSelected"
        />
        <Button
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="selectedDocuments.length > 0 ? `Excluir (${selectedDocuments.length})` : 'Excluir'"
          :disabled="selectedDocuments.length === 0"
          @click="deleteSelectedDocuments"
        />
        <Button
          variant="primary"
          icon="fa-solid fa-file-export"
          label="Emitir"
          @click="openEmitModal"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="documentsData"
        :columnDefs="columnDefs"
        :currentPage="currentPage"
        :pageSize="pageSize"
        :totalRows="totalRows"
        :selectable="true"
        @update:page="handlePageChange"
        @update:pageSize="handlePageSizeChange"
        @update:selection="handleSelectionChange"
        @row-dblclick="handleRowDoubleClick"
      />
    </div>

    <Modal v-model="emitModalOpen" title="Emitir Documento">
      <DocumentSelect v-model="emitDocumentId" />

      <template #footer>
        <Button variant="secondary" label="Cancelar" @click="emitModalOpen = false" />
        <Button variant="primary" icon="fa-solid fa-file-export" label="Emitir" @click="handleEmit" />
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AgGrid from '@/components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import Modal from '@/components/utils/Modal.vue'
import DocumentSelect from '@/components/utils/DocumentSelect.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const router = useRouter()

const documentsData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const selectedDocuments = ref([])
const emitModalOpen = ref(false)
const emitDocumentId = ref(null)
let searchTimeout = null

const columnDefs = ref([
  { field: 'name', headerName: 'Nome', flex: 1, sortable: true, filter: true },
  { field: 'subject', headerName: 'Assunto', flex: 1, sortable: true, filter: true },
  { field: 'description', headerName: 'Descrição breve', flex: 1, sortable: true, filter: true },
  { field: 'active', headerName: 'Situação', type: 'boolean', width: 130, sortable: true },
  { field: 'updated_at', headerName: 'Última alteração', type: 'datetime', width: 200, sortable: true },
  { field: 'created_at', headerName: 'Criado em', type: 'datetime', width: 200, sortable: true },
])

const fetchDocuments = async () => {
  try {
    const url = `/api/documents?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
    const response = await apiFetch(url).then((res) => res.json())
    documentsData.value = response.data
    totalRows.value = response.total
    selectedDocuments.value = []
  } catch (err) {
    console.error('Erro ao buscar lista de documentos:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchDocuments()
  }, 400)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchDocuments()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchDocuments()
}

const handleSelectionChange = (selection) => {
  selectedDocuments.value = selection
}

const handleRowDoubleClick = (rowData) => {
  router.push(`/document/form/${rowData.id}`)
}

const editSelected = () => {
  if (selectedDocuments.value.length !== 1) return
  router.push(`/document/form/${selectedDocuments.value[0].id}`)
}

const deleteSelectedDocuments = async () => {
  if (selectedDocuments.value.length === 0) return

  const targets = [...selectedDocuments.value]
  const ok = await swal.confirmDelete({
    count: targets.length,
    entity: 'documento',
    entityPlural: 'documentos',
  })
  if (!ok) return

  try {
    for (const document of targets) {
      await apiFetch(`/api/documents/${document.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    await fetchDocuments()
    swal.toastSuccess(
      targets.length > 1 ? 'Documentos excluídos com sucesso!' : 'Documento excluído com sucesso!',
    )
  } catch (err) {
    console.error('Falha ao excluir documentos:', err)
    swal.toastError('Falha ao excluir documento(s).')
  }
}

const openEmitModal = () => {
  const single = selectedDocuments.value.length === 1 ? selectedDocuments.value[0] : null
  emitDocumentId.value = single?.active ? single.id : null
  emitModalOpen.value = true
}

// Baixa o PDF via apiFetch (precisa do header Authorization, por isso não dá
// pra só navegar num <a href> direto) e força o download com um link temporário.
const handleEmit = async () => {
  if (!emitDocumentId.value) {
    swal.toastWarning('Selecione um documento para emitir.')
    return
  }

  try {
    const response = await apiFetch(`/api/documents/${emitDocumentId.value}/emit`)
    if (!response.ok) {
      throw new Error('Não foi possível gerar o PDF deste documento.')
    }

    const blob = await response.blob()
    const disposition = response.headers.get('Content-Disposition') || ''
    const filename = disposition.match(/filename="?([^"]+)"?/)?.[1] || 'documento.pdf'

    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    URL.revokeObjectURL(url)

    emitModalOpen.value = false
  } catch (err) {
    console.error('Falha ao emitir documento:', err)
    swal.toastError('Falha ao emitir documento.')
  }
}

onMounted(fetchDocuments)
</script>

<style scoped>
.list-toolbar {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 12px;
  width: 100%;
}

.list-toolbar .search-input {
  width: 100%;
  max-width: 420px;
}

.list-toolbar__actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.grid-wrap {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}
</style>
