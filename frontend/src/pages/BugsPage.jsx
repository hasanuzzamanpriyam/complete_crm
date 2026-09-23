import React, { useState } from 'react'
import { Bug, Search, Plus, Filter, AlertTriangle, CheckCircle2, Clock, MoreVertical, X } from 'lucide-react'

export default function BugsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedSeverity, setSelectedSeverity] = useState('All')
  const [showModal, setShowModal] = useState(false)

  const [bugs, setBugs] = useState([
    { id: 'BUG-401', title: 'Payment callback webhook timeout on PipraPay high concurrency', project: 'Apex Global Retail', reporter: 'Marcus Vance', severity: 'Critical', status: 'In Progress', assignee: 'Forid Admin' },
    { id: 'BUG-400', title: 'Invoice PDF export missing localized currency symbol in table header', project: 'Vanguard Health Tech', reporter: 'Sarah Jenkins', severity: 'Medium', status: 'Resolved', assignee: 'Michael Scott' },
    { id: 'BUG-399', title: 'Mobile viewport overflow on staff attendance clock-in slider', project: 'Internal CRM', reporter: 'Forid Admin', severity: 'Low', status: 'Open', assignee: 'Sarah Jenkins' },
    { id: 'BUG-398', title: 'TimeSync desktop tracker fails to auto-sync offline cache over slow 3G', project: 'TimeSync App', reporter: 'Elena Rostova', severity: 'High', status: 'In Progress', assignee: 'Forid Admin' },
  ])

  const [formData, setFormData] = useState({ title: '', project: '', severity: 'Medium', assignee: 'Forid Admin' })

  const severities = ['All', 'Critical', 'High', 'Medium', 'Low']

  const filtered = bugs.filter(b => {
    const matchesSearch = b.title.toLowerCase().includes(searchQuery.toLowerCase()) || b.project.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesSev = selectedSeverity === 'All' || b.severity === selectedSeverity
    return matchesSearch && matchesSev
  })

  const getSeverityBadge = (sev) => {
    switch (sev) {
      case 'Critical': return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
      case 'High': return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
      case 'Medium': return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
      default: return 'bg-slate-700/50 text-slate-300 border-slate-600/30'
    }
  }

  const handleCreate = (e) => {
    e.preventDefault()
    if (!formData.title) return
    const newBug = {
      id: `BUG-${bugs.length + 402}`,
      title: formData.title,
      project: formData.project || 'General Project',
      reporter: 'Forid Admin',
      severity: formData.severity,
      status: 'Open',
      assignee: formData.assignee,
    }
    setBugs([newBug, ...bugs])
    setShowModal(false)
    setFormData({ title: '', project: '', severity: 'Medium', assignee: 'Forid Admin' })
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Bug className="w-6 h-6 text-rose-400" />
            <span>Bug & Issue Tracker</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Log software defects, reproduction steps, assign developer fix owners, and track resolution SLA.
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-rose-600/30 transition-all flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          <span>Report Bug</span>
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Total Defects</span>
          <p className="text-2xl font-bold text-white mt-1">{bugs.length}</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Critical Blockers</span>
          <p className="text-2xl font-bold text-rose-400 mt-1">{bugs.filter(b => b.severity === 'Critical').length}</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">In Progress</span>
          <p className="text-2xl font-bold text-amber-400 mt-1">{bugs.filter(b => b.status === 'In Progress').length}</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Resolved SLA</span>
          <p className="text-2xl font-bold text-emerald-400 mt-1">94.2%</p>
        </div>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search bug title, project..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 focus:outline-none focus:border-rose-500"
          />
        </div>

        <div className="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
          {severities.map(s => (
            <button
              key={s}
              onClick={() => setSelectedSeverity(s)}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors ${
                selectedSeverity === s ? 'bg-rose-600 text-white' : 'bg-slate-800/60 text-slate-400 hover:text-slate-200'
              }`}
            >
              {s}
            </button>
          ))}
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Bug ID</th>
              <th className="py-3.5 px-4">Issue Description</th>
              <th className="py-3.5 px-4">Project</th>
              <th className="py-3.5 px-4">Severity</th>
              <th className="py-3.5 px-4">Assignee</th>
              <th className="py-3.5 px-4">Status</th>
              <th className="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(b => (
              <tr key={b.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-rose-400">{b.id}</td>
                <td className="py-3.5 px-4 font-medium text-slate-200 max-w-sm">{b.title}</td>
                <td className="py-3.5 px-4 text-slate-300">{b.project}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold border ${getSeverityBadge(b.severity)}`}>
                    {b.severity}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-slate-300">{b.assignee}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    b.status === 'Resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                  }`}>
                    {b.status}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-right">
                  <button className="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-white">
                    <MoreVertical className="w-4 h-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-base font-bold text-white">Log Defect / Bug</h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreate} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Issue Summary</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Memory leak during large CSV upload"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-rose-500"
                />
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Affected Project</label>
                <input
                  type="text"
                  placeholder="e.g. Apex Global Retail"
                  value={formData.project}
                  onChange={(e) => setFormData({ ...formData, project: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-rose-500"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Severity</label>
                  <select
                    value={formData.severity}
                    onChange={(e) => setFormData({ ...formData, severity: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-rose-500"
                  >
                    <option>Critical</option>
                    <option>High</option>
                    <option>Medium</option>
                    <option>Low</option>
                  </select>
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Assignee</label>
                  <input
                    type="text"
                    value={formData.assignee}
                    onChange={(e) => setFormData({ ...formData, assignee: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-rose-500"
                  />
                </div>
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 rounded-lg bg-rose-600 text-white font-medium hover:bg-rose-500">
                  Save Bug
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
