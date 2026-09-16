import api from './api'

/**
 * Keuangan API Service
 * Handle all API calls related to financial management (SPP & Payroll)
 */

export const keuanganService = {
  // ============================================
  // DASHBOARD
  // ============================================
  
  /**
   * Get dashboard statistics
   */
  async getDashboard() {
    const response = await api.get('/keuangan/dashboard')
    return response.data
  },

  // ============================================
  // TAGIHAN SPP
  // ============================================
  
  /**
   * Get all tagihan SPP with filters
   * @param {Object} params - Query parameters
   * @param {string} params.status - Filter by status (UNPAID, PENDING, LUNAS, etc.)
   * @param {string} params.periode - Filter by periode (YYYY-MM-DD)
   * @param {number} params.kelas_id - Filter by kelas
   * @param {string} params.search - Search by murid name or NIS
   * @param {number} params.per_page - Items per page
   */
  async getTagihanSpp(params = {}) {
    const response = await api.get('/keuangan/tagihan', { params })
    return response.data
  },

  /**
   * Create new tagihan SPP
   * @param {Object} data - Tagihan data
   */
  async createTagihan(data) {
    const response = await api.post('/keuangan/tagihan', data)
    return response.data
  },

  /**
   * Update tagihan SPP
   * @param {number} id - Tagihan ID
   * @param {Object} data - Updated data
   */
  async updateTagihan(id, data) {
    const response = await api.put(`/keuangan/tagihan/${id}`, data)
    return response.data
  },

  /**
   * Delete tagihan SPP
   * @param {number} id - Tagihan ID
   */
  async deleteTagihan(id) {
    const response = await api.delete(`/keuangan/tagihan/${id}`)
    return response.data
  },

  // ============================================
  // SLIP GAJI
  // ============================================
  
  /**
   * Get all slip gaji with filters
   * @param {Object} params - Query parameters
   * @param {string} params.status - Filter by status (DRAFT, APPROVED, PAID)
   * @param {string} params.periode - Filter by periode (YYYY-MM-DD)
   * @param {number} params.per_page - Items per page
   */
  async getSlipGaji(params = {}) {
    const response = await api.get('/keuangan/slip-gaji', { params })
    return response.data
  },

  /**
   * Create new slip gaji
   * @param {Object} data - Slip gaji data
   */
  async createSlipGaji(data) {
    const response = await api.post('/keuangan/slip-gaji', data)
    return response.data
  },

  /**
   * Update slip gaji
   * @param {number} id - Slip gaji ID
   * @param {Object} data - Updated data
   */
  async updateSlipGaji(id, data) {
    const response = await api.put(`/keuangan/slip-gaji/${id}`, data)
    return response.data
  },

  /**
   * Approve slip gaji (Admin only)
   * @param {number} id - Slip gaji ID
   */
  async approveSlipGaji(id) {
    const response = await api.post(`/keuangan/slip-gaji/${id}/approve`)
    return response.data
  },

  /**
   * Mark slip gaji as PAID
   * @param {number} id - Slip gaji ID
   */
  async markSlipGajiAsPaid(id) {
    const response = await api.post(`/keuangan/slip-gaji/${id}/mark-paid`)
    return response.data
  },

  /**
   * Delete slip gaji
   * @param {number} id - Slip gaji ID
   */
  async deleteSlipGaji(id) {
    const response = await api.delete(`/keuangan/slip-gaji/${id}`)
    return response.data
  },

  // ============================================
  // HELPER FUNCTIONS
  // ============================================
  
  /**
   * Format currency (IDR)
   * @param {number} amount
   */
  formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount)
  },

  /**
   * Format date (Indonesian)
   * @param {string} date
   */
  formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    })
  },

  /**
   * Format datetime (Indonesian)
   * @param {string} datetime
   */
  formatDateTime(datetime) {
    if (!datetime) return '-'
    return new Date(datetime).toLocaleString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  },

  /**
   * Get status badge color
   * @param {string} status
   */
  getStatusColor(status) {
    const colors = {
      UNPAID: 'warning',
      PENDING: 'info',
      LUNAS: 'success',
      EXPIRED: 'error',
      CANCELLED: 'secondary',
      DRAFT: 'secondary',
      APPROVED: 'info',
      PAID: 'success',
      SUCCESS: 'success',
      FAILED: 'error',
    }
    return colors[status] || 'default'
  },

  /**
   * Get status label (Indonesian)
   * @param {string} status
   */
  getStatusLabel(status) {
    const labels = {
      UNPAID: 'Belum Bayar',
      PENDING: 'Pending',
      LUNAS: 'Lunas',
      EXPIRED: 'Kadaluarsa',
      CANCELLED: 'Dibatalkan',
      DRAFT: 'Draft',
      APPROVED: 'Disetujui',
      PAID: 'Dibayar',
      SUCCESS: 'Berhasil',
      FAILED: 'Gagal',
    }
    return labels[status] || status
  },
}

export default keuanganService
