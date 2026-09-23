import React, { useState, useEffect } from 'react'
import {
  Users,
  CheckCircle2,
  Clock,
  Briefcase,
  TrendingUp,
  ArrowUpRight,
  Shield,
  Activity,
  UserCheck,
  FolderKanban,
  Receipt,
  Plus,
  RefreshCw,
  Sparkles,
  CheckSquare,
} from 'lucide-react'
import api from '../api/client'
import { useAuthStore } from '../store/useAuthStore'

export default function Dashboard() {
  const { user } = useAuthStore()
  const [liveUsersData, setLiveUsersData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [refreshing, setRefreshing] = useState(false)

  const fetchLiveUsers = async () => {
    try {
      setRefreshing(true)
      const res = await api.get('/dashboard/live-users')
      if (res.data?.success) {
        setLiveUsersData(res.data.data)
      }
    } catch (err) {
      console.error('Failed to fetch live users:', err)
    } finally {
      setLoading(false)
      setRefreshing(false)
    }
  }

  useEffect(() => {
    fetchLiveUsers()
    // Poll every 30 seconds for live users
    const interval = setInterval(fetchLiveUsers, 30000)
    return () => clearInterval(interval)
  }, [])

  return (
    <div className="space-y-6">
      {/* Top Banner / Welcome */}
      <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-900/60 via-slate-900/80 to-purple-900/40 p-6 md:p-8 border border-slate-800/80 backdrop-blur shadow-xl">
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-medium mb-3">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Modern React UI Migration — Phase 1</span>
            </div>
            <h1 className="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
              Welcome back, {user?.full_name || user?.username || 'Administrator'}! 👋
            </h1>
            <p className="mt-1 text-sm text-slate-400 max-w-xl">
              Here is your executive CRM overview. Monitor live team attendance, active tasks, and company performance in real-time.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={fetchLiveUsers}
              disabled={refreshing}
              className="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 text-xs font-semibold text-slate-200 transition-colors flex items-center gap-2"
            >
              <RefreshCw className={`w-3.5 h-3.5 ${refreshing ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
            <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2 cursor-pointer">
              <Plus className="w-4 h-4" />
              <span>Quick Action</span>
            </button>
          </div>
        </div>
      </div>

      {/* KPI Stat Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Card 1: Active Team */}
        <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur hover:border-slate-700 transition-all">
          <div className="flex items-center justify-between">
            <span className="text-xs font-medium text-slate-400">Live Team Status</span>
            <div className="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
              <UserCheck className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-2xl font-bold text-white">
              {liveUsersData?.summary?.active ?? liveUsersData?.counts?.active ?? 0}
            </span>
            <span className="text-xs text-emerald-400 font-medium">Online</span>
          </div>
          <p className="mt-1 text-[11px] text-slate-400">
            {liveUsersData?.summary?.total ?? liveUsersData?.counts?.total ?? 0} registered staff members
          </p>
        </div>

        {/* Card 2: Active Timers */}
        <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur hover:border-slate-700 transition-all">
          <div className="flex items-center justify-between">
            <span className="text-xs font-medium text-slate-400">Running Timers</span>
            <div className="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
              <Clock className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-2xl font-bold text-white">
              {liveUsersData?.summary?.active ?? liveUsersData?.counts?.active ?? 0}
            </span>
            <span className="text-xs text-indigo-400 font-medium">Tracking</span>
          </div>
          <p className="mt-1 text-[11px] text-slate-400">
            {liveUsersData?.summary?.paused ?? liveUsersData?.counts?.paused ?? 0} paused timers
          </p>
        </div>

        {/* Card 3: Idle / Away */}
        <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur hover:border-slate-700 transition-all">
          <div className="flex items-center justify-between">
            <span className="text-xs font-medium text-slate-400">Idle / Offline</span>
            <div className="p-2 rounded-xl bg-amber-500/10 text-amber-400">
              <Activity className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-2xl font-bold text-white">
              {liveUsersData?.summary?.offline ?? liveUsersData?.counts?.offline ?? 0}
            </span>
            <span className="text-xs text-amber-400 font-medium">Away</span>
          </div>
          <p className="mt-1 text-[11px] text-slate-400">
            {liveUsersData?.summary?.idle ?? liveUsersData?.counts?.idle ?? 0} idle in desktop app
          </p>
        </div>

        {/* Card 4: System Health */}
        <div className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur hover:border-slate-700 transition-all">
          <div className="flex items-center justify-between">
            <span className="text-xs font-medium text-slate-400">API Connection</span>
            <div className="p-2 rounded-xl bg-purple-500/10 text-purple-400">
              <Shield className="w-4 h-4" />
            </div>
          </div>
          <div className="mt-3 flex items-baseline gap-2">
            <span className="text-2xl font-bold text-white">100%</span>
            <span className="text-xs text-purple-400 font-medium">Operational</span>
          </div>
          <p className="mt-1 text-[11px] text-slate-400">CodeIgniter 3 REST Backend</p>
        </div>
      </div>

      {/* Main Grid: Live Team Activity + Quick Shortcuts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Live Users Table Widget */}
        <div className="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                <Users className="w-4 h-4 text-indigo-400" />
                <span>Live Team Attendance & Activities</span>
              </h2>
              <p className="text-xs text-slate-400 mt-0.5">
                Real-time connection from TimeSync desktop tracker and web sessions.
              </p>
            </div>
            <span className="text-xs px-2.5 py-1 rounded-full font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              Live Feed
            </span>
          </div>

          {loading ? (
            <div className="py-12 flex flex-col items-center justify-center text-slate-400 gap-3">
              <div className="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
              <span className="text-xs">Fetching live team telemetry...</span>
            </div>
          ) : (liveUsersData?.active_users?.length > 0 || liveUsersData?.users?.length > 0) ? (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                    <th className="pb-3 pl-1">Employee</th>
                    <th className="pb-3">Status</th>
                    <th className="pb-3">Current Active Task</th>
                    <th className="pb-3 text-right pr-1">Last Active Ping</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60">
                  {(liveUsersData.active_users || liveUsersData.users).slice(0, 8).map((u) => {
                    const isOnline = u.status === 'active'
                    return (
                      <tr key={u.user_id} className="hover:bg-slate-800/40 transition-colors">
                        <td className="py-3 pl-1">
                          <div className="flex items-center gap-2.5">
                            <div className="w-7 h-7 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center text-white font-bold text-xs">
                              {u.fullname ? u.fullname.charAt(0).toUpperCase() : 'U'}
                            </div>
                            <div>
                              <p className="font-medium text-slate-200">{u.fullname || 'Employee'}</p>
                              <p className="text-[10px] text-slate-400">ID: #{u.user_id}</p>
                            </div>
                          </div>
                        </td>
                        <td className="py-3">
                          <span
                            className={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold ${
                              isOnline
                                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                : u.status === 'paused'
                                ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                : 'bg-slate-800 text-slate-400 border border-slate-700/50'
                            }`}
                          >
                            <span
                              className={`w-1.5 h-1.5 rounded-full ${
                                isOnline
                                  ? 'bg-emerald-400 animate-pulse'
                                  : u.status === 'paused'
                                  ? 'bg-amber-400'
                                  : 'bg-slate-500'
                              }`}
                            />
                            <span className="capitalize">{u.status}</span>
                          </span>
                        </td>
                        <td className="py-3 text-slate-300">
                          {u.current_task ? (
                            <span className="font-medium text-indigo-300">{u.current_task}</span>
                          ) : (
                            <span className="text-slate-400">—</span>
                          )}
                        </td>
                        <td className="py-3 text-right pr-1 text-slate-400 font-mono text-[11px]">
                          {u.last_active_ping || 'Recently'}
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="py-12 text-center text-slate-400 text-xs flex flex-col items-center justify-center gap-2">
              <div className="p-3 rounded-2xl bg-slate-800/60 border border-slate-700/40 text-slate-400 mb-1">
                <Users className="w-5 h-5" />
              </div>
              <p className="font-medium text-slate-300">All team members are currently on standby.</p>
              <p className="text-[11px] text-slate-500 max-w-sm">
                Active tracking sessions and live attendance will show up here automatically when staff clock in or use the TimeSync tracker.
              </p>
            </div>
          )}
        </div>

        {/* Quick Action Navigation Shortcuts */}
        <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
          <div>
            <h2 className="text-sm sm:text-base font-bold text-white">Quick Module Shortcuts</h2>
            <p className="text-xs text-slate-400 mt-0.5">
              Access the newly reorganized functional sections.
            </p>
          </div>

          <div className="grid grid-cols-1 gap-2.5">
            <div className="p-3 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 transition-all cursor-pointer group flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-indigo-500/10 text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                  <CheckSquare className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-slate-200">Tasks & Kanban</h3>
                  <p className="text-[10px] text-slate-400">Sprint planning & execution</p>
                </div>
              </div>
              <ArrowUpRight className="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" />
            </div>

            <div className="p-3 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 transition-all cursor-pointer group flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                  <Receipt className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-slate-200">Sales & Invoicing</h3>
                  <p className="text-[10px] text-slate-400">Invoices, estimates & payments</p>
                </div>
              </div>
              <ArrowUpRight className="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" />
            </div>

            <div className="p-3 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 transition-all cursor-pointer group flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-purple-500/10 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                  <FolderKanban className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-slate-200">Projects Workspace</h3>
                  <p className="text-[10px] text-slate-400">Milestones & deliverables</p>
                </div>
              </div>
              <ArrowUpRight className="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" />
            </div>

            <div className="p-3 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 transition-all cursor-pointer group flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-rose-500/10 text-rose-400 group-hover:bg-rose-500 group-hover:text-white transition-colors">
                  <Users className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-xs font-semibold text-slate-200">Clients & Leads</h3>
                  <p className="text-[10px] text-slate-400">Customer relationships</p>
                </div>
              </div>
              <ArrowUpRight className="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" />
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
