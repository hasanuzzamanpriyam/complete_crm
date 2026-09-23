import React, { useState } from 'react'
import { Award, Plus, Search, Star, TrendingUp, UserCheck } from 'lucide-react'

export default function PerformancePage() {
  const [reviews, setReviews] = useState([
    { id: 1, staff: 'Marcus Vance', role: 'Lead Architect', score: '4.9 / 5.0', kpiCompleted: '96%', reviewPeriod: 'Q3 2026', reviewer: 'Forid Admin', status: 'Exemplary' },
    { id: 2, staff: 'Elena Rostova', role: 'Product Manager', score: '4.8 / 5.0', kpiCompleted: '94%', reviewPeriod: 'Q3 2026', reviewer: 'Forid Admin', status: 'Exemplary' },
    { id: 3, staff: 'Michael Scott', role: 'Full Stack Dev', score: '4.5 / 5.0', kpiCompleted: '88%', reviewPeriod: 'Q3 2026', reviewer: 'Marcus Vance', status: 'Achieved' },
    { id: 4, staff: 'Sarah Jenkins', role: 'Senior Accountant', score: '4.7 / 5.0', kpiCompleted: '92%', reviewPeriod: 'Q3 2026', reviewer: 'Forid Admin', status: 'Achieved' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Award className="w-6 h-6 text-amber-400" />
            <span>Performance & Appraisals</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Conduct 360-degree staff appraisals, track quarterly OKR/KPI completions, and manage promotion scorecards.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-amber-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Appraisal</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {reviews.map(r => (
          <div key={r.id} className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2">
                <span className="text-xs text-slate-400">{r.reviewPeriod}</span>
                <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                  {r.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{r.staff}</h3>
              <p className="text-xs text-slate-400">{r.role}</p>

              <div className="my-4 p-3 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between">
                <div>
                  <span className="text-[10px] text-slate-400">Score Rating</span>
                  <div className="flex items-center gap-1 text-amber-400 font-bold text-sm mt-0.5">
                    <Star className="w-3.5 h-3.5 fill-amber-400" />
                    <span>{r.score}</span>
                  </div>
                </div>
                <div className="text-right">
                  <span className="text-[10px] text-slate-400">KPI Goal Hit</span>
                  <p className="text-sm font-bold text-emerald-400 mt-0.5">{r.kpiCompleted}</p>
                </div>
              </div>
            </div>

            <div className="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
              <span>Reviewed by {r.reviewer}</span>
              <button className="text-indigo-400 hover:text-indigo-300 font-medium">Details</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
