import React, { useState } from 'react'
import { GraduationCap, Plus, Search, Users, Calendar, Award, CheckCircle2 } from 'lucide-react'

export default function TrainingPage() {
  const [trainings, setTrainings] = useState([
    { id: 1, title: 'React 19 & Enterprise Architecture Workshop', trainer: 'Lead Architect Marcus', dept: 'Software Engineering', attendees: 14, start: '2026-10-01', end: '2026-10-05', cost: '$1,200', status: 'Upcoming' },
    { id: 2, title: 'Security Best Practices & OWASP Hardening', trainer: 'Cyber Defense Consultant', dept: 'All Technical Staff', attendees: 20, start: '2026-09-10', end: '2026-09-12', cost: '$800', status: 'Completed' },
    { id: 3, title: 'Corporate Customer Relations & De-escalation', trainer: 'Sarah Jenkins', dept: 'Support & Sales', attendees: 8, start: '2026-08-20', end: '2026-08-22', cost: '$400', status: 'Completed' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <GraduationCap className="w-6 h-6 text-indigo-400" />
            <span>Staff Training & Skill Development</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Conduct internal corporate training programs, track staff certifications, and skill upgrades.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Training Session</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {trainings.map(t => (
          <div key={t.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                  {t.dept}
                </span>
                <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                  t.status === 'Upcoming' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700'
                }`}>
                  {t.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{t.title}</h3>
              <p className="text-xs text-slate-400 mt-1">Conducted by: <strong className="text-slate-200">{t.trainer}</strong></p>

              <div className="space-y-1.5 mt-4 pt-3 border-t border-slate-800 text-xs text-slate-300">
                <div className="flex items-center justify-between"><span>Duration:</span><strong className="text-white">{t.start} to {t.end}</strong></div>
                <div className="flex items-center justify-between"><span>Trainees Enrolled:</span><strong className="text-white">{t.attendees} Members</strong></div>
                <div className="flex items-center justify-between"><span>Training Budget:</span><strong className="text-emerald-400">{t.cost}</strong></div>
              </div>
            </div>

            <div className="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
              <span className="text-emerald-400 font-medium flex items-center gap-1"><Award className="w-3.5 h-3.5" /> Certificate Eligible</span>
              <button className="text-indigo-400 hover:text-indigo-300 font-semibold">Roster →</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
