import React, { useState } from 'react'
import { DollarSign, FileText, Plus, Search, Calendar, UserCheck, CheckCircle2, Download, Printer } from 'lucide-react'

export default function PayrollPage() {
  const [activeTab, setActiveTab] = useState('summary')

  const payrolls = [
    { id: 1, staff: 'Marcus Vance', role: 'Lead Architect', basic: 5500, allowances: 800, deductions: 200, net: 6100, month: 'September 2026', status: 'Paid' },
    { id: 2, staff: 'Elena Rostova', role: 'Product Manager', basic: 4800, allowances: 600, deductions: 150, net: 5250, month: 'September 2026', status: 'Paid' },
    { id: 3, staff: 'Sarah Jenkins', role: 'Senior Accountant', basic: 3800, allowances: 400, deductions: 120, net: 4080, month: 'September 2026', status: 'Pending' },
    { id: 4, staff: 'Michael Scott', role: 'Full Stack Dev', basic: 4200, allowances: 500, deductions: 140, net: 4560, month: 'September 2026', status: 'Pending' },
  ]

  const templates = [
    { name: 'Executive Engineering Tier', basicSalary: '$5,000 - $7,000', providentFund: '8%', taxSlab: '10%', overtimeRate: '$35/hr' },
    { name: 'Standard Staff Tier', basicSalary: '$3,500 - $4,800', providentFund: '8%', taxSlab: '7.5%', overtimeRate: '$25/hr' },
    { name: 'Internship & Trainee Tier', basicSalary: '$1,200 - $1,800', providentFund: '0%', taxSlab: '0%', overtimeRate: '$15/hr' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <DollarSign className="w-6 h-6 text-emerald-400" />
            <span>Payroll & Salary Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Automate monthly salary slips, tax withholdings, provident fund deductions, and salary disbursal.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
            <CheckCircle2 className="w-4 h-4" />
            <span>Process Bulk Payroll</span>
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-slate-800 gap-6 text-xs font-semibold overflow-x-auto pb-1">
        <button
          onClick={() => setActiveTab('summary')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'summary' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Payroll Summary & Slips
        </button>
        <button
          onClick={() => setActiveTab('templates')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'templates' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Salary Templates
        </button>
        <button
          onClick={() => setActiveTab('disbursal')}
          className={`pb-3 whitespace-nowrap transition-colors ${activeTab === 'disbursal' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-200'}`}
        >
          Make Payment (Direct Bank)
        </button>
      </div>

      {activeTab === 'summary' && (
        <div className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
              <span className="text-xs text-slate-400">Total Monthly Payroll</span>
              <p className="text-2xl font-bold text-white mt-1">$19,990.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
              <span className="text-xs text-slate-400">Disbursed (Paid)</span>
              <p className="text-2xl font-bold text-emerald-400 mt-1">$11,350.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
              <span className="text-xs text-slate-400">Pending Authorization</span>
              <p className="text-2xl font-bold text-amber-400 mt-1">$8,640.00</p>
            </div>
            <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800">
              <span className="text-xs text-slate-400">Tax Withholding Pool</span>
              <p className="text-2xl font-bold text-indigo-400 mt-1">$1,840.00</p>
            </div>
          </div>

          <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
                  <th className="py-3.5 px-4">Staff Member</th>
                  <th className="py-3.5 px-4">Role</th>
                  <th className="py-3.5 px-4">Basic Pay</th>
                  <th className="py-3.5 px-4">Allowances</th>
                  <th className="py-3.5 px-4">Deductions</th>
                  <th className="py-3.5 px-4">Net Payable</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4 text-right">Payslip</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {payrolls.map(p => (
                  <tr key={p.id} className="hover:bg-slate-800/40">
                    <td className="py-3.5 px-4 font-semibold text-slate-200">{p.staff}</td>
                    <td className="py-3.5 px-4 text-slate-400">{p.role}</td>
                    <td className="py-3.5 px-4 text-slate-300">${p.basic}</td>
                    <td className="py-3.5 px-4 text-emerald-400">+${p.allowances}</td>
                    <td className="py-3.5 px-4 text-rose-400">-${p.deductions}</td>
                    <td className="py-3.5 px-4 font-bold text-white">${p.net}</td>
                    <td className="py-3.5 px-4">
                      <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                        p.status === 'Paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                      }`}>
                        {p.status}
                      </span>
                    </td>
                    <td className="py-3.5 px-4 text-right">
                      <button className="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium inline-flex items-center gap-1">
                        <Download className="w-3 h-3" />
                        <span>PDF</span>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {activeTab === 'templates' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {templates.map((tpl, i) => (
            <div key={i} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800">
              <h3 className="text-base font-bold text-white mb-2">{tpl.name}</h3>
              <p className="text-xl font-bold text-indigo-400 mb-4">{tpl.basicSalary}</p>
              <div className="space-y-2 text-xs border-t border-slate-800 pt-3 text-slate-300">
                <div className="flex justify-between"><span>Provident Fund:</span><strong className="text-white">{tpl.providentFund}</strong></div>
                <div className="flex justify-between"><span>Withholding Tax:</span><strong className="text-white">{tpl.taxSlab}</strong></div>
                <div className="flex justify-between"><span>Overtime Rate:</span><strong className="text-white">{tpl.overtimeRate}</strong></div>
              </div>
            </div>
          ))}
        </div>
      )}

      {activeTab === 'disbursal' && (
        <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 max-w-xl">
          <h3 className="text-base font-bold text-white mb-2">Automated Direct Bank Disbursal</h3>
          <p className="text-xs text-slate-400 mb-4">Transfer salaries directly to staff accounts via City Bank Payroll API.</p>
          <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700 space-y-3 text-xs mb-4">
            <div className="flex justify-between text-slate-300"><span>Disbursal Batch:</span><strong className="text-white">Batch #2026-09-SEP</strong></div>
            <div className="flex justify-between text-slate-300"><span>Total Accounts:</span><strong className="text-white">31 Active Employees</strong></div>
            <div className="flex justify-between text-slate-300"><span>Total Disbursal Amount:</span><strong className="text-emerald-400 font-bold">$19,990.00</strong></div>
          </div>
          <button className="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-semibold text-white text-xs shadow-lg shadow-emerald-600/30">
            Execute Disbursal
          </button>
        </div>
      )}
    </div>
  )
}
