import React, { useState } from 'react'
import { Code2, Play, CheckCircle2, Shield, Copy, ExternalLink, RefreshCw } from 'lucide-react'
import api from '../api/client'
import { useAuthStore } from '../store/useAuthStore'

export default function DeveloperPage() {
  const { token } = useAuthStore()
  const [selectedEndpoint, setSelectedEndpoint] = useState('/dashboard/live-users')
  const [responseJson, setResponseJson] = useState(null)
  const [loading, setLoading] = useState(false)
  const [copied, setCopied] = useState(false)

  const endpoints = [
    { method: 'GET', path: '/dashboard/live-users', desc: 'Real-time telemetry and active tracking sessions' },
    { method: 'GET', path: '/dashboard/summary', desc: 'Personal productivity & project distribution summary' },
    { method: 'GET', path: '/tasks', desc: 'List visible project tasks with permissions' },
    { method: 'GET', path: '/projects', desc: 'List all authorized project workspaces' },
    { method: 'GET', path: '/attendance', desc: 'Monthly attendance timecard records' },
    { method: 'GET', path: '/time-entries', desc: 'Raw desktop time entries and logs' },
    { method: 'GET', path: '/auth/health', desc: 'API gateway status and version health check' },
  ]

  const handleTestEndpoint = async () => {
    setLoading(true)
    setResponseJson(null)
    try {
      const res = await api.get(selectedEndpoint)
      setResponseJson(res.data)
    } catch (err) {
      setResponseJson(err.response?.data || { error: err.message })
    } finally {
      setLoading(false)
    }
  }

  const copyToken = () => {
    if (!token) return
    navigator.clipboard.writeText(token)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Code2 className="w-5 h-5 text-indigo-400" />
            <span>Developer Center & API Documentation</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Test and inspect REST API endpoints, Bearer authentication, and JSON responses
          </p>
        </div>
      </div>

      {/* Bearer Token Info Card */}
      <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-2">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Shield className="w-4 h-4 text-emerald-400" />
            <span className="text-xs font-bold text-white">Active Session Bearer Token</span>
          </div>
          <button
            onClick={copyToken}
            className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 cursor-pointer"
          >
            <Copy className="w-3.5 h-3.5" />
            <span>{copied ? 'Copied!' : 'Copy Token'}</span>
          </button>
        </div>
        <p className="text-xs font-mono text-slate-400 bg-slate-950 p-2.5 rounded-xl border border-slate-800 truncate">
          {token ? `Bearer ${token}` : 'No active session token'}
        </p>
      </div>

      {/* Interactive API Explorer */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Endpoints List */}
        <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-3">
          <h2 className="text-xs font-bold text-slate-400 uppercase tracking-wider">
            Available Endpoints
          </h2>
          <div className="space-y-2">
            {endpoints.map((ep) => (
              <button
                key={ep.path}
                onClick={() => setSelectedEndpoint(ep.path)}
                className={`w-full text-left p-3 rounded-xl border transition-all ${
                  selectedEndpoint === ep.path
                    ? 'bg-indigo-950/40 border-indigo-500/60 ring-1 ring-indigo-500/50'
                    : 'bg-slate-800/40 border-slate-800 hover:border-slate-700'
                }`}
              >
                <div className="flex items-center gap-2 mb-1">
                  <span className="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400">
                    {ep.method}
                  </span>
                  <span className="text-xs font-mono font-semibold text-white">
                    {ep.path}
                  </span>
                </div>
                <p className="text-[11px] text-slate-400 line-clamp-1">{ep.desc}</p>
              </button>
            ))}
          </div>
        </div>

        {/* Console / Response Viewer */}
        <div className="lg:col-span-2 p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur flex flex-col justify-between space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-slate-800">
            <div className="flex items-center gap-2">
              <span className="text-xs font-mono text-indigo-400 font-bold">GET</span>
              <span className="text-xs font-mono text-white font-bold">
                /api{selectedEndpoint}
              </span>
            </div>

            <button
              onClick={handleTestEndpoint}
              disabled={loading}
              className="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer"
            >
              {loading ? (
                <RefreshCw className="w-3.5 h-3.5 animate-spin" />
              ) : (
                <Play className="w-3.5 h-3.5" />
              )}
              <span>Send Request</span>
            </button>
          </div>

          <div className="flex-1 bg-slate-950 rounded-xl p-4 border border-slate-800/80 font-mono text-xs overflow-auto max-h-[420px]">
            {responseJson ? (
              <pre className="text-emerald-400 leading-relaxed">
                {JSON.stringify(responseJson, null, 2)}
              </pre>
            ) : (
              <div className="text-slate-500 text-center py-16">
                Click "Send Request" to test this endpoint against your local CodeIgniter backend.
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
