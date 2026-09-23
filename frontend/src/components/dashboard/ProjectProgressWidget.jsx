import React from 'react'
import { Link } from 'react-router-dom'
import { FolderKanban, ArrowRight, Clock, CheckCircle } from 'lucide-react'

export default function ProjectProgressWidget() {
  const projects = [
    {
      id: 1,
      name: 'E-Commerce Platform Redesign',
      client: 'Apex Global Retail',
      progress: 85,
      status: 'In Progress',
      statusColor: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
      dueDate: 'Oct 15, 2026',
      tasksDone: 24,
      totalTasks: 28,
    },
    {
      id: 2,
      name: 'TimeSync Desktop v2.4 Release',
      client: 'Internal Enterprise',
      progress: 92,
      status: 'Testing',
      statusColor: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
      dueDate: 'Oct 02, 2026',
      tasksDone: 36,
      totalTasks: 39,
    },
    {
      id: 3,
      name: 'Mobile Banking API Integration',
      client: 'Zenith Capital Corp',
      progress: 45,
      status: 'In Progress',
      statusColor: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
      dueDate: 'Nov 12, 2026',
      tasksDone: 14,
      totalTasks: 30,
    },
    {
      id: 4,
      name: 'Cloud Infrastructure Migration',
      client: 'CloudWave Technologies',
      progress: 100,
      status: 'Completed',
      statusColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      dueDate: 'Sep 20, 2026',
      tasksDone: 18,
      totalTasks: 18,
    },
  ]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <FolderKanban className="w-4 h-4 text-purple-400" />
            <span>Active Projects Progress</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Key milestone completion rates and deadlines
          </p>
        </div>
        <Link
          to="/projects"
          className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors"
        >
          <span>View all</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </Link>
      </div>

      <div className="space-y-3.5">
        {projects.map((p) => (
          <div
            key={p.id}
            className="p-3.5 rounded-xl bg-slate-800/40 border border-slate-700/40 hover:border-slate-600/60 transition-all"
          >
            <div className="flex items-start justify-between gap-2 mb-2">
              <div>
                <h3 className="text-xs sm:text-sm font-semibold text-slate-100">
                  {p.name}
                </h3>
                <p className="text-[11px] text-slate-400">Client: {p.client}</p>
              </div>
              <span
                className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${p.statusColor}`}
              >
                {p.status}
              </span>
            </div>

            <div className="space-y-1.5">
              <div className="flex items-center justify-between text-[11px]">
                <span className="text-slate-400">
                  {p.tasksDone}/{p.totalTasks} Tasks completed
                </span>
                <span className="font-bold text-slate-200">{p.progress}%</span>
              </div>
              <div className="w-full h-1.5 rounded-full bg-slate-700/60 overflow-hidden">
                <div
                  style={{ width: `${p.progress}%` }}
                  className={`h-full rounded-full transition-all duration-500 ${
                    p.progress === 100
                      ? 'bg-emerald-500'
                      : p.progress > 70
                      ? 'bg-indigo-500'
                      : 'bg-amber-500'
                  }`}
                />
              </div>
            </div>

            <div className="mt-2 pt-2 border-t border-slate-800 flex items-center justify-between text-[10px] text-slate-400">
              <span className="flex items-center gap-1">
                <Clock className="w-3 h-3 text-slate-400" /> Due: {p.dueDate}
              </span>
              <Link
                to={`/projects`}
                className="text-indigo-400 hover:text-indigo-300 font-medium"
              >
                Project details →
              </Link>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
