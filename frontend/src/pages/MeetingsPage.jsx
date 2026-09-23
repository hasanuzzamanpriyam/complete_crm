import React, { useState } from 'react'
import { Video, Plus, Search, Calendar, Users, Clock, ExternalLink, ShieldCheck } from 'lucide-react'

export default function MeetingsPage() {
  const [meetings, setMeetings] = useState([
    { id: 1, title: 'Weekly Engineering & Sprint Planning', host: 'Forid Admin', date: 'Today, 03:00 PM', duration: '45 mins', platform: 'Zoom Pro', roomUrl: 'https://zoom.us/j/8492049182', attendees: 8, status: 'Upcoming' },
    { id: 2, title: 'Client Sync: Apex Global ERP Milestones', host: 'Elena Rostova', date: 'Tomorrow, 11:30 AM', duration: '30 mins', platform: 'Google Meet', roomUrl: 'https://meet.google.com/abc-defg-hij', attendees: 5, status: 'Scheduled' },
    { id: 3, title: 'Executive Finance & Cashflow Review', host: 'Sarah Jenkins', date: 'Sep 25, 04:00 PM', duration: '60 mins', platform: 'Zoom Pro', roomUrl: 'https://zoom.us/j/9928341029', attendees: 4, status: 'Scheduled' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Video className="w-6 h-6 text-indigo-400" />
            <span>Video Meetings & Conferencing</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Schedule Zoom and Google Meet rooms, invite team members and clients, and launch instant conferences.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Schedule Meeting</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {meetings.map(m => (
          <div key={m.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                  {m.platform}
                </span>
                <span className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                  m.status === 'Upcoming' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-300'
                }`}>
                  {m.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{m.title}</h3>
              <p className="text-xs text-slate-400 mt-1">Host: {m.host}</p>

              <div className="space-y-1.5 mt-4 text-xs text-slate-300">
                <div className="flex items-center gap-2"><Calendar className="w-3.5 h-3.5 text-slate-500" /><span>{m.date}</span></div>
                <div className="flex items-center gap-2"><Clock className="w-3.5 h-3.5 text-slate-500" /><span>Duration: {m.duration}</span></div>
                <div className="flex items-center gap-2"><Users className="w-3.5 h-3.5 text-slate-500" /><span>{m.attendees} Confirmed Attendees</span></div>
              </div>
            </div>

            <div className="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
              <a
                href={m.roomUrl}
                target="_blank"
                rel="noreferrer"
                className="w-full py-2 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors"
              >
                <span>Join Conference</span>
                <ExternalLink className="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
