import React, { useState } from 'react'
import { DollarSign, Search, Plus, Filter, Calendar, CheckCircle2, MoreVertical, CreditCard, ArrowDownLeft } from 'lucide-react'

export default function PaymentsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [payments, setPayments] = useState([
    { id: 'PAY-8901', invoice: 'INV-2026-0042', client: 'Apex Global Retail', amount: 12500, method: 'PipraPay Gateway', date: '2026-09-21 14:32', status: 'Completed', ref: 'TXN-9988231' },
    { id: 'PAY-8900', invoice: 'INV-2026-0040', client: 'CloudWave Technologies', amount: 8900, method: 'Bank Wire / Swift', date: '2026-09-20 11:15', status: 'Completed', ref: 'SWIFT-443210' },
    { id: 'PAY-8899', invoice: 'INV-2026-0039', client: 'Vanguard Health Tech', amount: 15000, method: 'Stripe Credit Card', date: '2026-09-18 09:44', status: 'Completed', ref: 'ch_3N84x92' },
    { id: 'PAY-8898', invoice: 'INV-2026-0038', client: 'Zenith Logistics Hub', amount: 4500, method: 'PipraPay Gateway', date: '2026-09-15 16:20', status: 'Completed', ref: 'TXN-7731990' },
  ])

  const filtered = payments.filter(p =>
    p.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
    p.client.toLowerCase().includes(searchQuery.toLowerCase()) ||
    p.invoice.toLowerCase().includes(searchQuery.toLowerCase())
  )

  const totalCollected = payments.reduce((acc, p) => acc + p.amount, 0)

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <DollarSign className="w-6 h-6 text-emerald-400" />
            <span>Payments Received</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Real-time transaction register of client settlements via PipraPay, Bank Transfer, and Card.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Record Offline Payment</span>
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Total Collected</span>
          <p className="text-2xl font-bold text-emerald-400 mt-1">${totalCollected.toLocaleString()}</p>
          <span className="text-[11px] text-slate-400">This billing period</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">PipraPay Volume</span>
          <p className="text-2xl font-bold text-white mt-1">$17,000</p>
          <span className="text-[11px] text-indigo-400">41.5% of total</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Settlement Success Rate</span>
          <p className="text-2xl font-bold text-purple-400 mt-1">100%</p>
          <span className="text-[11px] text-emerald-400">Zero chargebacks</span>
        </div>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search payments, invoice, client..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
          />
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Payment ID</th>
              <th className="py-3.5 px-4">Invoice #</th>
              <th className="py-3.5 px-4">Client</th>
              <th className="py-3.5 px-4">Amount</th>
              <th className="py-3.5 px-4">Gateway / Method</th>
              <th className="py-3.5 px-4">Timestamp</th>
              <th className="py-3.5 px-4">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(p => (
              <tr key={p.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-emerald-400">{p.id}</td>
                <td className="py-3.5 px-4 font-mono text-indigo-400">{p.invoice}</td>
                <td className="py-3.5 px-4 font-medium text-slate-200">{p.client}</td>
                <td className="py-3.5 px-4 font-bold text-white">${p.amount.toLocaleString()}</td>
                <td className="py-3.5 px-4 text-slate-300">
                  <div className="flex items-center gap-1.5">
                    <CreditCard className="w-3.5 h-3.5 text-slate-400" />
                    <span>{p.method}</span>
                  </div>
                </td>
                <td className="py-3.5 px-4 text-slate-400">{p.date}</td>
                <td className="py-3.5 px-4">
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {p.status}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
