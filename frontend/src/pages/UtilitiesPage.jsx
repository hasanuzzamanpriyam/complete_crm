import React, { useState } from 'react'
import { Gift, Shield, Plus, Calendar, Target, CheckCircle2 } from 'lucide-react'

export default function UtilitiesPage() {
  const [activeTab, setActiveTab] = useState('holidays')

  const holidays = [
    { name: 'New Year Day', date: '2026-01-01', day: 'Thursday', type: 'Public Holiday' },
    { name: 'International Mother Language Day', date: '2026-02-21', day: 'Saturday', type: 'National Holiday' },
    { name: 'Independence Day', date: '2026-03-26', day: 'Thursday', type: 'National Holiday' },
    { name: 'Eid-ul-Fitr Vacations', date: '2026-03-20 to 2026-03-24', day: 'Friday - Tuesday', type: 'Religious Holiday' },
    { name: 'Victory Day', date: '2026-12-16', day: 'Wednesday', type: 'National Holiday' },
  ]

  const goals = [
    { title: 'Q3 Enterprise Revenue Target', target: '$300,000', achieved: '$244,500', progress: 81.5, owner: 'Marcus Vance' },
    { title: 'New Customer Signups', target: '25 Corporate Clients', achieved: '21 Clients', progress: 84.0, owner: 'Sales Team' },
    { title: 'TimeSync Pro Desktop Adoption', target: '100 Active Seats', achieved: '85 Active Seats', progress: 85.0, owner: 'Product Team' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Gift className="w-6 h-6 text-indigo-400" />
            <span>Utilities & Organizational Goals</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Maintain public and corporate holiday calendars, track quarterly executive goals and achievements.
          </p>
        </div>
      </div>

      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold">
        <button
          onClick={() => setActiveTab('holidays')}
          className={`pb-3 transition-colors ${activeTab === 'holidays' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Holiday Calendar (2026)
        </button>
        <button
          onClick={() => setActiveTab('goals')}
          className={`pb-3 transition-colors ${activeTab === 'goals' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Goal & Target Tracking
        </button>
      </div>

      {activeTab === 'holidays' && (
        <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                <th className="py-3.5 px-4">Holiday Name</th>
                <th className="py-3.5 px-4">Date</th>
                <th className="py-3.5 px-4">Day</th>
                <th className="py-3.5 px-4">Classification</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {holidays.map((h, i) => (
                <tr key={i} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-semibold text-white">{h.name}</td>
                  <td className="py-3.5 px-4 font-mono text-indigo-400">{h.date}</td>
                  <td className="py-3.5 px-4 text-slate-300">{h.day}</td>
                  <td className="py-3.5 px-4">
                    <span className="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                      {h.type}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeTab === 'goals' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {goals.map((g, i) => (
            <div key={i} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs text-slate-400">Lead: {g.owner}</span>
                  <span className="text-xs font-bold text-emerald-400">{g.progress}% Hit</span>
                </div>
                <h3 className="text-base font-bold text-white">{g.title}</h3>
                <div className="mt-4 space-y-2 text-xs">
                  <div className="flex justify-between text-slate-300">
                    <span>Achieved:</span>
                    <strong className="text-emerald-400">{g.achieved}</strong>
                  </div>
                  <div className="flex justify-between text-slate-400">
                    <span>Target:</span>
                    <strong className="text-white">{g.target}</strong>
                  </div>
                  <div className="w-full h-2 bg-slate-800 rounded-full overflow-hidden mt-2">
                    <div className="bg-emerald-500 h-full rounded-full" style={{ width: `${g.progress}%` }} />
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
