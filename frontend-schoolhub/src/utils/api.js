import axios from 'axios'

// Buat instance axios dengan base URL API Laravel
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
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
api.interceptors.request.use(
  (config) => {
    const token = sessionStorage.getItem('token')
    
    // Cek apakah ini request ke endpoint yang memerlukan auth
    const publicEndpoints = ['/login', '/register', '/public']
    const isPublicEndpoint = publicEndpoints.some((endpoint) =>
      config.url?.includes(endpoint),
    )

    // Log untuk debugging
    if (!isPublicEndpoint) {
      console.log('🔐 API Request:', config.method?.toUpperCase(), config.url)
      console.log('   Token exists:', token ? 'YES' : 'NO')
    }

    // Jika bukan public endpoint dan tidak ada token, redirect ke login
    if (!isPublicEndpoint && !token) {
      const publicPaths = ['/', '/login', '/pendaftaran', '/berita', '/pengumuman', '/kontak', '/profile', '/ppdb']
      const currentPath = window.location.pathname
      const isPublicPage = publicPaths.some(
        (path) => currentPath === path || currentPath.startsWith(path),
      )

      if (!isPublicPage) {
        console.error('❌ No token found, redirecting to login...')
        sessionStorage.clear()
        window.location.href = '/login'
        return Promise.reject(new Error('No authentication token found'))
      }
    }

    if (token) {
      // Otomatis tambahkan header Authorization ke setiap request
      config.headers.Authorization = `Bearer ${token}`
      if (!isPublicEndpoint) {
        console.log('   ✅ Token added to request')
      }
    }

    // Jika data adalah FormData, hapus Content-Type biar browser set sendiri
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type']
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

api.interceptors.response.use(
  (response) => {
    // Log successful responses (hanya untuk debugging)
    if (response.config.url && !response.config.url.includes('/public')) {
      console.log('✅ API Response:', response.config.method?.toUpperCase(), response.config.url, '- Status:', response.status)
    }
    return response
  },
  (error) => {
    const isUnauthorized = error.response?.status === 401
    const requestUrl = error.config?.url ?? ''
    const isLoginRequest = requestUrl.includes('/login')
    const isPublicRequest = requestUrl.includes('/public')

    // Log error responses
    console.error('❌ API Error:', error.config?.method?.toUpperCase(), requestUrl, '- Status:', error.response?.status)

    // Daftar path public yang tidak perlu redirect ke login
    const publicPaths = ['/', '/login', '/pendaftaran', '/profil', '/ppdb', '/berita', '/pengumuman', '/kontak']
    const currentPath = window.location.pathname
    const isPublicPage = publicPaths.some(
      (path) => currentPath === path || currentPath.startsWith(path),
    )

    // Hanya redirect ke login jika:
    // 1. Response 401 (Unauthorized)
    // 2. Bukan request login atau public
    // 3. Bukan di public page
    const hasToken = sessionStorage.getItem('token')

    if (isUnauthorized && !isLoginRequest && !isPublicRequest && !isPublicPage && hasToken) {
      // Log untuk debugging
      console.warn('🔓 Token expired or invalid, clearing session and redirecting to login...')
      
      sessionStorage.removeItem('token')
      sessionStorage.removeItem('user')

      // Gunakan setTimeout untuk menghindari race condition
      setTimeout(() => {
        if (window.location.pathname !== '/login') {
          console.log('➡️  Redirecting to /login')
          window.location.href = '/login'
        }
      }, 100)
    }

    return Promise.reject(error)
  },
)

export default api
