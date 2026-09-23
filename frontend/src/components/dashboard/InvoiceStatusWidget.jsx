import React from 'react'
import { Link } from 'react-router-dom'
import { Receipt, ArrowRight, AlertCircle, CheckCircle2, Clock } from 'lucide-react'

export default function InvoiceStatusWidget() {
  const invoices = [
    {
      id: 'INV-2026-089',
      client: 'Skyline Logistics LLC',
      amount: '$4,800.00',
      status: 'Paid',
      color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      dueDate: 'Sep 18, 2026',
    },
    {
      id: 'INV-2026-092',
      client: 'Vanguard Health Tech',
      amount: '$7,250.00',
      status: 'Due',
      color: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
      dueDate: 'Oct 05, 2026',
    },
    {
      id: 'INV-2026-078',
      client: 'Omni Media Global',
      amount: '$2,100.00',
      status: 'Overdue',
      color: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
      dueDate: 'Sep 12, 2026',
    },
    {
      id: 'INV-2026-095',
      client: 'BlueHorizon Consulting',
      amount: '$3,400.00',
      status: 'Partially Paid',
      color: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
      dueDate: 'Oct 10, 2026',
    },
  ]

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <Receipt className="w-4 h-4 text-emerald-400" />
            <span>Recent Invoicing & Receivables</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Billing status and outstanding accounts
          </p>
        </div>
        <Link
          to="/invoices"
          className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors"
        >
          <span>Manage</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </Link>
      </div>

      <div className="divide-y divide-slate-800/60">
        {invoices.map((inv) => (
          <div
            key={inv.id}
            className="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3"
          >
            <div className="flex items-center gap-3">
              <div className="p-2 rounded-xl bg-slate-800/80 border border-slate-700/40 text-slate-300">
                <Receipt className="w-4 h-4 text-indigo-400" />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <span className="text-xs font-bold text-white font-mono">
                    {inv.id}
                  </span>
                  <span
                    className={`text-[9px] font-bold px-1.5 py-0.2 rounded border ${inv.color}`}
                  >
                    {inv.status}
                  </span>
                </div>
                <p className="text-[11px] text-slate-400">{inv.client}</p>
              </div>
            </div>

            <div className="text-right">
              <p className="text-xs font-bold text-white">{inv.amount}</p>
              <p className="text-[10px] text-slate-400 flex items-center justify-end gap-1">
                <Clock className="w-2.5 h-2.5 text-slate-400" /> {inv.dueDate}
              </p>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
