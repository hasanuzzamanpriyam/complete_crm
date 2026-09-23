import React, { useState } from 'react'
import { AlertCircle, Search, Plus, CheckCircle2, Clock, MoreVertical, X } from 'lucide-react'

export default function ComplaintsPage() {
  const [complaints, setComplaints] = useState([
    { id: 'CMP-102', client: 'Apex Global Retail', subject: 'Server connection drop during flash sale peak', priority: 'High', date: '2026-09-20', status: 'Under Investigation', resolution: 'Investigating load balancer logs' },
    { id: 'CMP-101', client: 'Vanguard Health Tech', subject: 'Delayed invoice dispatch from automated billing queue', priority: 'Medium', date: '2026-09-14', status: 'Resolved', resolution: 'Cron daemon restarted and email queue cleared' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <AlertCircle className="w-6 h-6 text-rose-400" />
            <span>Customer Complaints & Escalations</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Resolve critical customer grievances, track executive escalations, and manage dispute resolution.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-rose-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Log Complaint</span>
        </button>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Ticket / Complaint #</th>
              <th className="py-3.5 px-4">Client</th>
              <th className="py-3.5 px-4">Subject</th>
              <th className="py-3.5 px-4">Priority</th>
              <th className="py-3.5 px-4">Date Logged</th>
              <th className="py-3.5 px-4">Resolution Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {complaints.map(c => (
              <tr key={c.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-rose-400">{c.id}</td>
                <td className="py-3.5 px-4 font-medium text-white">{c.client}</td>
                <td className="py-3.5 px-4 text-slate-200">{c.subject}</td>
                <td className="py-3.5 px-4">
                  <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                    {c.priority}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-slate-400">{c.date}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    c.status === 'Resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                  }`}>
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
