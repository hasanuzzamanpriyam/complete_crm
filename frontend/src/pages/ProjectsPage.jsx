import React, { useState } from 'react'
import {
  FolderKanban,
  Plus,
  Search,
  Filter,
  Clock,
  DollarSign,
  Users,
  CheckCircle,
  MoreVertical,
  Calendar,
} from 'lucide-react'

export default function ProjectsPage() {
  const [filterStatus, setFilterStatus] = useState('All')
  const [searchQuery, setSearchQuery] = useState('')
  const [showModal, setShowModal] = useState(false)

  const [projects, setProjects] = useState([
    {
      id: 1,
      name: 'E-Commerce Platform Redesign',
      client: 'Apex Global Retail',
      budget: '$25,000.00',
      progress: 85,
      status: 'In Progress',
      startDate: '2026-08-01',
      dueDate: '2026-10-15',
      members: ['Forid M.', 'Sarah J.', 'Marcus V.'],
    },
    {
      id: 2,
      name: 'TimeSync Desktop v2.4 Release',
      client: 'Internal Enterprise',
      budget: '$15,000.00',
      progress: 92,
      status: 'In Progress',
      startDate: '2026-08-15',
      dueDate: '2026-10-02',
      members: ['Sarah J.', 'David C.'],
    },
    {
      id: 3,
      name: 'Mobile Banking API Integration',
      client: 'Zenith Capital Corp',
      budget: '$45,000.00',
      progress: 45,
      status: 'In Progress',
      startDate: '2026-09-01',
      dueDate: '2026-11-12',
      members: ['Forid M.', 'David C.'],
    },
    {
      id: 4,
      name: 'Cloud Infrastructure Migration',
      client: 'CloudWave Technologies',
      budget: '$18,500.00',
      progress: 100,
      status: 'Completed',
      startDate: '2026-07-10',
      dueDate: '2026-09-20',
      members: ['Forid M.', 'Elena R.'],
    },
    {
      id: 5,
      name: 'Omni Media Ad Network Upgrade',
      client: 'Omni Media Global',
      budget: '$12,000.00',
      progress: 15,
      status: 'On Hold',
      startDate: '2026-09-10',
      dueDate: '2026-12-01',
      members: ['Sarah J.'],
    },
  ])

  const [name, setName] = useState('')
  const [client, setClient] = useState('')
  const [budget, setBudget] = useState('')
  const [dueDate, setDueDate] = useState('')

  const handleCreate = (e) => {
    e.preventDefault()
    if (!name || !client) return
    const newP = {
      id: Date.now(),
      name,
      client,
      budget: budget.startsWith('$') ? budget : `$${budget || '10,000.00'}`,
      progress: 0,
      status: 'In Progress',
      startDate: new Date().toISOString().split('T')[0],
      dueDate: dueDate || '2026-11-30',
      members: ['Forid M.'],
    }
    setProjects([newP, ...projects])
    setShowModal(false)
    setName('')
    setClient('')
    setBudget('')
  }

  const filtered = projects.filter((p) => {
    const matchesStatus = filterStatus === 'All' || p.status === filterStatus
    const matchesQuery =
      p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.client.toLowerCase().includes(searchQuery.toLowerCase())
    return matchesStatus && matchesQuery
  })

  return (
    <div className="space-y-6">
      {/* Title & Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <FolderKanban className="w-5 h-5 text-indigo-400" />
            <span>Projects Workspace</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Monitor client project lifecycles, team allocation, and milestones
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>New Project</span>
        </button>
      </div>

      {/* Filter and Search Bar */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-slate-900/70 border border-slate-800">
        <div className="relative flex-1 max-w-sm">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search projects or clients..."
            className="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-800 border border-slate-700/60 text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          />
        </div>

        <div className="flex items-center gap-1.5 overflow-x-auto">
          {['All', 'In Progress', 'Completed', 'On Hold'].map((st) => (
            <button
              key={st}
              onClick={() => setFilterStatus(st)}
              className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap ${
                filterStatus === st
                  ? 'bg-indigo-600 text-white'
                  : 'bg-slate-800 text-slate-400 hover:text-white'
              }`}
            >
              {st}
            </button>
          ))}
        </div>
      </div>

      {/* Projects Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filtered.map((p) => (
          <div
            key={p.id}
            className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4 hover:border-slate-700 transition-all flex flex-col justify-between"
          >
            <div className="space-y-2">
              <div className="flex items-start justify-between gap-2">
                <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300">
                  {p.client}
                </span>
                <span
                  className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                    p.status === 'Completed'
                      ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                      : p.status === 'In Progress'
                      ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
                      : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                  }`}
                >
                  {p.status}
                </span>
              </div>

              <h2 className="text-sm font-bold text-white leading-snug">
                {p.name}
              </h2>
            </div>

            {/* Progress Bar */}
            <div className="space-y-1.5">
              <div className="flex items-center justify-between text-xs">
                <span className="text-slate-400">Progress</span>
                <span className="font-bold text-white">{p.progress}%</span>
              </div>
              <div className="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  style={{ width: `${p.progress}%` }}
                  className={`h-full rounded-full transition-all duration-500 ${
                    p.progress === 100
                      ? 'bg-emerald-500'
                      : p.progress > 50
                      ? 'bg-indigo-500'
                      : 'bg-amber-500'
                  }`}
                />
              </div>
            </div>

            {/* Meta details */}
            <div className="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
              <div className="flex items-center gap-1 font-mono font-bold text-slate-200">
                <DollarSign className="w-3.5 h-3.5 text-emerald-400" />
                <span>{p.budget}</span>
              </div>
              <div className="flex items-center gap-1">
                <Clock className="w-3.5 h-3.5 text-slate-400" />
                <span>{p.dueDate}</span>
              </div>
            </div>

            {/* Team Avatars & Action */}
            <div className="flex items-center justify-between pt-1">
              <div className="flex items-center -space-x-1.5">
                {p.members.map((m, idx) => (
                  <div
                    key={idx}
                    className="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-[9px] flex items-center justify-center ring-2 ring-slate-900"
                    title={m}
                  >
                    {m.charAt(0)}
                  </div>
                ))}
              </div>
              <button className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                View Workspace →
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Create Project Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Initiate Project</h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleCreate} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Project Title
                </label>
                <input
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. ERP Cloud Redesign"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Client Organization
                </label>
                <input
                  type="text"
                  required
                  value={client}
                  onChange={(e) => setClient(e.target.value)}
                  placeholder="e.g. Apex Global Retail"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Project Budget
                  </label>
                  <input
                    type="text"
                    value={budget}
                    onChange={(e) => setBudget(e.target.value)}
                    placeholder="$25,000.00"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Due Date
                  </label>
                  <input
                    type="date"
                    value={dueDate}
                    onChange={(e) => setDueDate(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white rounded-xl shadow-sm cursor-pointer"
                >
                  Create Project
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
