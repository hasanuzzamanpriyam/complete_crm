import React from 'react'
import {
  Activity,
  CheckCircle,
  FileCheck,
  UserPlus,
  CreditCard,
  MessageSquare,
  Clock,
} from 'lucide-react'

export default function RecentActivityWidget() {
  const activities = [
    {
      id: 1,
      user: 'Forid Mondol',
      action: 'received payment of $4,800.00 via PipraPay',
      time: '12 mins ago',
      icon: CreditCard,
      iconColor: 'text-emerald-400 bg-emerald-500/10',
    },
    {
      id: 2,
      user: 'Sarah Jenkins',
      action: 'completed milestone on E-Commerce Platform Redesign',
      time: '45 mins ago',
      icon: CheckCircle,
      iconColor: 'text-indigo-400 bg-indigo-500/10',
    },
    {
      id: 3,
      user: 'David Chen',
      action: 'created new project agreement for Apex Global Retail',
      time: '2 hours ago',
      icon: FileCheck,
      iconColor: 'text-purple-400 bg-purple-500/10',
    },
    {
      id: 4,
      user: 'System Bot',
      action: 'clocked out 4 team members on scheduled shift end',
      time: '3 hours ago',
      icon: Clock,
      iconColor: 'text-amber-400 bg-amber-500/10',
    },
    {
      id: 5,
      user: 'Marcus Vance',
      action: 'resolved ticket #TK-482 (Database Connection Timeout)',
      time: '4 hours ago',
      icon: MessageSquare,
      iconColor: 'text-cyan-400 bg-cyan-500/10',
    },
  ]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <Activity className="w-4 h-4 text-cyan-400" />
            <span>Audit Trail & Activity Feed</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            System logs and recent transactions across departments
          </p>
        </div>
        <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700/60 text-slate-400">
          Real-time
        </span>
      </div>

      <div className="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
        {activities.map((item) => {
          const Icon = item.icon
          return (
            <div key={item.id} className="relative flex items-start gap-3">
              <div
                className={`absolute -left-6 top-0.5 p-1 rounded-full ring-4 ring-slate-900 ${item.iconColor}`}
              >
                <Icon className="w-3 h-3" />
              </div>
              <div className="text-xs flex-1">
                <p className="text-slate-300">
                  <strong className="text-white font-semibold">{item.user}</strong>{' '}
                  {item.action}
                </p>
                <span className="text-[10px] text-slate-400">{item.time}</span>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
