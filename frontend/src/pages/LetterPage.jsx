import React, { useState } from 'react'
import { Mail, Plus, Search, FileText, Download, Printer } from 'lucide-react'

export default function LetterPage() {
  const [templates, setTemplates] = useState([
    { id: 1, title: 'Job Offer & Appointment Letter', category: 'Recruitment', variables: '[CANDIDATE_NAME], [SALARY], [JOIN_DATE], [DESIGNATION]' },
    { id: 2, title: 'Experience & Release Certificate', category: 'Offboarding', variables: '[EMPLOYEE_NAME], [TENURE], [DESIGNATION], [CONDUCT]' },
    { id: 3, title: 'Official Salary Certificate & NOC', category: 'Banking & Visa', variables: '[EMPLOYEE_NAME], [GROSS_SALARY], [JOIN_DATE], [PASSPORT_NO]' },
    { id: 4, title: 'Formal Promotion & Increment Letter', category: 'HR Lifecycle', variables: '[EMPLOYEE_NAME], [NEW_ROLE], [NEW_SALARY], [EFFECTIVE_DATE]' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Mail className="w-6 h-6 text-indigo-400" />
            <span>Corporate Letters & Certificate Generation</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Generate formal appointment letters, internship certificates, salary proof, and release letters.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Letter Template</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {templates.map(t => (
          <div key={t.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <span className="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                {t.category}
              </span>
              <h3 className="text-base font-bold text-white mt-3">{t.title}</h3>
              <p className="text-xs text-slate-400 mt-2 font-mono text-[11px]">{t.variables}</p>
            </div>

            <div className="mt-5 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
              <span className="text-slate-400">PDF Ready</span>
              <button className="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 font-medium flex items-center gap-1.5 transition-colors">
                <Printer className="w-3.5 h-3.5" />
                <span>Generate Letter</span>
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
