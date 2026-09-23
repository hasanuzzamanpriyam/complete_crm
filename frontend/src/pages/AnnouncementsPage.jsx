import React, { useState } from 'react'
import { Bell, Plus, Calendar, User, AlertCircle, Sparkles, CheckCircle2 } from 'lucide-react'

export default function AnnouncementsPage() {
  const [showModal, setShowModal] = useState(false)
  const [announcements, setAnnouncements] = useState([
    {
      id: 1,
      title: 'Q3 General Strategy & All-Hands Conference',
      date: 'Sep 24, 2026',
      author: 'Forid Mondol (Admin)',
      priority: 'High',
      badgeColor: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
      content:
        'All staff members are invited to join the Q3 performance and roadmap review. The meeting link will be broadcast via Zoom 15 minutes before the session starts.',
    },
    {
      id: 2,
      title: 'TimeSync Desktop Tracker Update v2.4 Mandatory Upgrade',
      date: 'Sep 22, 2026',
      author: 'IT Infrastructure',
      priority: 'Important',
      badgeColor: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
      content:
        'Please ensure you download the latest build from the top banner to support improved background telemetry and automated biometric sync.',
    },
    {
      id: 3,
      title: 'Official Holiday Announcement: Autumn Festival',
      date: 'Sep 15, 2026',
      author: 'Human Resources',
      priority: 'Normal',
      badgeColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      content:
        'The corporate offices will be closed on Friday, October 2nd. Emergency on-call support will remain active for critical server management.',
    },
  ])

  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')
  const [priority, setPriority] = useState('Normal')

  const handleCreate = (e) => {
    e.preventDefault()
    if (!title) return
    const newA = {
      id: Date.now(),
      title,
      content,
      priority,
      author: 'Forid Mondol',
      date: 'Today',
      badgeColor:
        priority === 'High'
          ? 'bg-rose-500/10 text-rose-400 border-rose-500/20'
          : priority === 'Important'
          ? 'bg-amber-500/10 text-amber-400 border-amber-500/20'
          : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    }
    setAnnouncements([newA, ...announcements])
    setTitle('')
    setContent('')
    setShowModal(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Bell className="w-5 h-5 text-indigo-400" />
            <span>Company Announcements</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Broadcast updates, corporate notices, and operational announcements
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>New Notice</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {announcements.map((a) => (
          <div
            key={a.id}
            className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-3 hover:border-slate-700 transition-all"
          >
            <div className="flex items-start justify-between gap-3">
              <h2 className="text-sm sm:text-base font-bold text-white leading-snug">
                {a.title}
              </h2>
              <span
                className={`text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0 ${a.badgeColor}`}
              >
                {a.priority}
              </span>
            </div>

            <p className="text-xs sm:text-sm text-slate-300 leading-relaxed">
              {a.content}
            </p>

            <div className="pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
              <span className="flex items-center gap-1.5">
                <User className="w-3 h-3 text-slate-400" /> {a.author}
              </span>
              <span className="flex items-center gap-1.5">
                <Calendar className="w-3 h-3 text-slate-400" /> {a.date}
              </span>
            </div>
          </div>
        ))}
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Create Announcement</h3>
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
                  Title
                </label>
                <input
                  type="text"
                  required
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="Notice subject..."
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Priority
                </label>
                <select
                  value={priority}
                  onChange={(e) => setPriority(e.target.value)}
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="Normal">Normal</option>
                  <option value="Important">Important</option>
                  <option value="High">High</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Content
                </label>
                <textarea
                  rows={4}
                  required
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  placeholder="Full announcement body..."
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
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
                  Post Notice
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
