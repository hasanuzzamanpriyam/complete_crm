import React, { useState } from 'react'
import { FileCheck2, Search, Plus, Calendar, User, MoreVertical, CheckCircle, Shield } from 'lucide-react'

export default function ContractsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [contracts, setContracts] = useState([
    { id: 'CTR-901', title: 'Master Services Agreement (MSA)', client: 'Apex Global Retail', value: 120000, start: '2026-01-01', end: '2026-12-31', status: 'Active', type: 'Annual Retainer' },
    { id: 'CTR-902', title: 'Software Development & SLA Agreement', client: 'CloudWave Technologies', value: 75000, start: '2026-03-15', end: '2027-03-14', status: 'Active', type: 'Fixed Scope' },
    { id: 'CTR-903', title: 'Confidentiality & Non-Disclosure (NDA)', client: 'Vanguard Health Tech', value: 0, start: '2026-06-01', end: '2028-06-01', status: 'Signed', type: 'Legal NDA' },
  ])

  const filtered = contracts.filter(c =>
    c.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
    c.client.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <FileCheck2 className="w-6 h-6 text-indigo-400" />
            <span>Contracts & Legal Agreements</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Store client contracts, monitor expiration dates, and review digital signatures.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Contract</span>
        </button>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Contract Title</th>
              <th className="py-3.5 px-4">Client</th>
              <th className="py-3.5 px-4">Contract Value</th>
              <th className="py-3.5 px-4">Contract Type</th>
              <th className="py-3.5 px-4">Start Date</th>
              <th className="py-3.5 px-4">End Date</th>
              <th className="py-3.5 px-4">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(c => (
              <tr key={c.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-semibold text-slate-200">
                  <p>{c.title}</p>
                  <span className="text-[10px] text-indigo-400 font-mono">{c.id}</span>
                </td>
                <td className="py-3.5 px-4 text-slate-300">{c.client}</td>
                <td className="py-3.5 px-4 font-bold text-white">{c.value ? `$${c.value.toLocaleString()}` : 'N/A'}</td>
                <td className="py-3.5 px-4 text-slate-400">{c.type}</td>
                <td className="py-3.5 px-4 text-slate-400">{c.start}</td>
                <td className="py-3.5 px-4 text-slate-400">{c.end}</td>
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
