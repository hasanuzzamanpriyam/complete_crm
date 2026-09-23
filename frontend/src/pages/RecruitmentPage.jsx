import React, { useState } from 'react'
import { Briefcase, Plus, Search, Users, Calendar, MapPin, CheckCircle2 } from 'lucide-react'

export default function RecruitmentPage() {
  const [jobs, setJobs] = useState([
    { id: 1, title: 'Senior React / Full Stack Engineer', dept: 'Software Engineering', applicants: 34, type: 'Full-time', location: 'Dhaka (Hybrid)', status: 'Active' },
    { id: 2, title: 'Enterprise Account Executive', dept: 'Sales', applicants: 18, type: 'Full-time', location: 'Dhaka', status: 'Active' },
    { id: 3, title: 'DevOps & Cloud Security Specialist', dept: 'Infrastructure', applicants: 12, type: 'Full-time', location: 'Remote', status: 'Active' },
    { id: 4, title: 'Product UI/UX Designer', dept: 'Product & Design', applicants: 45, type: 'Contract', location: 'Remote', status: 'Closed' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Briefcase className="w-6 h-6 text-indigo-400" />
            <span>Recruitment & Job Openings</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Post career circulars, track applicant resumes, schedule candidate interviews, and issue job offers.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Post Job Circular</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {jobs.map(j => (
          <div key={j.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-semibold text-indigo-400">{j.dept}</span>
                <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                  j.status === 'Active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700'
                }`}>
                  {j.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{j.title}</h3>
              <div className="flex items-center gap-4 text-xs text-slate-400 mt-2">
                <span className="flex items-center gap-1"><MapPin className="w-3.5 h-3.5 text-slate-500" />{j.location}</span>
                <span>•</span>
                <span>{j.type}</span>
              </div>
            </div>

            <div className="mt-5 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
              <span className="text-slate-300 font-medium flex items-center gap-1.5">
                <Users className="w-3.5 h-3.5 text-indigo-400" />
                <span>{j.applicants} Applicants Received</span>
              </span>
              <button className="text-indigo-400 hover:text-indigo-300 font-semibold">View Pipeline →</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
