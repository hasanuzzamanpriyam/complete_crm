import React from 'react'
import { useLocation, Link } from 'react-router-dom'
import {
  Sparkles,
  ArrowLeft,
  Construction,
  Layers,
  ChevronRight,
} from 'lucide-react'

export default function PlaceholderPage({ title, category, description }) {
  const location = useLocation()
  const displayTitle = title || location.pathname.replace('/', '').replace(/-/g, ' ').toUpperCase()

  return (
    <div className="space-y-6">
      {/* Breadcrumb Header */}
      <div className="flex items-center justify-between">
        <div>
          <div className="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <Link to="/" className="hover:text-slate-200">Dashboard</Link>
            <ChevronRight className="w-3.5 h-3.5" />
            <span className="text-slate-300">{category || 'Module'}</span>
            <ChevronRight className="w-3.5 h-3.5" />
            <span className="text-indigo-400 font-medium capitalize">{displayTitle}</span>
          </div>
          <h1 className="text-xl md:text-2xl font-extrabold text-white tracking-tight capitalize">
            {displayTitle}
          </h1>
        </div>

        <Link
          to="/"
          className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-2 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Back to Dashboard</span>
        </Link>
      </div>

      {/* Modern Placeholder Container */}
      <div className="rounded-2xl bg-slate-900/60 border border-slate-800/80 p-8 sm:p-12 text-center backdrop-blur flex flex-col items-center justify-center min-h-[400px]">
        <div className="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-4">
          <Construction className="w-8 h-8 animate-bounce" />
        </div>

        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-semibold mb-3">
          <Sparkles className="w-3.5 h-3.5" />
          <span>Reorganized Navigation Active</span>
        </div>

        <h2 className="text-lg sm:text-xl font-bold text-white capitalize mb-2">
          {displayTitle} Module
        </h2>

        <p className="text-xs sm:text-sm text-slate-400 max-w-md mx-auto mb-6">
          {description || 'This module has been reorganized into the new modern navigation hierarchy. In the next phase, its detailed data tables and interactive views will be migrated to React components.'}
        </p>

        <div className="p-4 rounded-xl bg-slate-800/60 border border-slate-700/50 max-w-sm text-left text-xs text-slate-300 space-y-2">
          <div className="flex justify-between">
            <span className="text-slate-400">Endpoint / Route:</span>
            <code className="text-indigo-300 font-mono">{location.pathname}</code>
          </div>
          <div className="flex justify-between">
            <span className="text-slate-400">Backend API:</span>
            <span className="text-emerald-400 font-medium">Ready for integration</span>
          </div>
        </div>
      </div>
    </div>
  )
}
