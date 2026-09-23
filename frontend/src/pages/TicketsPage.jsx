import React, { useState } from 'react'
import {
  HelpCircle,
  Plus,
  Search,
  MessageSquare,
  AlertCircle,
  CheckCircle2,
  Clock,
  User,
} from 'lucide-react'

export default function TicketsPage() {
  const [filterPriority, setFilterPriority] = useState('All')
  const [searchQuery, setSearchQuery] = useState('')
  const [showModal, setShowModal] = useState(false)

  const [tickets, setTickets] = useState([
    {
      id: 'TK-482',
      subject: 'Database Connection Timeout on High Concurrency',
      client: 'Apex Global Retail',
      department: 'Technical Support',
      priority: 'High',
      status: 'Open',
      updated: '15 mins ago',
      color: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    },
    {
      id: 'TK-480',
      subject: 'PipraPay Webhook Signature Mismatch on Callback',
      client: 'CloudWave Technologies',
      department: 'Billing & Gateway',
      priority: 'Urgent',
      status: 'In Progress',
      updated: '1 hour ago',
      color: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    },
    {
      id: 'TK-476',
      subject: 'Request for Multi-Department Access Permissions',
      client: 'Vanguard Health Tech',
      department: 'Account Management',
      priority: 'Normal',
      status: 'Answered',
      updated: '3 hours ago',
      color: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    },
    {
      id: 'TK-465',
      subject: 'SSL Certificate Renewal for Primary Domain',
      client: 'Omni Media Global',
      department: 'Server Hosting',
      priority: 'Low',
      status: 'Closed',
      updated: 'Yesterday',
      color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    },
  ])

  const [subject, setSubject] = useState('')
  const [client, setClient] = useState('')
  const [priority, setPriority] = useState('Normal')
  const [dept, setDept] = useState('Technical Support')

  const handleCreate = (e) => {
    e.preventDefault()
    if (!subject || !client) return
    const newT = {
      id: `TK-${Math.floor(485 + Math.random() * 50)}`,
      subject,
      client,
      department: dept,
      priority,
      status: 'Open',
      updated: 'Just now',
      color:
        priority === 'Urgent'
          ? 'bg-rose-500/10 text-rose-400 border-rose-500/20'
          : priority === 'High'
          ? 'bg-amber-500/10 text-amber-400 border-amber-500/20'
          : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    }
    setTickets([newT, ...tickets])
    setShowModal(false)
    setSubject('')
    setClient('')
  }

  const filtered = tickets.filter((t) => {
    const matchesPriority = filterPriority === 'All' || t.priority === filterPriority
    const matchesQuery =
      t.subject.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.client.toLowerCase().includes(searchQuery.toLowerCase())
    return matchesPriority && matchesQuery
  })

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <HelpCircle className="w-5 h-5 text-indigo-400" />
            <span>Support Tickets & Help Desk</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Resolve customer inquiries, service incidents, and technical requests
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>New Ticket</span>
        </button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Open Tickets</span>
          <p className="text-xl font-bold text-rose-400 mt-1">1 Open</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">In Progress</span>
          <p className="text-xl font-bold text-amber-400 mt-1">1 Active</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Awaiting Response</span>
          <p className="text-xl font-bold text-indigo-400 mt-1">1 Ticket</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Resolved (30d)</span>
          <p className="text-xl font-bold text-emerald-400 mt-1">42 Closed</p>
        </div>
      </div>

      {/* Table */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
        <div className="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div className="relative flex-1 max-w-sm">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search by ticket #, subject or client..."
              className="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-800 border border-slate-700/60 text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <div className="flex items-center gap-1.5">
            {['All', 'Urgent', 'High', 'Normal'].map((p) => (
              <button
                key={p}
                onClick={() => setFilterPriority(p)}
                className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors ${
                  filterPriority === p
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-slate-800 text-slate-400 hover:text-white'
                }`}
              >
                {p}
              </button>
            ))}
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Ticket ID</th>
                <th className="py-3 px-4">Subject</th>
                <th className="py-3 px-4">Client</th>
                <th className="py-3 px-4">Department</th>
                <th className="py-3 px-4">Priority</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4 text-right">Updated</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filtered.map((t) => (
                <tr key={t.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-mono font-bold text-indigo-300">{t.id}</td>
                  <td className="py-3 px-4 font-semibold text-white">{t.subject}</td>
                  <td className="py-3 px-4 text-slate-300">{t.client}</td>
                  <td className="py-3 px-4 text-slate-400">{t.department}</td>
                  <td className="py-3 px-4">
                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${t.color}`}>
                      {t.priority}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-200">
                      {t.status}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-right text-slate-400">{t.updated}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Create Support Ticket</h3>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white text-lg font-bold">
                ×
              </button>
            </div>

            <form onSubmit={handleCreate} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">Subject</label>
                <input
                  type="text"
                  required
                  value={subject}
                  onChange={(e) => setSubject(e.target.value)}
                  placeholder="Summary of inquiry or incident..."
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">Client Name</label>
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
                  <label className="block text-xs font-medium text-slate-300 mb-1">Department</label>
                  <select
                    value={dept}
                    onChange={(e) => setDept(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="Technical Support">Technical Support</option>
                    <option value="Billing & Gateway">Billing & Gateway</option>
                    <option value="Server Hosting">Server Hosting</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">Priority</label>
                  <select
                    value={priority}
                    onChange={(e) => setPriority(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="Normal">Normal</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                  </select>
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
                  Submit Ticket
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
