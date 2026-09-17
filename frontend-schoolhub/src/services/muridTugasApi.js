import api from '@/utils/api'

/** API tugas yang tersedia untuk murid. */
export const muridTugasApi = {
  list: async () => {
    const response = await api.get('/murid/tugas')
    return response.data?.data ?? response.data ?? []
  },
  submit: async (id, formData) => {
    const response = await api.post(`/murid/tugas/${id}/kumpulkan`, formData)
    return response.data?.data ?? response.data ?? {}
  },
}

export default muridTugasApi
