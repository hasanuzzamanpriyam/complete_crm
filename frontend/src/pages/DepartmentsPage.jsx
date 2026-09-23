import React, { useState } from 'react'
import { Users, Plus, Search, Building2, UserCheck, MoreVertical, X } from 'lucide-react'

export default function DepartmentsPage() {
  const [departments, setDepartments] = useState([
    { id: 1, name: 'Software Engineering', head: 'Forid Admin', staffCount: 14, designations: ['Lead Architect', 'Full Stack Dev', 'QA Engineer', 'DevOps'] },
    { id: 2, name: 'Product & Design', head: 'Elena Rostova', staffCount: 5, designations: ['Product Manager', 'UI/UX Designer'] },
    { id: 3, name: 'Finance & Accounts', head: 'Sarah Jenkins', staffCount: 4, designations: ['Senior Accountant', 'Billing Executive'] },
    { id: 4, name: 'Sales & Customer Success', head: 'Marcus Vance', staffCount: 6, designations: ['Account Executive', 'SDR', 'Support Specialist'] },
    { id: 5, name: 'Human Resources (HR)', head: 'Chloe Bennett', staffCount: 2, designations: ['HR Manager', 'Talent Acquisition'] },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Building2 className="w-6 h-6 text-indigo-400" />
            <span>Departments & Designations</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Organize company hierarchy, department heads, staff assignments, and corporate job designations.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Department</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {departments.map(d => (
          <div key={d.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                  {d.staffCount} Staff Members
                </span>
                <button className="text-slate-400 hover:text-white">
                  <MoreVertical className="w-4 h-4" />
                </button>
              </div>
              <h3 className="text-base font-bold text-white">{d.name}</h3>
              <p className="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                <UserCheck className="w-3.5 h-3.5 text-emerald-400" />
                <span>Head: <strong className="text-slate-200">{d.head}</strong></span>
              </p>
            </div>

            <div className="mt-5 pt-4 border-t border-slate-800">
              <span className="text-[11px] text-slate-400 font-medium">Designations:</span>
              <div className="flex flex-wrap gap-1.5 mt-2">
                {d.designations.map((des, i) => (
                  <span key={i} className="px-2 py-0.5 rounded-md bg-slate-800 text-[10px] text-slate-300 border border-slate-700">
                    {des}
                  </span>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
