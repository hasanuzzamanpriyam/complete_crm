import React, { useState } from 'react'
import { CalendarCheck, Plus, Search, Calendar, User, Clock, CheckCircle2, MoreVertical, X } from 'lucide-react'

export default function ConsultationPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [consultations, setConsultations] = useState([
    { id: 1, client: 'Apex Global Retail', topic: 'Enterprise Cloud ERP Architectural Review', consultant: 'Forid Admin', date: '2026-09-24', time: '02:00 PM', duration: '60 mins', fee: '$250.00', status: 'Confirmed' },
    { id: 2, client: 'CloudWave Technologies', topic: 'High-Volume Payment Gateway Integration Audit', consultant: 'Marcus Vance', date: '2026-09-25', time: '11:00 AM', duration: '45 mins', fee: '$180.00', status: 'Scheduled' },
    { id: 3, client: 'Vanguard Health Tech', topic: 'HIPAA & Data Privacy Security Assessment', consultant: 'Forid Admin', date: '2026-09-28', time: '04:00 PM', duration: '90 mins', fee: '$350.00', status: 'Pending' },
  ])

  const [showModal, setShowModal] = useState(false)
  const [form, setForm] = useState({ client: '', topic: '', consultant: 'Forid Admin', date: '', time: '', fee: '200' })

  const handleCreate = (e) => {
    e.preventDefault()
    setConsultations([...consultations, {
      id: Date.now(),
      ...form,
      fee: `$${form.fee}.00`,
      duration: '60 mins',
      status: 'Confirmed'
    }])
    setShowModal(false)
  }

  const filtered = consultations.filter(c =>
    c.client.toLowerCase().includes(searchQuery.toLowerCase()) ||
    c.topic.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <CalendarCheck className="w-6 h-6 text-indigo-400" />
            <span>Consultation & Client Booking Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Schedule paid client advisory sessions, technical consulting calls, and expert consultations.
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          <span>Book Consultation</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search client, topic..."
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
              <th className="py-3.5 px-4">Client</th>
              <th className="py-3.5 px-4">Consultation Topic</th>
              <th className="py-3.5 px-4">Consultant</th>
              <th className="py-3.5 px-4">Schedule Date & Time</th>
              <th className="py-3.5 px-4">Fee</th>
              <th className="py-3.5 px-4">Status</th>
              <th className="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(c => (
              <tr key={c.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-semibold text-white">{c.client}</td>
                <td className="py-3.5 px-4 text-slate-200 max-w-xs">{c.topic}</td>
                <td className="py-3.5 px-4 text-slate-300">{c.consultant}</td>
                <td className="py-3.5 px-4 text-slate-400">
                  {c.date} at {c.time} ({c.duration})
                </td>
                <td className="py-3.5 px-4 font-bold text-emerald-400">{c.fee}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    c.status === 'Confirmed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
                  }`}>
                    {c.status}
                  </span>
                </td>
                <td className="py-3.5 px-4 text-right">
                  <button className="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-white">
                    <MoreVertical className="w-4 h-4" />
                  </button>
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
              <h2 className="text-base font-bold text-white">Book New Consultation</h2>
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
                  value={form.client}
                  onChange={e => setForm({ ...form, client: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div>
                <label className="block text-slate-300 font-medium mb-1">Discussion Subject</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Server Architecture Migration"
                  value={form.topic}
                  onChange={e => setForm({ ...form, topic: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Date</label>
                  <input
                    type="date"
                    required
                    value={form.date}
                    onChange={e => setForm({ ...form, date: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Time</label>
                  <input
                    type="time"
                    required
                    value={form.time}
                    onChange={e => setForm({ ...form, time: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">
                  Confirm Booking
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
