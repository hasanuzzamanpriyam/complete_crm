import React, { useState } from 'react'
import {
  Briefcase,
  Search,
  Plus,
  Filter,
  DollarSign,
  Calendar,
  Building2,
  User,
  ArrowUpRight,
  CheckCircle2,
  XCircle,
  MoreVertical,
  X,
} from 'lucide-react'

export default function OpportunitiesPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedStage, setSelectedStage] = useState('All')
  const [showModal, setShowModal] = useState(false)

  const [opportunities, setOpportunities] = useState([
    {
      id: 1,
      title: 'Cloud Infrastructure Migration',
      client: 'Apex Global Retail',
      contact: 'Marcus Vance',
      value: 65000,
      stage: 'Proposal Sent',
      probability: 70,
      expectedClose: '2026-10-15',
      source: 'Referral',
      owner: 'Forid Admin',
    },
    {
      id: 2,
      title: 'Enterprise ERP Customization',
      client: 'Vanguard Health Tech',
      contact: 'Dr. Arthur Pendelton',
      value: 48000,
      stage: 'Negotiation',
      probability: 85,
      expectedClose: '2026-10-02',
      source: 'Website',
      owner: 'Sarah Jenkins',
    },
    {
      id: 3,
      title: 'Multi-Store POS Integration',
      client: 'Omni Media Global',
      contact: 'Chloe Bennett',
      value: 28500,
      stage: 'Qualification',
      probability: 40,
      expectedClose: '2026-11-01',
      source: 'Direct Lead',
      owner: 'Forid Admin',
    },
    {
      id: 4,
      title: 'AI Customer Service Chatbot',
      client: 'CloudWave Technologies',
      contact: 'Elena Rostova',
      value: 36000,
      stage: 'Closed Won',
      probability: 100,
      expectedClose: '2026-09-18',
      source: 'Existing Client',
      owner: 'Sarah Jenkins',
    },
    {
      id: 5,
      title: 'Cybersecurity Threat Audit',
      client: 'Zenith Logistics Hub',
      contact: 'Tariq Al-Mansoor',
      value: 22000,
      stage: 'Proposal Sent',
      probability: 60,
      expectedClose: '2026-10-20',
      source: 'Outreach',
      owner: 'Michael Scott',
    },
  ])

  const [formData, setFormData] = useState({
    title: '',
    client: '',
    value: '',
    stage: 'Qualification',
    probability: 50,
    expectedClose: '',
  })

  const stages = ['All', 'Qualification', 'Proposal Sent', 'Negotiation', 'Closed Won', 'Closed Lost']

  const filteredOpportunities = opportunities.filter((op) => {
    const matchesSearch =
      op.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      op.client.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesStage = selectedStage === 'All' || op.stage === selectedStage
    return matchesSearch && matchesStage
  })

  const totalValue = opportunities.reduce((acc, curr) => acc + curr.value, 0)
  const weightedValue = opportunities.reduce((acc, curr) => acc + (curr.value * curr.probability) / 100, 0)
  const wonCount = opportunities.filter((o) => o.stage === 'Closed Won').length

  const handleCreate = (e) => {
    e.preventDefault()
    if (!formData.title || !formData.client) return
    const newOp = {
      id: Date.now(),
      title: formData.title,
      client: formData.client,
      contact: 'Primary Contact',
      value: parseFloat(formData.value) || 0,
      stage: formData.stage,
      probability: parseInt(formData.probability) || 50,
      expectedClose: formData.expectedClose || new Date().toISOString().split('T')[0],
      source: 'Direct Lead',
      owner: 'Forid Admin',
    }
    setOpportunities([newOp, ...opportunities])
    setShowModal(false)
    setFormData({
      title: '',
      client: '',
      value: '',
      stage: 'Qualification',
      probability: 50,
      expectedClose: '',
    })
  }

  const getStageBadge = (stage) => {
    switch (stage) {
      case 'Closed Won':
        return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
      case 'Closed Lost':
        return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
      case 'Negotiation':
        return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
      case 'Proposal Sent':
        return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
      default:
        return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
    }
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Briefcase className="w-6 h-6 text-indigo-400" />
            <span>Opportunities & Deal Pipeline</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Track business opportunities, forecast revenue, and manage deal stages.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2 cursor-pointer w-fit"
        >
          <Plus className="w-4 h-4" />
          <span>New Opportunity</span>
        </button>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Total Pipeline Value</span>
          <p className="text-2xl font-bold text-white mt-1">${totalValue.toLocaleString()}</p>
          <span className="text-[11px] text-indigo-400">5 active deals</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Weighted Forecast</span>
          <p className="text-2xl font-bold text-emerald-400 mt-1">${Math.round(weightedValue).toLocaleString()}</p>
          <span className="text-[11px] text-slate-400">Probability adjusted</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Win Rate</span>
          <p className="text-2xl font-bold text-amber-400 mt-1">
            {((wonCount / opportunities.length) * 100).toFixed(0)}%
          </p>
          <span className="text-[11px] text-slate-400">{wonCount} deals won</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Avg Deal Size</span>
          <p className="text-2xl font-bold text-purple-400 mt-1">
            ${Math.round(totalValue / (opportunities.length || 1)).toLocaleString()}
          </p>
          <span className="text-[11px] text-slate-400">Across all sectors</span>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search deals, clients..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div className="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
          {stages.map((stg) => (
            <button
              key={stg}
              onClick={() => setSelectedStage(stg)}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors ${
                selectedStage === stg
                  ? 'bg-indigo-600 text-white'
                  : 'bg-slate-800/60 text-slate-400 hover:text-slate-200'
              }`}
            >
              {stg}
            </button>
          ))}
        </div>
      </div>

      {/* Table */}
      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Opportunity</th>
                <th className="py-3.5 px-4">Client</th>
                <th className="py-3.5 px-4">Deal Value</th>
                <th className="py-3.5 px-4">Stage</th>
                <th className="py-3.5 px-4">Probability</th>
                <th className="py-3.5 px-4">Expected Close</th>
                <th className="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filteredOpportunities.map((op) => (
                <tr key={op.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3.5 px-4 font-semibold text-slate-200">
                    <p>{op.title}</p>
                    <span className="text-[10px] text-slate-400 font-normal">{op.source}</span>
                  </td>
                  <td className="py-3.5 px-4">
                    <div className="flex items-center gap-2">
                      <Building2 className="w-3.5 h-3.5 text-slate-500" />
                      <span className="text-slate-300">{op.client}</span>
                    </div>
                  </td>
                  <td className="py-3.5 px-4 font-bold text-white">${op.value.toLocaleString()}</td>
                  <td className="py-3.5 px-4">
                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold border ${getStageBadge(op.stage)}`}>
                      {op.stage}
                    </span>
                  </td>
                  <td className="py-3.5 px-4">
                    <div className="flex items-center gap-2">
                      <div className="w-16 h-1.5 rounded-full bg-slate-800 overflow-hidden">
                        <div
                          className="h-full bg-indigo-500 rounded-full"
                          style={{ width: `${op.probability}%` }}
                        />
                      </div>
                      <span className="text-[11px] text-slate-400">{op.probability}%</span>
                    </div>
                  </td>
                  <td className="py-3.5 px-4 text-slate-400">{op.expectedClose}</td>
                  <td className="py-3.5 px-4 text-right">
                    <button className="p-1 rounded-md hover:bg-slate-800 text-slate-400 hover:text-slate-200">
                      <MoreVertical className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-base font-bold text-white">Create New Opportunity</h2>
              <button
                onClick={() => setShowModal(false)}
                className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Opportunity Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. AWS Migration Contract"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>

              <div>
                <label className="block text-slate-300 font-medium mb-1">Client Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Acme Corp"
                  value={formData.client}
                  onChange={(e) => setFormData({ ...formData, client: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Value ($)</label>
                  <input
                    type="number"
                    placeholder="25000"
                    value={formData.value}
                    onChange={(e) => setFormData({ ...formData, value: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Probability (%)</label>
                  <input
                    type="number"
                    min="0"
                    max="100"
                    value={formData.probability}
                    onChange={(e) => setFormData({ ...formData, probability: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Stage</label>
                  <select
                    value={formData.stage}
                    onChange={(e) => setFormData({ ...formData, stage: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                  >
                    <option>Qualification</option>
                    <option>Proposal Sent</option>
                    <option>Negotiation</option>
                    <option>Closed Won</option>
                    <option>Closed Lost</option>
                  </select>
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Expected Close</label>
                  <input
                    type="date"
                    value={formData.expectedClose}
                    onChange={(e) => setFormData({ ...formData, expectedClose: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>

              <div className="pt-2 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium"
                >
                  Save Deal
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
