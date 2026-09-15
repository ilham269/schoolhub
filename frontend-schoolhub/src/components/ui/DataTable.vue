<template>
  <div class="data-table-container">
    <!-- Search & Actions Bar -->
    <div class="table-toolbar">
      <div class="search-box">
        <i class="fas fa-search" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari data..."
          class="search-input"
        />
      </div>

      <div class="toolbar-actions">
        <slot name="toolbar" />
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th v-if="selectable" class="col-checkbox">
              <input type="checkbox" @change="toggleSelectAll" :checked="allSelected" />
            </th>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="['col-' + column.key, column.sortable && 'sortable']"
              @click="column.sortable && sort(column.key)"
            >
              {{ column.label }}
              <i
                v-if="column.sortable"
                :class="getSortIcon(column.key)"
                class="sort-icon"
              />
            </th>
            <th v-if="hasActions" class="col-actions">Aksi</th>
          </tr>
        </thead>

        <tbody v-if="!loading && paginatedData.length">
          <tr v-for="(item, index) in paginatedData" :key="item.id || index">
            <td v-if="selectable" class="col-checkbox">
              <input
                type="checkbox"
                :value="item.id"
                v-model="selectedItems"
                @change="$emit('selection-change', selectedItems)"
              />
            </td>

            <td v-for="column in columns" :key="column.key" :class="'col-' + column.key">
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getCellValue(item, column.key)"
              >
                {{ formatCell(item, column) }}
              </slot>
            </td>

            <td v-if="hasActions" class="col-actions">
              <div class="action-buttons">
                <button
                  v-if="actions.includes('view')"
                  @click="$emit('view', item)"
                  class="btn-action btn-view"
                  title="Lihat Detail"
                >
                  <i class="fas fa-eye" />
                </button>
                <button
                  v-if="actions.includes('edit')"
                  @click="$emit('edit', item)"
                  class="btn-action btn-edit"
                  title="Edit"
                >
                  <i class="fas fa-edit" />
                </button>
                <button
                  v-if="actions.includes('delete')"
                  @click="$emit('delete', item)"
                  class="btn-action btn-delete"
                  title="Hapus"
                >
                  <i class="fas fa-trash" />
                </button>
                <slot name="custom-actions" :item="item" />
              </div>
            </td>
          </tr>
        </tbody>

        <tbody v-else-if="loading">
          <tr>
            <td :colspan="totalColumns" class="empty-state">
              <div class="loading-state">
                <i class="fas fa-spinner fa-spin" />
                <p>Memuat data...</p>
              </div>
            </td>
          </tr>
        </tbody>

        <tbody v-else>
          <tr>
            <td :colspan="totalColumns" class="empty-state">
              <div class="empty-message">
                <i class="fas fa-inbox" />
                <p>{{ emptyText }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && paginatedData.length" class="table-footer">
      <div class="pagination-info">
        Menampilkan {{ startIndex + 1 }} - {{ endIndex }} dari {{ filteredData.length }} data
      </div>

      <div class="pagination">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          <i class="fas fa-chevron-left" />
        </button>

        <button
          v-for="page in visiblePages"
          :key="page"
          @click="goToPage(page)"
          :class="['pagination-btn', page === currentPage && 'active']"
        >
          {{ page }}
        </button>

        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          <i class="fas fa-chevron-right" />
        </button>
      </div>

      <div class="per-page-selector">
        <label>Per halaman:</label>
        <select v-model.number="itemsPerPage" class="per-page-select">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Array,
    required: true,
  },
  actions: {
    type: Array,
    default: () => ['view', 'edit', 'delete'],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  selectable: {
    type: Boolean,
    default: false,
  },
  emptyText: {
    type: String,
    default: 'Tidak ada data',
  },
  perPage: {
    type: Number,
    default: 10,
  },
})

const emit = defineEmits(['view', 'edit', 'delete', 'selection-change'])

// State
const searchQuery = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')
const selectedItems = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(props.perPage)

// Computed
const hasActions = computed(() => props.actions.length > 0)

const totalColumns = computed(() => {
  let count = props.columns.length
  if (props.selectable) count++
  if (hasActions.value) count++
  return count
})

const filteredData = computed(() => {
  let result = [...props.data]

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter((item) => {
      return props.columns.some((column) => {
        const value = getCellValue(item, column.key)
        return String(value).toLowerCase().includes(query)
      })
    })
  }

  // Sort
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = getCellValue(a, sortKey.value)
      const bVal = getCellValue(b, sortKey.value)

      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return result
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / itemsPerPage.value))

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value)

const endIndex = computed(() =>
  Math.min(startIndex.value + itemsPerPage.value, filteredData.value.length),
)

const paginatedData = computed(() => {
  return filteredData.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)

  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const allSelected = computed(() => {
  return (
    paginatedData.value.length > 0 &&
    paginatedData.value.every((item) => selectedItems.value.includes(item.id))
  )
})

// Methods
const getCellValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item)
}

const formatCell = (item, column) => {
  const value = getCellValue(item, column.key)

  if (column.format) {
    return column.format(value, item)
  }

  return value ?? '-'
}

const sort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const getSortIcon = (key) => {
  if (sortKey.value !== key) return 'fas fa-sort'
  return sortOrder.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = paginatedData.value.map((item) => item.id)
  }
  emit('selection-change', selectedItems.value)
}

// Watch for data changes and reset page
watch(
  () => props.data,
  () => {
    currentPage.value = 1
  },
)

watch(searchQuery, () => {
  currentPage.value = 1
})
</script>

<style scoped>
.data-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 38px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.toolbar-actions {
  display: flex;
  gap: 12px;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f9fafb;
}

.data-table th {
  padding: 12px 16px;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: all 0.2s;
}

.data-table th.sortable:hover {
  background: #f3f4f6;
  color: #374151;
}

.sort-icon {
  margin-left: 6px;
  font-size: 0.7rem;
  opacity: 0.5;
}

.data-table td {
  padding: 16px;
  font-size: 0.875rem;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
}

.data-table tbody tr {
  transition: background 0.2s;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.col-checkbox {
  width: 40px;
  text-align: center;
}

.col-actions {
  width: 120px;
  text-align: right;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-action {
  padding: 6px 10px;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  background: #f3f4f6;
  color: #6b7280;
}

.btn-action:hover {
  transform: translateY(-1px);
}

.btn-view:hover {
  background: #dbeafe;
  color: #3b82f6;
}

.btn-edit:hover {
  background: #d1fae5;
  color: #10b981;
}

.btn-delete:hover {
  background: #fee2e2;
  color: #ef4444;
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
}

.loading-state,
.empty-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #9ca3af;
}

.loading-state i {
  font-size: 2rem;
}

.empty-message i {
  font-size: 3rem;
  color: #d1d5db;
}

.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  gap: 16px;
  flex-wrap: wrap;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.pagination {
  display: flex;
  gap: 4px;
}

.pagination-btn {
  min-width: 36px;
  height: 36px;
  padding: 0 8px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  color: #374151;
}

.pagination-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.pagination-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.per-page-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  color: #6b7280;
}

.per-page-select {
  padding: 6px 28px 6px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  background: white
    url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e")
    no-repeat right 6px center/16px 16px;
  appearance: none;
}

@media (max-width: 768px) {
  .table-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    max-width: none;
  }

  .table-footer {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }

  .pagination {
    justify-content: center;
  }

  .per-page-selector {
    justify-content: center;
  }
}
</style>
