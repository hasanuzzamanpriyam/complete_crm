import React, { useState } from 'react'
import { FileMinus, Search, Plus, Filter, Calendar, MoreVertical, X, CheckCircle2 } from 'lucide-react'

export default function CreditNotesPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [credits, setCredits] = useState([
    { id: 'CN-2026-0012', invoice: 'INV-2026-0038', client: 'Zenith Logistics Hub', amount: 800, date: '2026-09-19', status: 'Applied', reason: 'Service SLA refund' },
    { id: 'CN-2026-0011', invoice: 'INV-2026-0035', client: 'Apex Global Retail', amount: 1200, date: '2026-09-12', status: 'Applied', reason: 'Unused cloud hosting quota' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <FileMinus className="w-6 h-6 text-rose-400" />
            <span>Credit Notes</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Manage credit memos, bill adjustments, and customer refunds.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Credit Note</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search credit notes..."
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
              <th className="py-3.5 px-4">Credit Note #</th>
              <th className="py-3.5 px-4">Invoice Ref</th>
              <th className="py-3.5 px-4">Client</th>
              <th className="py-3.5 px-4">Credit Amount</th>
              <th className="py-3.5 px-4">Reason</th>
              <th className="py-3.5 px-4">Date</th>
              <th className="py-3.5 px-4">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {credits.map(c => (
              <tr key={c.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-rose-400">{c.id}</td>
                <td className="py-3.5 px-4 font-mono text-indigo-400">{c.invoice}</td>
                <td className="py-3.5 px-4 text-slate-200 font-medium">{c.client}</td>
                <td className="py-3.5 px-4 font-bold text-white">${c.amount.toLocaleString()}</td>
                <td className="py-3.5 px-4 text-slate-400">{c.reason}</td>
                <td className="py-3.5 px-4 text-slate-400">{c.date}</td>
                <td className="py-3.5 px-4">
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {c.status}
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
