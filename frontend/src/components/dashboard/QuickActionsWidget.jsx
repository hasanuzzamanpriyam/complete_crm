import React from 'react'
import { Link } from 'react-router-dom'
import {
  Plus,
  FileText,
  UserPlus,
  Clock,
  Video,
  Receipt,
  CheckSquare,
  HelpCircle,
  TrendingUp,
  FileSpreadsheet,
} from 'lucide-react'

export default function QuickActionsWidget() {
  const actions = [
    {
      label: 'New Task',
      desc: 'Assign to sprint or team',
      icon: CheckSquare,
      to: '/tasks',
      color: 'from-blue-500/20 to-indigo-500/10 text-blue-400 border-blue-500/30 hover:border-blue-500/60',
    },
    {
      label: 'Create Invoice',
      desc: 'Bill client or project',
      icon: Receipt,
      to: '/invoices',
      color: 'from-emerald-500/20 to-teal-500/10 text-emerald-400 border-emerald-500/30 hover:border-emerald-500/60',
    },
    {
      label: 'Add Lead',
      desc: 'Register prospective deal',
      icon: TrendingUp,
      to: '/leads',
      color: 'from-amber-500/20 to-orange-500/10 text-amber-400 border-amber-500/30 hover:border-amber-500/60',
    },
    {
      label: 'Add Client',
      desc: 'Corporate account setup',
      icon: UserPlus,
      to: '/clients',
      color: 'from-purple-500/20 to-pink-500/10 text-purple-400 border-purple-500/30 hover:border-purple-500/60',
    },
    {
      label: 'Start Timer',
      desc: 'Log task work session',
      icon: Clock,
      to: '/timesync',
      color: 'from-cyan-500/20 to-blue-500/10 text-cyan-400 border-cyan-500/30 hover:border-cyan-500/60',
    },
    {
      label: 'Book Meeting',
      desc: 'Zoom or team video sync',
      icon: Video,
      to: '/meetings',
      color: 'from-rose-500/20 to-red-500/10 text-rose-400 border-rose-500/30 hover:border-rose-500/60',
    },
    {
      label: 'Open Sheet',
      desc: 'Financial calculations',
      icon: FileSpreadsheet,
      to: '/spreadsheet',
      color: 'from-lime-500/20 to-emerald-500/10 text-lime-400 border-lime-500/30 hover:border-lime-500/60',
    },
    {
      label: 'Support Ticket',
      desc: 'Log client issue / bug',
      icon: HelpCircle,
      to: '/tickets',
      color: 'from-violet-500/20 to-purple-500/10 text-violet-400 border-violet-500/30 hover:border-violet-500/60',
    },
  ]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur shadow-xl">
      <div className="flex items-center justify-between mb-4">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <Plus className="w-4 h-4 text-indigo-400" />
            <span>Operations & Quick Actions</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Instant shortcuts to initiate enterprise workflows across CRM, HR, and Billing.
          </p>
        </div>
        <span className="text-[11px] font-medium text-slate-500">8 Shortcuts</span>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        {actions.map((act, idx) => {
          const Icon = act.icon
          return (
            <Link
              key={idx}
              to={act.to}
              className={`p-3.5 rounded-xl bg-gradient-to-b border transition-all duration-200 flex flex-col items-center text-center group hover:scale-[1.03] hover:shadow-lg ${act.color}`}
            >
              <div className="p-2.5 rounded-lg bg-slate-900/60 backdrop-blur mb-2 group-hover:scale-110 transition-transform">
                <Icon className="w-4 h-4" />
              </div>
              <span className="text-xs font-semibold text-slate-200 group-hover:text-white line-clamp-1">
                {act.label}
              </span>
              <span className="text-[10px] text-slate-400 mt-0.5 hidden sm:line-clamp-1">
                {act.desc}
              </span>
            </Link>
          )
        })}
      </div>
    </div>
  )
}
