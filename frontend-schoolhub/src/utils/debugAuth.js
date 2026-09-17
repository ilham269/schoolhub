// Debug utility untuk cek status auth
import api from './api'

export async function debugAuth() {
  console.group('🔍 DEBUG AUTH')
  
  // 1. Cek token di sessionStorage
  const token = sessionStorage.getItem('token')
  console.log('1. Token exists:', token ? 'YES' : 'NO')
  if (token) {
    console.log('   Token preview:', token.substring(0, 20) + '...')
  }
  
  // 2. Cek user data
  const userStr = sessionStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      console.log('2. User data:', {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
      })
    } catch (e) {
      console.error('   Failed to parse user:', e)
    }
  } else {
    console.log('2. User data: NOT FOUND')
  }
  
  // 3. Test API call ke /auth/me
  try {
    console.log('3. Testing /auth/me endpoint...')
    const response = await api.get('/auth/me')
    console.log('   ✅ Auth working! User:', response.data)
  } catch (error) {
    console.error('   ❌ Auth failed:', error.response?.status, error.response?.data?.message)
  }
  
  // 4. Test API call ke /guru/tugas (GET - read only)
  try {
    console.log('4. Testing /guru/tugas endpoint...')
    const response = await api.get('/guru/tugas')
    console.log('   ✅ Tugas endpoint working! Count:', response.data?.data?.length || 0)
  } catch (error) {
    console.error('   ❌ Tugas endpoint failed:', error.response?.status, error.response?.data?.message)
    
    // Check if it's a role issue
    if (error.response?.status === 403) {
      console.error('   >> This is a PERMISSION issue - user might not have guru role')
    } else if (error.response?.status === 401) {
      console.error('   >> This is an AUTH issue - token invalid or missing')
    }
  }
  
  console.groupEnd()
}

// Auto-run di development
if (import.meta.env.DEV) {
  window.debugAuth = debugAuth
  console.log('💡 Run window.debugAuth() in console to debug authentication')
}
