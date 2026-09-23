import React, { useState } from 'react'
import { Percent, Plus, Search, MoreVertical, CheckCircle2 } from 'lucide-react'

export default function TaxRatesPage() {
  const [taxes, setTaxes] = useState([
    { id: 1, name: 'Standard VAT', rate: 15.0, type: 'Percentage', default: true },
    { id: 2, name: 'Reduced Goods Tax', rate: 5.0, type: 'Percentage', default: false },
    { id: 3, name: 'Digital Services Tax', rate: 7.5, type: 'Percentage', default: false },
    { id: 4, name: 'Zero-Rated Export', rate: 0.0, type: 'Fixed / Zero', default: false },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Percent className="w-6 h-6 text-indigo-400" />
            <span>Tax Rates Configuration</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Define corporate tax rules, VAT percentage, and local sales tax policies.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Add Tax Rate</span>
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {taxes.map(t => (
          <div key={t.id} className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-300">{t.name}</span>
              {t.default && (
                <span className="px-2 py-0.5 rounded text-[10px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-medium">
                  Default
                </span>
              )}
            </div>
            <div className="my-4">
              <span className="text-3xl font-extrabold text-white">{t.rate}%</span>
              <p className="text-[11px] text-slate-400 mt-0.5">{t.type}</p>
            </div>
            <div className="flex items-center justify-between pt-3 border-t border-slate-800 text-xs text-slate-400">
              <span>Applied to Invoices</span>
              <button className="text-indigo-400 hover:text-indigo-300">Edit</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
