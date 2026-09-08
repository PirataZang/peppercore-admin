<template>
  <div ref="root" class="select-wrapper">
    <label v-if="label" class="select-label">{{ label }}</label>

    <div
      ref="controlRef"
      class="select-control"
      :class="{
        'is-open': isOpen,
        'is-disabled': disabled,
        'is-searchable': search,
      }"
      @click="handleDisplayClick"
    >
      <div class="control-body">
        <div v-if="multiple" class="multi-tags">
          <template v-if="selectedOptions.length">
            <span v-for="opt in selectedOptions" :key="opt.value" class="multi-tag">
              {{ opt.label }}
              <button type="button" class="multi-tag-remove" @click.stop="removeOption(opt)">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </span>
          </template>
          <span v-else class="control-placeholder">{{ placeholder || 'Selecionar...' }}</span>
        </div>

        <div v-else class="single-value-wrapper">
          <input
            v-if="search && isOpen"
            ref="searchInputRef"
            v-model="searchTerm"
            class="inline-search"
            :placeholder="placeholder || 'Selecionar...'"
            @click.stop
          />
          <template v-else>
            <span v-if="selectedOptions.length" class="single-value">{{ selectedOptions[0].label }}</span>
            <span v-else class="control-placeholder">{{ placeholder || 'Selecionar...' }}</span>
          </template>
        </div>
      </div>

      <div class="control-actions">
        <button
          v-if="canClear"
          type="button"
          class="action-btn clear-btn"
          @click.stop="clear"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>

        <button
          v-if="link"
          type="button"
          class="action-btn link-btn"
          @click.stop="navigateToLink"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
            <polyline points="15 3 21 3 21 9"></polyline>
            <line x1="10" y1="14" x2="21" y2="3"></line>
          </svg>
        </button>

        <span class="chevron-icon" :class="{ 'is-open': isOpen }">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </span>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="select-fade">
        <div
          v-if="isOpen"
          ref="dropdownRef"
          class="select-dropdown"
          :style="{ top: `${dropdownPos.top}px`, left: `${dropdownPos.left}px`, width: `${dropdownPos.width}px` }"
        >
          <ul class="options-list">
            <li
              v-for="opt in filteredOptions"
              :key="opt.value"
              class="option-item"
              :class="{
                'is-selected': isSelected(opt),
                'is-disabled': opt.disabled,
              }"
              @click.stop="select(opt)"
            >
              <span v-if="multiple" class="option-checkbox" :class="{ checked: isSelected(opt) }">
                <svg v-if="isSelected(opt)" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </span>
              <span v-else-if="isSelected(opt)" class="option-check-single">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </span>
              <span class="option-label">{{ opt.label }}</span>
            </li>
            <li v-if="!filteredOptions.length" class="option-item empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              Nenhuma opção encontrada
            </li>
          </ul>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, useAttrs } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  options: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: '',
  },
  modelValue: {
    type: [String, Number, Array, null],
    default: null,
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  search: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: '',
  },
  clearable: {
    type: Boolean,
    default: true,
  },
  link: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const attrs = useAttrs()
const router = useRouter()

const isOpen = ref(false)
const searchTerm = ref('')
const root = ref(null)
const controlRef = ref(null)
const dropdownRef = ref(null)
const searchInputRef = ref(null)
const dropdownPos = ref({ top: 0, left: 0, width: 0 })

function updateDropdownPos() {
  if (!controlRef.value) return
  const rect = controlRef.value.getBoundingClientRect()
  dropdownPos.value = {
    top: rect.bottom + 6,
    left: rect.left,
    width: rect.width,
  }
}

const selectedValues = ref(
  props.multiple
    ? Array.isArray(props.modelValue) ? [...props.modelValue] : []
    : props.modelValue != null ? [props.modelValue] : [],
)

watch(
  () => props.modelValue,
  (value) => {
    if (props.multiple) {
      selectedValues.value = Array.isArray(value) ? [...value] : []
    } else {
      selectedValues.value = value != null ? [value] : []
    }
  },
)

const filteredOptions = computed(() => {
  if (props.search && searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    return props.options.filter((option) => option.label.toLowerCase().includes(term))
  }
  return props.options
})

const selectedOptions = computed(() =>
  props.options.filter((option) => selectedValues.value.includes(option.value)),
)

const canClear = computed(() => props.clearable && selectedOptions.value.length > 0)

function isSelected(option) {
  return selectedValues.value.includes(option.value)
}

function select(option) {
  if (option.disabled || props.disabled) return

  if (props.multiple) {
    if (isSelected(option)) {
      selectedValues.value = selectedValues.value.filter((value) => value !== option.value)
    } else {
      selectedValues.value = [...selectedValues.value, option.value]
    }
    emit('update:modelValue', selectedValues.value)
    emit('change', selectedValues.value)
  } else {
    selectedValues.value = option.value != null ? [option.value] : []
    emit('update:modelValue', selectedValues.value[0] ?? null)
    emit('change', selectedValues.value[0] ?? null)
    close()
  }
}

function removeOption(option) {
  if (!props.multiple) return
  selectedValues.value = selectedValues.value.filter((value) => value !== option.value)
  emit('update:modelValue', selectedValues.value)
  emit('change', selectedValues.value)
}

function clear() {
  if (!canClear.value) return
  selectedValues.value = []
  emit('update:modelValue', props.multiple ? [] : null)
  emit('change', props.multiple ? [] : null)
  close()
}

function handleDisplayClick(event) {
  if (props.disabled) return
  if (isOpen.value && searchInputRef.value && event.target === searchInputRef.value) return

  isOpen.value = !isOpen.value
  if (isOpen.value) {
    updateDropdownPos()
    if (props.search) {
      searchTerm.value = ''
      nextTick(() => searchInputRef.value?.focus())
    }
  } else {
    searchTerm.value = ''
  }
}

function close() {
  isOpen.value = false
  searchTerm.value = ''
}

function navigateToLink() {
  if (props.link) {
    router.push({ name: props.link }).catch(() => {
      router.push('/' + props.link)
    })
  }
}

function handleClickOutside(event) {
  if (root.value?.contains(event.target)) return
  if (dropdownRef.value?.contains(event.target)) return
  close()
}

function handleScrollOrResize(event) {
  if (!isOpen.value) return
  // Rolar a própria lista de opções também dispara "scroll" (fase de captura,
  // então chega aqui antes do alvo) — só fecha se o scroll foi na página em
  // volta, não dentro do dropdown.
  if (event?.target instanceof Node && dropdownRef.value?.contains(event.target)) return
  close()
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  window.addEventListener('scroll', handleScrollOrResize, true)
  window.addEventListener('resize', handleScrollOrResize)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  window.removeEventListener('scroll', handleScrollOrResize, true)
  window.removeEventListener('resize', handleScrollOrResize)
})
</script>

<style scoped lang="scss">
.select-wrapper {
  position: relative;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-sizing: border-box;
}

.select-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  display: block;
}

.select-control {
  display: flex;
  align-items: center;
  min-height: 42px;
  padding: 5px 10px 5px 12px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background: #ffffff;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
  user-select: none;
  gap: 8px;

  &:hover:not(.is-disabled) {
    border-color: var(--color-border-strong);
  }

  &.is-open {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-soft);
    background: #ffffff;
  }

  &.is-disabled {
    background: var(--color-bg-muted);
    opacity: 0.85;
    cursor: not-allowed;
    pointer-events: none;
  }

  &.is-searchable {
    cursor: text;
  }
}

.control-body {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: center;
}

.single-value-wrapper {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: center;
}

.single-value {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.875rem;
  color: var(--color-text);
}

.control-placeholder {
  color: var(--color-text-faint);
  font-size: 0.875rem;
  flex: 1;
}

.multi-tags {
  flex: 1;
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  align-items: center;
}

.multi-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--color-primary-soft);
  color: var(--color-primary);
  border: 1px solid var(--color-primary-muted);
  border-radius: 6px;
  padding: 2px 8px;
  font-size: 12.5px;
  font-weight: 500;

  .multi-tag-remove {
    display: inline-flex;
    align-items: center;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--color-primary);
    padding: 0;
    border-radius: 3px;
    transition: color 0.15s ease;

    &:hover {
      color: var(--color-primary-hover);
    }
  }
}

.inline-search {
  border: none;
  outline: none;
  flex: 1;
  min-width: 60px;
  background: transparent;
  font-size: 0.875rem;
  color: var(--color-text);
  padding: 0;

  &::placeholder {
    color: var(--color-text-faint);
  }
}

.control-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
  padding: 0;
}

.clear-btn {
  color: var(--color-text-muted);

  &:hover {
    background: var(--color-bg-muted);
    color: var(--color-text);
  }
}

.link-btn {
  color: var(--color-secondary);

  &:hover {
    background: var(--color-secondary-soft, #eef2ff);
    color: var(--color-secondary);
  }
}

.chevron-icon {
  color: var(--color-text-muted);
  display: flex;
  align-items: center;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);

  &.is-open {
    transform: rotate(180deg);
  }
}

.select-dropdown {
  position: fixed;
  z-index: 9999;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
}

.options-list {
  list-style: none;
  margin: 0;
  padding: 5px 0;
  max-height: 240px;
  overflow-y: auto;

  &::-webkit-scrollbar {
    width: 4px;
  }
  &::-webkit-scrollbar-track {
    background: transparent;
  }
  &::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
  }
}

.option-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  transition: background 0.12s ease, color 0.12s ease;

  &:hover:not(.is-disabled) {
    background: var(--color-bg-muted);
    color: var(--color-text);
  }

  &.is-selected {
    background: var(--color-primary-soft);
    color: var(--color-primary);
    font-weight: 600;
  }

  &.is-disabled {
    color: var(--color-text-muted);
    cursor: not-allowed;

    &:hover {
      background: transparent;
    }
  }

  &.empty {
    color: var(--color-text-muted);
    font-style: italic;
    cursor: default;
    justify-content: center;
    gap: 8px;

    svg {
      opacity: 0.5;
    }
  }
}

.option-label {
  flex: 1;
}

.option-checkbox {
  width: 16px;
  height: 16px;
  border: 1.5px solid var(--color-border);
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.15s ease, border-color 0.15s ease;
  color: #fff;

  &.checked {
    background: var(--color-primary);
    border-color: var(--color-primary);
  }
}

.option-check-single {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--color-primary);
}

.select-fade-enter-active,
.select-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}

.select-fade-enter-from,
.select-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.98);
}
</style>
