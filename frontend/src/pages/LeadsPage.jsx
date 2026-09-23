import React, { useState } from 'react'
import {
  TrendingUp,
  Plus,
  Search,
  DollarSign,
  User,
  Building,
  MoreVertical,
  Kanban,
  List,
} from 'lucide-react'

export default function LeadsPage() {
  const [viewMode, setViewMode] = useState('kanban')
  const [showModal, setShowModal] = useState(false)

  const [leads, setLeads] = useState([
    {
      id: 1,
      name: 'CyberShield Systems',
      contact: 'Victor Vance',
      value: '$24,000',
      stage: 'New',
      source: 'Google Organic',
      assignee: 'Sarah J.',
    },
    {
      id: 2,
      name: 'Nexus FinTech Labs',
      contact: 'Amara Okafor',
      value: '$48,500',
      stage: 'Contacted',
      source: 'Direct Referral',
      assignee: 'Forid M.',
    },
    {
      id: 3,
      name: 'Quantum BioAnalytics',
      contact: 'Dr. Lucas Meyer',
      value: '$32,000',
      stage: 'Proposal Sent',
      source: 'Webinar',
      assignee: 'David C.',
    },
    {
      id: 4,
      name: 'Titan Logistics North',
      contact: 'Ronald Briggs',
      value: '$15,000',
      stage: 'Negotiation',
      source: 'Cold Outreach',
      assignee: 'Sarah J.',
    },
    {
      id: 5,
      name: 'AeroSpace Components Inc',
      contact: 'Hannah Abbott',
      value: '$85,000',
      stage: 'Won',
      source: 'Partnership',
      assignee: 'Forid M.',
    },
  ])

  const stages = ['New', 'Contacted', 'Proposal Sent', 'Negotiation', 'Won']

  const [newName, setNewName] = useState('')
  const [newContact, setNewContact] = useState('')
  const [newValue, setNewValue] = useState('$10,000')
  const [newStage, setNewStage] = useState('New')

  const handleAddLead = (e) => {
    e.preventDefault()
    if (!newName) return
    const newL = {
      id: Date.now(),
      name: newName,
      contact: newContact || 'Direct Contact',
      value: newValue.startsWith('$') ? newValue : `$${newValue}`,
      stage: newStage,
      source: 'Direct Web',
      assignee: 'Forid M.',
    }
    setLeads([...leads, newL])
    setShowModal(false)
    setNewName('')
    setNewContact('')
    setNewValue('$10,000')
  }

  const changeLeadStage = (id, newS) => {
    setLeads((prev) =>
      prev.map((l) => (l.id === id ? { ...l, stage: newS } : l))
    )
  }

  return (
    <div className="space-y-6">
      {/* Title & Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <TrendingUp className="w-5 h-5 text-indigo-400" />
            <span>Leads & Pipeline Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Track business opportunities from initial contact through closed deals
          </p>
        </div>

        <div className="flex items-center gap-3">
          <div className="flex items-center bg-slate-800/80 rounded-xl border border-slate-700/60 p-1">
            <button
              onClick={() => setViewMode('kanban')}
              className={`p-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 ${
                viewMode === 'kanban'
                  ? 'bg-indigo-600 text-white'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <Kanban className="w-3.5 h-3.5" />
              <span>Board</span>
            </button>
            <button
              onClick={() => setViewMode('table')}
              className={`p-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 ${
                viewMode === 'table'
                  ? 'bg-indigo-600 text-white'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <List className="w-3.5 h-3.5" />
              <span>List</span>
            </button>
          </div>

          <button
            onClick={() => setShowModal(true)}
            className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add Lead</span>
          </button>
        </div>
      </div>

      {/* Kanban Board View */}
      {viewMode === 'kanban' ? (
        <div className="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 overflow-x-auto pb-4">
          {stages.map((stage) => {
            const stageLeads = leads.filter((l) => l.stage === stage)
            return (
              <div
                key={stage}
                className="flex flex-col rounded-2xl bg-slate-900/60 border border-slate-800/80 p-3.5 space-y-3 min-w-[240px]"
              >
                <div className="flex items-center justify-between pb-2 border-b border-slate-800">
                  <div className="flex items-center gap-2">
                    <span className="text-xs font-bold text-white">{stage}</span>
                    <span className="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-slate-800 text-slate-300">
                      {stageLeads.length}
                    </span>
                  </div>
                </div>

                <div className="space-y-2.5 flex-1">
                  {stageLeads.map((lead) => (
                    <div
                      key={lead.id}
                      className="p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/50 hover:border-indigo-500/50 transition-all space-y-2 shadow-sm"
                    >
                      <div className="flex items-start justify-between gap-1">
                        <h3 className="text-xs font-semibold text-white leading-tight">
                          {lead.name}
                        </h3>
                        <span className="text-xs font-mono font-bold text-emerald-400">
                          {lead.value}
                        </span>
                      </div>

                      <p className="text-[11px] text-slate-400 flex items-center gap-1">
                        <User className="w-3 h-3 text-slate-400" /> {lead.contact}
                      </p>

                      <div className="pt-2 border-t border-slate-700/60 flex items-center justify-between text-[10px]">
                        <span className="text-slate-400">{lead.source}</span>
                        <div className="flex items-center gap-1">
                          <select
                            value={lead.stage}
                            onChange={(e) => changeLeadStage(lead.id, e.target.value)}
                            className="bg-slate-900 text-slate-300 rounded px-1 py-0.5 text-[9px] border border-slate-700"
                          >
                            {stages.map((s) => (
                              <option key={s} value={s}>
                                → {s}
                              </option>
                            ))}
                          </select>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )
          })}
        </div>
      ) : (
        /* Table View */
        <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Deal / Company</th>
                <th className="py-3 px-4">Contact</th>
                <th className="py-3 px-4">Estimated Value</th>
                <th className="py-3 px-4">Stage</th>
                <th className="py-3 px-4">Source</th>
                <th className="py-3 px-4">Assignee</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {leads.map((l) => (
                <tr key={l.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-semibold text-white">{l.name}</td>
                  <td className="py-3 px-4 text-slate-300">{l.contact}</td>
                  <td className="py-3 px-4 font-mono font-bold text-emerald-400">
                    {l.value}
                  </td>
                  <td className="py-3 px-4">
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                      {l.stage}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-slate-400">{l.source}</td>
                  <td className="py-3 px-4 text-slate-300">{l.assignee}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Add Lead Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Create New Lead</h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleAddLead} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Lead / Deal Name
                </label>
                <input
                  type="text"
                  required
                  value={newName}
                  onChange={(e) => setNewName(e.target.value)}
                  placeholder="e.g. CyberShield Security Contract"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Contact Person
                </label>
                <input
                  type="text"
                  value={newContact}
                  onChange={(e) => setNewContact(e.target.value)}
                  placeholder="e.g. Victor Vance"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Estimated Value
                  </label>
                  <input
                    type="text"
                    value={newValue}
                    onChange={(e) => setNewValue(e.target.value)}
                    placeholder="$25,000"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Initial Stage
                  </label>
                  <select
                    value={newStage}
                    onChange={(e) => setNewStage(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  >
                    {stages.map((s) => (
                      <option key={s} value={s}>
                        {s}
                      </option>
                    ))}
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
                  Create Lead
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
