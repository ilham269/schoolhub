import api from '@/utils/api'

/** API tugas yang tersedia untuk murid. */
export const muridTugasApi = {
  list: () => api.get('/murid/tugas').then((response) => response.data),
  submit: (id, formData) => api.post(`/murid/tugas/${id}/kumpulkan`, formData).then((response) => response.data),
}

export default muridTugasApi
