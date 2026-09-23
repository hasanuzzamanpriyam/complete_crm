import React, { useState } from 'react'
import { ShoppingBag, RefreshCw, Plus, CheckCircle, ExternalLink, Package, DollarSign } from 'lucide-react'

export default function WooCommercePage() {
  const [stores, setStores] = useState([
    { id: 1, name: 'Apex Fashion Outlet Store', url: 'https://shop.apexretail.com', status: 'Connected', ordersSync: 1420, revenue: '$89,400', lastSync: '10 mins ago' },
    { id: 2, name: 'CloudGear Tech Accessories', url: 'https://store.cloudwave.io', status: 'Connected', ordersSync: 650, revenue: '$34,120', lastSync: '1 hour ago' },
  ])

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <ShoppingBag className="w-6 h-6 text-pink-400" />
            <span>WooCommerce Store Integrations</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Synchronize orders, products, and customer records from connected WordPress / WooCommerce stores.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-3.5 py-2 rounded-xl bg-slate-800 text-xs font-semibold text-slate-200 border border-slate-700 flex items-center gap-2">
            <RefreshCw className="w-3.5 h-3.5" />
            <span>Sync All Stores</span>
          </button>
          <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>Connect Store</span>
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {stores.map(s => (
          <div key={s.id} className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur">
            <div className="flex items-center justify-between mb-3">
              <span className="font-bold text-base text-white">{s.name}</span>
              <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                {s.status}
              </span>
            </div>
            <a href={s.url} target="_blank" rel="noreferrer" className="text-xs text-indigo-400 hover:underline flex items-center gap-1 mb-4">
              <span>{s.url}</span>
              <ExternalLink className="w-3 h-3" />
            </a>

            <div className="grid grid-cols-2 gap-3 pt-4 border-t border-slate-800/80">
              <div>
                <span className="text-[11px] text-slate-400">Total Synced Orders</span>
                <p className="text-lg font-bold text-white mt-0.5">{s.ordersSync.toLocaleString()}</p>
              </div>
              <div>
                <span className="text-[11px] text-slate-400">Synced GMV Revenue</span>
                <p className="text-lg font-bold text-emerald-400 mt-0.5">{s.revenue}</p>
              </div>
            </div>

            <div className="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
              <span>Last sync: {s.lastSync}</span>
              <button className="text-indigo-400 hover:text-indigo-300 font-medium">Manage Sync</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
