import axios from 'axios'

// Buat instance axios dengan base URL API Laravel
// Menggunakan proxy Vite untuk menghindari CORS
const api = axios.create({
  baseURL: '/api',
  timeout: 15000,
  // Tidak set headers default, biar bisa dinamis per request
})

const getCache = new Map()

api.getCached = async (url, config = {}, cacheDuration = 30000) => {
  const key = `${url}?${JSON.stringify(config.params ?? {})}`
  const cached = getCache.get(key)

  if (cached && Date.now() - cached.createdAt < cacheDuration) return cached.request

  const request = api.get(url, config).catch((error) => {
    getCache.delete(key)
    throw error
  })

  getCache.set(key, { createdAt: Date.now(), request })
  return request
}

// Interceptor: otomatis tambahkan token di setiap request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token') || sessionStorage.getItem('token')
  if (token) {
    // Otomatis tambahkan header Authorization ke setiap request
    config.headers.Authorization = `Bearer ${token}`
  }
  
  // Jika data adalah FormData, hapus Content-Type biar browser set sendiri
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
  }
  
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const isUnauthorized = error.response?.status === 401
    const requestUrl = error.config?.url ?? ''
    const isLoginRequest = requestUrl.includes('/login')

    if (isUnauthorized && !isLoginRequest) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      sessionStorage.removeItem('token')
      sessionStorage.removeItem('user')

      if (window.location.pathname !== '/login') {
        window.location.assign('/login')
      }
    }

    return Promise.reject(error)
  },
)


export default api
