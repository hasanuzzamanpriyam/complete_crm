import React, { useState } from 'react'
import { ShoppingBag, Search, Plus, Filter, Calendar, CheckCircle2, Clock } from 'lucide-react'

export default function PurchasesPage() {
  const [purchases, setPurchases] = useState([
    { id: 'PO-2026-051', supplier: 'Dell Enterprise Global', items: 'PowerEdge R750 Server Nodes', amount: 18400, date: '2026-09-18', status: 'Received', payment: 'Paid' },
    { id: 'PO-2026-050', supplier: 'Cisco Systems Inc', items: '24-Port Gigabit Switches (Qty: 10)', amount: 6800, date: '2026-09-12', status: 'In Transit', payment: 'Partially Paid' },
    { id: 'PO-2026-049', supplier: 'Amazon Web Services', items: 'Reserved Cloud Compute Quota', amount: 14200, date: '2026-09-01', status: 'Received', payment: 'Paid' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <ShoppingBag className="w-6 h-6 text-indigo-400" />
            <span>Suppliers & Purchase Orders (PO)</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Procure hardware equipment, track supplier shipments, and manage accounts payable invoices.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>New Purchase Order</span>
        </button>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">PO Number</th>
              <th className="py-3.5 px-4">Supplier</th>
              <th className="py-3.5 px-4">Items / Description</th>
              <th className="py-3.5 px-4">Order Value</th>
              <th className="py-3.5 px-4">Order Date</th>
              <th className="py-3.5 px-4">Receiving Status</th>
              <th className="py-3.5 px-4">Payment</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {purchases.map(p => (
              <tr key={p.id} className="hover:bg-slate-800/40">
                <td className="py-3.5 px-4 font-mono font-semibold text-indigo-400">{p.id}</td>
                <td className="py-3.5 px-4 font-semibold text-white">{p.supplier}</td>
                <td className="py-3.5 px-4 text-slate-300">{p.items}</td>
                <td className="py-3.5 px-4 font-bold text-white">${p.amount.toLocaleString()}</td>
                <td className="py-3.5 px-4 text-slate-400">{p.date}</td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    p.status === 'Received' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                  }`}>
                    {p.status}
                  </span>
                </td>
                <td className="py-3.5 px-4">
                  <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                    p.payment === 'Paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
                  }`}>
                    {p.payment}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
