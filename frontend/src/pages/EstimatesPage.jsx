import React, { useState } from 'react'
import {
  FileText,
  Search,
  Plus,
  Filter,
  DollarSign,
  Calendar,
  CheckCircle,
  Clock,
  Send,
  MoreVertical,
  X,
  ArrowRight,
} from 'lucide-react'

export default function EstimatesPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedStatus, setSelectedStatus] = useState('All')
  const [showModal, setShowModal] = useState(false)

  const [estimates, setEstimates] = useState([
    {
      id: 'EST-2026-0089',
      client: 'Apex Global Retail',
      contact: 'Marcus Vance',
      amount: 14500,
      date: '2026-09-20',
      expiryDate: '2026-10-20',
      status: 'Sent',
      itemsCount: 3,
    },
    {
      id: 'EST-2026-0088',
      client: 'CloudWave Technologies',
      contact: 'Elena Rostova',
      amount: 8900,
      date: '2026-09-18',
      expiryDate: '2026-10-18',
      status: 'Accepted',
      itemsCount: 2,
    },
    {
      id: 'EST-2026-0087',
      client: 'Vanguard Health Tech',
      contact: 'Dr. Arthur Pendelton',
      amount: 32000,
      date: '2026-09-15',
      expiryDate: '2026-09-30',
      status: 'Draft',
      itemsCount: 5,
    },
    {
      id: 'EST-2026-0086',
      client: 'Omni Media Global',
      contact: 'Chloe Bennett',
      amount: 6750,
      date: '2026-09-10',
      expiryDate: '2026-09-25',
      status: 'Expired',
      itemsCount: 1,
    },
    {
      id: 'EST-2026-0085',
      client: 'Zenith Logistics Hub',
      contact: 'Tariq Al-Mansoor',
      amount: 19800,
      date: '2026-09-05',
      expiryDate: '2026-10-05',
      status: 'Accepted',
      itemsCount: 4,
    },
  ])

  const [formData, setFormData] = useState({
    client: '',
    amount: '',
    expiryDate: '',
    notes: '',
  })

  const statuses = ['All', 'Draft', 'Sent', 'Accepted', 'Expired', 'Declined']

  const filtered = estimates.filter((est) => {
    const matchesSearch =
      est.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      est.client.toLowerCase().includes(searchQuery.toLowerCase())
    const matchesStatus = selectedStatus === 'All' || est.status === selectedStatus
    return matchesSearch && matchesStatus
  })

  const handleCreate = (e) => {
    e.preventDefault()
    if (!formData.client || !formData.amount) return
    const newEst = {
      id: `EST-2026-${String(estimates.length + 90).padStart(4, '0')}`,
      client: formData.client,
      contact: 'Primary Client',
      amount: parseFloat(formData.amount) || 0,
      date: new Date().toISOString().split('T')[0],
      expiryDate: formData.expiryDate || '2026-10-30',
      status: 'Draft',
      itemsCount: 1,
    }
    setEstimates([newEst, ...estimates])
    setShowModal(false)
    setFormData({ client: '', amount: '', expiryDate: '', notes: '' })
  }

  const getStatusBadge = (status) => {
    switch (status) {
      case 'Accepted':
        return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
      case 'Sent':
        return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
      case 'Draft':
        return 'bg-slate-700/50 text-slate-300 border-slate-600/30'
      case 'Expired':
        return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
      default:
        return 'bg-slate-800 text-slate-400 border-slate-700'
    }
  }

  return (
    <div className="space-y-6">
      {/* Top Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <FileText className="w-6 h-6 text-indigo-400" />
            <span>Estimates & Price Quotations</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Create quotations, send proposals to clients, and convert accepted estimates into invoices.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2 cursor-pointer w-fit"
        >
          <Plus className="w-4 h-4" />
          <span>Create Estimate</span>
        </button>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Total Quotations</span>
          <p className="text-2xl font-bold text-white mt-1">
            ${estimates.reduce((acc, e) => acc + e.amount, 0).toLocaleString()}
          </p>
          <span className="text-[11px] text-slate-400">{estimates.length} quotes prepared</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Accepted Quotes</span>
          <p className="text-2xl font-bold text-emerald-400 mt-1">
            ${estimates.filter((e) => e.status === 'Accepted').reduce((acc, e) => acc + e.amount, 0).toLocaleString()}
          </p>
          <span className="text-[11px] text-emerald-400">Ready for invoice conversion</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Pending Client Decision</span>
          <p className="text-2xl font-bold text-blue-400 mt-1">
            ${estimates.filter((e) => e.status === 'Sent').reduce((acc, e) => acc + e.amount, 0).toLocaleString()}
          </p>
          <span className="text-[11px] text-blue-400">Under client review</span>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search estimate ID, client..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div className="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
          {statuses.map((st) => (
            <button
              key={st}
              onClick={() => setSelectedStatus(st)}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors ${
                selectedStatus === st
                  ? 'bg-indigo-600 text-white'
                  : 'bg-slate-800/60 text-slate-400 hover:text-slate-200'
              }`}
            >
              {st}
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
                <th className="py-3.5 px-4">Estimate ID</th>
                <th className="py-3.5 px-4">Client</th>
                <th className="py-3.5 px-4">Amount</th>
                <th className="py-3.5 px-4">Issued Date</th>
                <th className="py-3.5 px-4">Expiry Date</th>
                <th className="py-3.5 px-4">Status</th>
                <th className="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filtered.map((est) => (
                <tr key={est.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3.5 px-4 font-mono font-semibold text-indigo-400">{est.id}</td>
                  <td className="py-3.5 px-4">
                    <p className="font-medium text-slate-200">{est.client}</p>
                    <span className="text-[10px] text-slate-400">{est.contact}</span>
                  </td>
                  <td className="py-3.5 px-4 font-bold text-white">${est.amount.toLocaleString()}</td>
                  <td className="py-3.5 px-4 text-slate-400">{est.date}</td>
                  <td className="py-3.5 px-4 text-slate-400">{est.expiryDate}</td>
                  <td className="py-3.5 px-4">
                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold border ${getStatusBadge(est.status)}`}>
                      {est.status}
                    </span>
                  </td>
                  <td className="py-3.5 px-4 text-right">
                    <div className="flex items-center justify-end gap-2">
                      {est.status === 'Accepted' && (
                        <button
                          title="Convert to Invoice"
                          className="px-2 py-1 rounded bg-indigo-600/20 text-indigo-300 hover:bg-indigo-600/30 text-[10px] font-medium flex items-center gap-1"
                        >
                          <span>Invoice</span>
                          <ArrowRight className="w-3 h-3" />
                        </button>
                      )}
                      <button className="p-1 rounded-md hover:bg-slate-800 text-slate-400 hover:text-slate-200">
                        <MoreVertical className="w-4 h-4" />
                      </button>
                    </div>
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
              <h2 className="text-base font-bold text-white">Create Price Estimate</h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreate} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Client Name</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Apex Global Retail"
                  value={formData.client}
                  onChange={(e) => setFormData({ ...formData, client: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Total Estimated Amount ($)</label>
                <input
                  type="number"
                  required
                  placeholder="15000"
                  value={formData.amount}
                  onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Valid Until / Expiry Date</label>
                <input
                  type="date"
                  value={formData.expiryDate}
                  onChange={(e) => setFormData({ ...formData, expiryDate: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:border-indigo-500"
                />
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500"
                >
                  Save Estimate
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
