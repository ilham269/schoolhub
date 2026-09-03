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
    
    // Daftar path public yang tidak perlu redirect ke login
    const publicPaths = ['/', '/login', '/pendaftaran', '/profil', '/ppdb']
    const currentPath = window.location.pathname
    const isPublicPage = publicPaths.some(path => currentPath === path || currentPath.startsWith(path))

    // Hanya redirect ke login jika:
    // 1. Response 401 (Unauthorized)
    // 2. Bukan request login
    // 3. User punya token (berarti token expired/invalid)
    // 4. Bukan di public page
    const hasToken = localStorage.getItem('token') || sessionStorage.getItem('token')
    
    if (isUnauthorized && !isLoginRequest && hasToken && !isPublicPage) {
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
