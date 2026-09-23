import React, { useState, useEffect } from 'react'
import {
  Menu as MenuIcon,
  Bell,
  Search,
  Clock,
  Plus,
  Shield,
  User,
  Sparkles,
} from 'lucide-react'
import { useAuthStore } from '../../store/useAuthStore'

export default function Header({ onMenuClick }) {
  const { user } = useAuthStore()
  const [time, setTime] = useState(new Date().toLocaleTimeString())
  const [isClockedIn, setIsClockedIn] = useState(false)

  useEffect(() => {
    const timer = setInterval(() => {
      setTime(new Date().toLocaleTimeString())
    }, 1000)
    return () => clearInterval(timer)
  }, [])

  return (
    <header className="sticky top-0 z-30 flex items-center justify-between h-16 px-4 md:px-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
      {/* Left: Mobile Menu Toggle & Breadcrumb / Title */}
      <div className="flex items-center gap-3">
        <button
          onClick={onMenuClick}
          className="p-2 -ml-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 lg:hidden"
        >
          <MenuIcon className="w-5 h-5" />
        </button>

        <div className="hidden sm:flex items-center gap-2 text-xs text-slate-400">
          <span className="font-medium text-slate-200">TIC CRM</span>
          <span>/</span>
          <span className="text-indigo-400 font-medium">Dashboard</span>
        </div>
      </div>

      {/* Right: Actions, Clock-in, Notifications, User */}
      <div className="flex items-center gap-2.5 sm:gap-4">
        {/* Live Clock & Clock In/Out */}
        <div className="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/80 border border-slate-700/50">
          <Clock className="w-3.5 h-3.5 text-indigo-400" />
          <span className="text-xs font-mono font-medium text-slate-300">
            {time}
          </span>
          <button
            onClick={() => setIsClockedIn(!isClockedIn)}
            className={`ml-1 text-[11px] font-semibold px-2 py-0.5 rounded transition-all ${
              isClockedIn
                ? 'bg-rose-500/20 text-rose-300 hover:bg-rose-500/30'
                : 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30'
            }`}
          >
            {isClockedIn ? 'Clock Out' : 'Clock In'}
          </button>
        </div>

        {/* Notifications */}
        <button className="relative p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
          <Bell className="w-4 h-4" />
          <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-indigo-500 animate-pulse" />
        </button>

        {/* User Pill */}
        <div className="flex items-center gap-2.5 pl-2 border-l border-slate-800">
          <div className="relative">
            <div className="flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-xs ring-2 ring-slate-800">
              {user?.full_name ? user.full_name.charAt(0).toUpperCase() : 'U'}
            </div>
            <span className="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-slate-900" />
          </div>
          <div className="hidden xl:block text-left">
            <p className="text-xs font-semibold text-slate-200 leading-tight">
              {user?.full_name || 'Admin'}
            </p>
            <p className="text-[10px] text-slate-400 leading-tight capitalize">
              {user?.role || 'Administrator'}
            </p>
          </div>
        </div>
      </div>
    </header>
  )
}
