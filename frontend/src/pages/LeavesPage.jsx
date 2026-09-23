import React, { useState } from 'react'
import { Calendar, Plus, CheckCircle2, XCircle, Clock, Search, MoreVertical, X } from 'lucide-react'

export default function LeavesPage() {
  const [leaves, setLeaves] = useState([
    { id: 1, staff: 'Michael Scott', department: 'Software Engineering', type: 'Annual Paid Leave', start: '2026-09-25', end: '2026-09-28', days: 4, reason: 'Family medical visit', status: 'Pending' },
    { id: 2, staff: 'Sarah Jenkins', department: 'Finance & Accounts', type: 'Casual Leave', start: '2026-09-20', end: '2026-09-21', days: 2, reason: 'Personal errands', status: 'Approved' },
    { id: 3, staff: 'Elena Rostova', department: 'Product & Design', type: 'Sick Leave', start: '2026-09-15', end: '2026-09-16', days: 2, reason: 'Viral flu recovery', status: 'Approved' },
  ])

  const [showModal, setShowModal] = useState(false)
  const [form, setForm] = useState({ staff: 'Forid Admin', type: 'Annual Paid Leave', start: '', end: '', reason: '' })

  const handleStatus = (id, newStatus) => {
    setLeaves(leaves.map(l => l.id === id ? { ...l, status: newStatus } : l))
  }

  const handleApply = (e) => {
    e.preventDefault()
    setLeaves([{ id: Date.now(), staff: form.staff, department: 'Software Engineering', type: form.type, start: form.start, end: form.end, days: 3, reason: form.reason, status: 'Pending' }, ...leaves])
    setShowModal(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Calendar className="w-6 h-6 text-indigo-400" />
            <span>Leave Management & Applications</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Review employee time-off requests, check quotas, and approve or decline vacation applications.
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          <span>Apply for Leave</span>
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Annual Quota</span>
          <p className="text-2xl font-bold text-white mt-1">14 Days</p>
          <span className="text-[11px] text-emerald-400">10 remaining</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Casual Leave</span>
          <p className="text-2xl font-bold text-indigo-400 mt-1">10 Days</p>
          <span className="text-[11px] text-indigo-400">7 remaining</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Sick Leave</span>
          <p className="text-2xl font-bold text-amber-400 mt-1">14 Days</p>
          <span className="text-[11px] text-amber-400">12 remaining</span>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
          <span className="text-xs text-slate-400">Pending Approvals</span>
          <p className="text-2xl font-bold text-rose-400 mt-1">{leaves.filter(l => l.status === 'Pending').length}</p>
          <span className="text-[11px] text-slate-400">Awaiting HR review</span>
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Employee</th>
              <th className="py-3.5 px-4">Leave Category</th>
              <th className="py-3.5 px-4">Duration</th>
              <th className="py-3.5 px-4">Days</th>
              <th className="py-3.5 px-4">Reason</th>
              <th className="py-3.5 px-4">Status</th>
              <th className="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {leaves.map(l => (
              <tr key={l.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-semibold text-slate-200">
                  <p>{l.staff}</p>
                  <span className="text-[10px] text-slate-400 font-normal">{l.department}</span>
                </td>
                <td className="py-3.5 px-4 text-slate-300">{l.type}</td>
                <td className="py-3.5 px-4 text-slate-400">{l.start} to {l.end}</td>
                <td className="py-3.5 px-4 font-bold text-white">{l.days}d</td>
                <td className="py-3.5 px-4 text-slate-400 max-w-xs">{l.reason}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    l.status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                  }`}>
                    {l.status}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-right">
                  {l.status === 'Pending' ? (
                    <div className="flex items-center justify-end gap-1.5">
                      <button onClick={() => handleStatus(l.id, 'Approved')} className="p-1 rounded bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/30">
                        <CheckCircle2 className="w-4 h-4" />
                      </button>
                      <button onClick={() => handleStatus(l.id, 'Rejected')} className="p-1 rounded bg-rose-600/20 text-rose-400 hover:bg-rose-600/30">
                        <XCircle className="w-4 h-4" />
                      </button>
                    </div>
                  ) : (
                    <span className="text-slate-500 text-[11px]">Processed</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-base font-bold text-white">Apply for Leave</h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleApply} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Leave Type</label>
                <select
                  value={form.type}
                  onChange={e => setForm({ ...form, type: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                >
                  <option>Annual Paid Leave</option>
                  <option>Casual Leave</option>
                  <option>Sick Leave</option>
                  <option>Maternity / Paternity</option>
                </select>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Start Date</label>
                  <input
                    type="date"
                    required
                    value={form.start}
                    onChange={e => setForm({ ...form, start: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">End Date</label>
                  <input
                    type="date"
                    required
                    value={form.end}
                    onChange={e => setForm({ ...form, end: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Reason for Leave</label>
                <textarea
                  rows={3}
                  required
                  placeholder="State reason..."
                  value={form.reason}
                  onChange={e => setForm({ ...form, reason: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">
                  Submit Application
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
