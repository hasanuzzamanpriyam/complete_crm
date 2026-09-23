import React, { useState } from 'react'
import {
  Receipt,
  Plus,
  Search,
  Filter,
  DollarSign,
  Download,
  CheckCircle2,
  AlertCircle,
  Clock,
  CreditCard,
} from 'lucide-react'

export default function InvoicesPage() {
  const [filterStatus, setFilterStatus] = useState('All')
  const [searchQuery, setSearchQuery] = useState('')
  const [showModal, setShowModal] = useState(false)

  const [invoices, setInvoices] = useState([
    {
      id: 'INV-2026-001',
      client: 'Apex Global Retail',
      date: '2026-09-01',
      dueDate: '2026-09-18',
      amount: '$4,800.00',
      paid: '$4,800.00',
      due: '$0.00',
      status: 'Paid',
    },
    {
      id: 'INV-2026-002',
      client: 'Vanguard Health Tech',
      date: '2026-09-10',
      dueDate: '2026-10-05',
      amount: '$7,250.00',
      paid: '$0.00',
      due: '$7,250.00',
      status: 'Due',
    },
    {
      id: 'INV-2026-003',
      client: 'Omni Media Global',
      date: '2026-08-20',
      dueDate: '2026-09-12',
      amount: '$2,100.00',
      paid: '$0.00',
      due: '$2,100.00',
      status: 'Overdue',
    },
    {
      id: 'INV-2026-004',
      client: 'BlueHorizon Consulting',
      date: '2026-09-12',
      dueDate: '2026-10-10',
      amount: '$5,000.00',
      paid: '$1,600.00',
      due: '$3,400.00',
      status: 'Partially Paid',
    },
  ])

  const [clientName, setClientName] = useState('')
  const [amountVal, setAmountVal] = useState('')
  const [dueDateVal, setDueDateVal] = useState('')

  const handleCreate = (e) => {
    e.preventDefault()
    if (!clientName || !amountVal) return
    const newInv = {
      id: `INV-2026-${String(invoices.length + 1).padStart(3, '0')}`,
      client: clientName,
      date: new Date().toISOString().split('T')[0],
      dueDate: dueDateVal || '2026-10-15',
      amount: amountVal.startsWith('$') ? amountVal : `$${amountVal}`,
      paid: '$0.00',
      due: amountVal.startsWith('$') ? amountVal : `$${amountVal}`,
      status: 'Due',
    }
    setInvoices([newInv, ...invoices])
    setShowModal(false)
    setClientName('')
    setAmountVal('')
  }

  const filtered = invoices.filter((i) => {
    const matchesStatus = filterStatus === 'All' || i.status === filterStatus
    const matchesQuery =
      i.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      i.client.toLowerCase().includes(searchQuery.toLowerCase())
    return matchesStatus && matchesQuery
  })

  return (
    <div className="space-y-6">
      {/* Title & Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Receipt className="w-5 h-5 text-indigo-400" />
            <span>Invoices & Billing</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Issue corporate invoices, track payments, and reconcile PipraPay gateway settlements
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Create Invoice</span>
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Total Invoiced</span>
          <p className="text-xl font-bold text-white mt-1">$19,150.00</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Paid Amount</span>
          <p className="text-xl font-bold text-emerald-400 mt-1">$6,400.00</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Due Amount</span>
          <p className="text-xl font-bold text-indigo-400 mt-1">$10,650.00</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Overdue</span>
          <p className="text-xl font-bold text-rose-400 mt-1">$2,100.00</p>
        </div>
      </div>

      {/* Table Container */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
        {/* Filters */}
        <div className="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div className="relative flex-1 max-w-sm">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search by invoice # or client..."
              className="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-800 border border-slate-700/60 text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
          </div>

          <div className="flex items-center gap-1.5 overflow-x-auto">
            {['All', 'Paid', 'Due', 'Overdue', 'Partially Paid'].map((st) => (
              <button
                key={st}
                onClick={() => setFilterStatus(st)}
                className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap ${
                  filterStatus === st
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-slate-800 text-slate-400 hover:text-white'
                }`}
              >
                {st}
              </button>
            ))}
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Invoice #</th>
                <th className="py-3 px-4">Client</th>
                <th className="py-3 px-4">Issue Date</th>
                <th className="py-3 px-4">Due Date</th>
                <th className="py-3 px-4">Total Amount</th>
                <th className="py-3 px-4">Paid</th>
                <th className="py-3 px-4">Balance Due</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {filtered.map((inv) => (
                <tr key={inv.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-mono font-bold text-indigo-300">
                    {inv.id}
                  </td>
                  <td className="py-3 px-4 font-medium text-white">{inv.client}</td>
                  <td className="py-3 px-4 text-slate-400">{inv.date}</td>
                  <td className="py-3 px-4 text-slate-300">{inv.dueDate}</td>
                  <td className="py-3 px-4 font-mono font-bold text-white">
                    {inv.amount}
                  </td>
                  <td className="py-3 px-4 font-mono text-emerald-400 font-semibold">
                    {inv.paid}
                  </td>
                  <td className="py-3 px-4 font-mono text-rose-400 font-semibold">
                    {inv.due}
                  </td>
                  <td className="py-3 px-4">
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                        inv.status === 'Paid'
                          ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                          : inv.status === 'Due'
                          ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
                          : inv.status === 'Overdue'
                          ? 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                          : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                      }`}
                    >
                      {inv.status}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <button className="px-2.5 py-1 text-[11px] font-semibold text-indigo-400 hover:text-white hover:bg-indigo-600/30 rounded-lg transition-colors cursor-pointer">
                      View Invoice
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create Invoice Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Issue New Invoice</h3>
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
                  Client / Organization
                </label>
                <input
                  type="text"
                  required
                  value={clientName}
                  onChange={(e) => setClientName(e.target.value)}
                  placeholder="e.g. Apex Global Retail"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Invoice Amount
                  </label>
                  <input
                    type="text"
                    required
                    value={amountVal}
                    onChange={(e) => setAmountVal(e.target.value)}
                    placeholder="$3,500.00"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Due Date
                  </label>
                  <input
                    type="date"
                    required
                    value={dueDateVal}
                    onChange={(e) => setDueDateVal(e.target.value)}
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
                  Create Invoice
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
