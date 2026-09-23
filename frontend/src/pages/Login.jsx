import React, { useState } from 'react'
import { useNavigate, useLocation } from 'react-router-dom'
import {
  Layers,
  Lock,
  User,
  ArrowRight,
  AlertCircle,
  CheckCircle2,
  Sparkles,
} from 'lucide-react'
import { useAuthStore } from '../store/useAuthStore'

export default function Login() {
  const [username, setUsername] = useState('forid')
  const [password, setPassword] = useState('123456')
  const { login, isLoading, error } = useAuthStore()
  const navigate = useNavigate()
  const location = useLocation()

  const from = location.state?.from?.pathname || '/'

  const handleSubmit = async (e) => {
    e.preventDefault()
    const res = await login(username, password)
    if (res.success) {
      navigate(from, { replace: true })
    }
  }

  return (
    <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 relative overflow-hidden">
      {/* Ambient background glow effects */}
      <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/15 rounded-full blur-[140px] pointer-events-none" />
      <div className="absolute bottom-10 right-10 w-96 h-96 bg-purple-600/10 rounded-full blur-[120px] pointer-events-none" />

      <div className="w-full max-w-md relative z-10">
        {/* Brand Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 shadow-xl shadow-indigo-500/30 mb-4 ring-1 ring-white/20">
            <Layers className="w-7 h-7 text-white" />
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            TIC CRM
          </h1>
          <p className="mt-1.5 text-xs sm:text-sm text-slate-400">
            Enterprise Management System — React Edition
          </p>
        </div>

        {/* Login Card */}
        <div className="bg-slate-900/70 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60">
          <div className="mb-6">
            <h2 className="text-lg font-bold text-white">Sign In</h2>
            <p className="text-xs text-slate-400 mt-0.5">
              Enter your credentials to access your administrative dashboard.
            </p>
          </div>

          {error && (
            <div className="mb-5 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center gap-2.5">
              <AlertCircle className="w-4 h-4 shrink-0 text-rose-400" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-medium text-slate-300 mb-1.5">
                Username or Email
              </label>
              <div className="relative">
                <User className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  required
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="Enter username"
                  className="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl bg-slate-800/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all"
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-xs font-medium text-slate-300">
                  Password
                </label>
              </div>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter password"
                  className="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl bg-slate-800/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all"
                />
              </div>
            </div>

            <div className="pt-2">
              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              >
                {isLoading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  <>
                    <span>Authenticate Session</span>
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>
            </div>
          </form>

          {/* Quick Demo Credentials helper */}
          <div className="mt-6 pt-5 border-t border-slate-800/80 text-center">
            <p className="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
              <Sparkles className="w-3.5 h-3.5 text-indigo-400" />
              Verified test account credentials auto-filled:
            </p>
            <div className="mt-2 inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-800/60 border border-slate-700/50 text-slate-300 text-xs font-mono">
              <span>user: <strong className="text-indigo-300">forid</strong></span>
              <span className="text-slate-600">|</span>
              <span>pass: <strong className="text-indigo-300">123456</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
