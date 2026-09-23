import React, { useState } from 'react'
import { FileText, Search, Plus, Filter, Calendar, User, MoreVertical, X, CheckCircle, Clock } from 'lucide-react'

export default function ProposalsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [proposals, setProposals] = useState([
    { id: 'PROP-104', subject: 'Digital Transformation & Cloud ERP', client: 'Apex Global Retail', total: 68000, date: '2026-09-18', openTill: '2026-10-18', status: 'Sent' },
    { id: 'PROP-103', subject: 'Custom AI Voice Agent Integration', client: 'CloudWave Technologies', total: 34500, date: '2026-09-12', openTill: '2026-10-12', status: 'Accepted' },
    { id: 'PROP-102', subject: 'Annual IT Support & Server Maintenance', client: 'Zenith Logistics Hub', total: 18200, date: '2026-09-08', openTill: '2026-09-28', status: 'Revised' },
    { id: 'PROP-101', subject: 'Mobile Application Wireframe & MVP', client: 'Omni Media Global', total: 25000, date: '2026-08-30', openTill: '2026-09-30', status: 'Draft' },
  ])

  const filtered = proposals.filter(p =>
    p.subject.toLowerCase().includes(searchQuery.toLowerCase()) ||
    p.client.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <FileText className="w-6 h-6 text-indigo-400" />
            <span>Sales Proposals</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Pitch comprehensive contracts, deliver rich proposals, and monitor client acceptance.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Proposal</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search proposals..."
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
              <th className="py-3.5 px-4">Proposal</th>
              <th className="py-3.5 px-4">To Client</th>
              <th className="py-3.5 px-4">Total Value</th>
              <th className="py-3.5 px-4">Date</th>
              <th className="py-3.5 px-4">Open Till</th>
              <th className="py-3.5 px-4">Status</th>
              <th className="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(p => (
              <tr key={p.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-semibold text-slate-200">
                  <p>{p.subject}</p>
                  <span className="text-[10px] text-indigo-400 font-mono">{p.id}</span>
                </td>
                <td className="py-3.5 px-4 text-slate-300">{p.client}</td>
                <td className="py-3.5 px-4 font-bold text-white">${p.total.toLocaleString()}</td>
                <td className="py-3.5 px-4 text-slate-400">{p.date}</td>
                <td className="py-3.5 px-4 text-slate-400">{p.openTill}</td>
                <td className="py-3.5 px-4">
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {p.status}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-right">
                  <button className="p-1 rounded-md hover:bg-slate-800 text-slate-400 hover:text-white">
                    <MoreVertical className="w-4 h-4" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
