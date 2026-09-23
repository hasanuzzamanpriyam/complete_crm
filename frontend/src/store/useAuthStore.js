import { create } from 'zustand'
import api from '../api/client'

const storedToken = localStorage.getItem('tic_crm_token')
const storedUser = localStorage.getItem('tic_crm_user')

export const useAuthStore = create((set, get) => ({
  token: storedToken || null,
  user: storedUser ? JSON.parse(storedUser) : null,
  isAuthenticated: !!storedToken,
  isLoading: false,
  error: null,

  login: async (username, password) => {
    set({ isLoading: true, error: null })
    try {
      const response = await api.post('/auth/login', { username, password })
      const data = response.data

      if (data.success && data.access_token) {
        localStorage.setItem('tic_crm_token', data.access_token)
        localStorage.setItem('tic_crm_refresh_token', data.refresh_token || '')
        localStorage.setItem('tic_crm_user', JSON.stringify(data.user))

        set({
          token: data.access_token,
          user: data.user,
          isAuthenticated: true,
          isLoading: false,
          error: null,
        })
        return { success: true }
      } else {
        const errorMsg = data.message || 'Login failed'
        set({ error: errorMsg, isLoading: false })
        return { success: false, message: errorMsg }
      }
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Network error occurred'
      set({ error: msg, isLoading: false })
      return { success: false, message: msg }
    }
  },

  logout: async () => {
    try {
      if (get().token) {
        await api.post('/auth/logout').catch(() => {})
      }
    } finally {
      localStorage.removeItem('tic_crm_token')
      localStorage.removeItem('tic_crm_refresh_token')
      localStorage.removeItem('tic_crm_user')
      set({
        token: null,
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null,
      })
    }
  },

  updateUser: (user) => {
    localStorage.setItem('tic_crm_user', JSON.stringify(user))
    set({ user })
  },
}))
