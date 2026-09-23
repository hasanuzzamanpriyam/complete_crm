import React, { useState } from 'react'
import { AlertTriangle, TrendingUp, UserMinus, UserCheck, Plus, Search } from 'lucide-react'

export default function LifecyclePage() {
  const [activeTab, setActiveTab] = useState('promotions')

  const promotions = [
    { id: 1, staff: 'Elena Rostova', from: 'Product Designer', to: 'Product Manager & Lead UX', date: '2026-08-01', hike: '+25%', approvedBy: 'Forid Admin' },
    { id: 2, staff: 'Michael Scott', from: 'Junior Web Dev', to: 'Full Stack Engineer', date: '2026-07-15', hike: '+20%', approvedBy: 'Marcus Vance' },
  ]

  const warnings = [
    { id: 1, staff: 'Alex Rivera', subject: 'Repeated unexcused morning tardiness', severity: 'Formal First Warning', date: '2026-09-10', issuedBy: 'HR Desk' },
  ]

  const resignations = [
    { id: 1, staff: 'David Miller', role: 'Support Agent', noticeDate: '2026-09-01', lastDay: '2026-09-30', reason: 'Higher studies abroad', handoverTo: 'Sarah Jenkins' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <AlertTriangle className="w-6 h-6 text-amber-400" />
            <span>Employee Lifecycle Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Track promotions, formal warnings, employee resignations, notice periods, and offboarding.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Record Lifecycle Event</span>
        </button>
      </div>

      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold overflow-x-auto pb-1">
        <button
          onClick={() => setActiveTab('promotions')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'promotions' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Staff Promotions ({promotions.length})
        </button>
        <button
          onClick={() => setActiveTab('warnings')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'warnings' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Disciplinary Warnings ({warnings.length})
        </button>
        <button
          onClick={() => setActiveTab('resignations')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'resignations' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Resignations & Offboarding ({resignations.length})
        </button>
      </div>

      {activeTab === 'promotions' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Employee</th>
                <th className="py-3.5 px-4">Previous Designation</th>
                <th className="py-3.5 px-4">Promoted To</th>
                <th className="py-3.5 px-4">Compensation Hike</th>
                <th className="py-3.5 px-4">Effective Date</th>
                <th className="py-3.5 px-4">Approved By</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {promotions.map(p => (
                <tr key={p.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-semibold text-white">{p.staff}</td>
                  <td className="py-3.5 px-4 text-slate-400">{p.from}</td>
                  <td className="py-3.5 px-4 text-indigo-400 font-semibold">{p.to}</td>
                  <td className="py-3.5 px-4 text-emerald-400 font-bold">{p.hike}</td>
                  <td className="py-3.5 px-4 text-slate-400">{p.date}</td>
                  <td className="py-3.5 px-4 text-slate-300">{p.approvedBy}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'warnings' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Employee</th>
                <th className="py-3.5 px-4">Subject</th>
                <th className="py-3.5 px-4">Warning Classification</th>
                <th className="py-3.5 px-4">Issued Date</th>
                <th className="py-3.5 px-4">Authorized By</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {warnings.map(w => (
                <tr key={w.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-semibold text-white">{w.staff}</td>
                  <td className="py-3.5 px-4 text-slate-300">{w.subject}</td>
                  <td className="py-3.5 px-4">
                    <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                      {w.severity}
                    </span>
                  </td>
                  <td className="py-3.5 px-4 text-slate-400">{w.date}</td>
                  <td className="py-3.5 px-4 text-slate-300">{w.issuedBy}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'resignations' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Employee</th>
                <th className="py-3.5 px-4">Role</th>
                <th className="py-3.5 px-4">Notice Date</th>
                <th className="py-3.5 px-4">Last Working Day</th>
                <th className="py-3.5 px-4">Reason</th>
                <th className="py-3.5 px-4">Handover Lead</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {resignations.map(r => (
                <tr key={r.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-semibold text-white">{r.staff}</td>
                  <td className="py-3.5 px-4 text-slate-400">{r.role}</td>
                  <td className="py-3.5 px-4 text-slate-400">{r.noticeDate}</td>
                  <td className="py-3.5 px-4 text-amber-400 font-medium">{r.lastDay}</td>
                  <td className="py-3.5 px-4 text-slate-300">{r.reason}</td>
                  <td className="py-3.5 px-4 text-slate-300">{r.handoverTo}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
