import React, { useState } from 'react'
import { Warehouse, Plus, Search, ArrowRightLeft, AlertCircle, CheckCircle2 } from 'lucide-react'

export default function StockPage() {
  const [warehouses, setWarehouses] = useState([
    { id: 1, name: 'Dhaka Central Server Datacenter', location: 'Mohakhali DOHS, Dhaka', capacity: '85%', itemsStored: 240, status: 'Optimal' },
    { id: 2, name: 'Chittagong Edge Relay Depot', location: 'Agrabad C/A, Chittagong', capacity: '42%', itemsStored: 95, status: 'Optimal' },
    { id: 3, name: 'HQ Hardware Spare Room', location: 'Level 4, Corporate HQ', capacity: '92%', itemsStored: 180, status: 'Near Capacity' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Warehouse className="w-6 h-6 text-indigo-400" />
            <span>Warehouses & Stock Depot Management</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Monitor storage capacities, hardware inventory in transit, and execute inter-depot stock transfers.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-2">
            <ArrowRightLeft className="w-4 h-4" />
            <span>Transfer Stock</span>
          </button>
          <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>Add Warehouse</span>
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {warehouses.map(w => (
          <div key={w.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-semibold text-slate-400">{w.itemsStored} Units Stored</span>
                <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                  w.status === 'Optimal' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                }`}>
                  {w.status}
                </span>
              </div>
              <h3 className="text-base font-bold text-white">{w.name}</h3>
              <p className="text-xs text-slate-400 mt-1">{w.location}</p>
            </div>

            <div className="mt-6 pt-4 border-t border-slate-800">
              <div className="flex justify-between text-xs mb-1">
                <span className="text-slate-400">Capacity Utilization</span>
                <span className="text-white font-bold">{w.capacity}</span>
              </div>
              <div className="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                <div className="h-full bg-indigo-500 rounded-full" style={{ width: w.capacity }} />
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
