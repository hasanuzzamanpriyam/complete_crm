import React, { useState } from 'react'
import { Package, Search, Plus, Filter, DollarSign, Tag, MoreVertical, X } from 'lucide-react'

export default function ItemsPage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [items, setItems] = useState([
    { id: 'ITM-001', name: 'Enterprise Cloud Server (Monthly Node)', category: 'Cloud Infrastructure', sku: 'SRV-CLOUD-16C', salePrice: 240, costPrice: 160, stock: 45, unit: 'Node' },
    { id: 'ITM-002', name: 'TimeSync Pro Annual Desktop License', category: 'Software Licenses', sku: 'LIC-TSYNC-YR', salePrice: 120, costPrice: 0, stock: 999, unit: 'Seat' },
    { id: 'ITM-003', name: 'Custom AI Voice API Pack (10k Mins)', category: 'AI Services', sku: 'API-VOICE-10K', salePrice: 450, costPrice: 280, stock: 120, unit: 'Package' },
    { id: 'ITM-004', name: 'Cisco Gigabit Managed Switch 24-Port', category: 'Hardware Equipment', sku: 'HW-SW-24P-CS', salePrice: 680, costPrice: 490, stock: 8, unit: 'Piece' },
  ])

  const [showModal, setShowModal] = useState(false)
  const [form, setForm] = useState({ name: '', category: 'Software Licenses', sku: '', salePrice: '', costPrice: '', stock: '' })

  const filtered = items.filter(i =>
    i.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    i.sku.toLowerCase().includes(searchQuery.toLowerCase())
  )

  const handleCreate = (e) => {
    e.preventDefault()
    setItems([{
      id: `ITM-00${items.length + 1}`,
      name: form.name,
      category: form.category,
      sku: form.sku || `SKU-${Date.now().toString().slice(-4)}`,
      salePrice: parseFloat(form.salePrice) || 0,
      costPrice: parseFloat(form.costPrice) || 0,
      stock: parseInt(form.stock) || 0,
      unit: 'Unit',
    }, ...items])
    setShowModal(false)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Package className="w-6 h-6 text-indigo-400" />
            <span>Items & Products Catalog</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Maintain inventory stock, pricing tiers, unit margins, and hardware equipment catalog.
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2"
        >
          <Plus className="w-4 h-4" />
          <span>Add Catalog Item</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search items, SKU, category..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
          />
        </div>
      </div>

      <div className="rounded-xl bg-slate-900/70 border border-slate-800/80 overflow-hidden">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold bg-slate-800/30">
              <th className="py-3.5 px-4">Item & SKU</th>
              <th className="py-3.5 px-4">Category</th>
              <th className="py-3.5 px-4">Sale Price</th>
              <th className="py-3.5 px-4">Cost Price</th>
              <th className="py-3.5 px-4">Profit Margin</th>
              <th className="py-3.5 px-4">Stock Level</th>
              <th className="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filtered.map(i => {
              const margin = i.salePrice ? (((i.salePrice - i.costPrice) / i.salePrice) * 100).toFixed(0) : '0'
              return (
                <tr key={i.id} className="hover:bg-slate-800/40">
                  <td className="py-3.5 px-4">
                    <p className="font-semibold text-white">{i.name}</p>
                    <span className="text-[10px] text-indigo-400 font-mono">{i.sku}</span>
                  </td>
                  <td className="py-3.5 px-4 text-slate-300">{i.category}</td>
                  <td className="py-3.5 px-4 font-bold text-white">${i.salePrice}</td>
                  <td className="py-3.5 px-4 text-slate-400">${i.costPrice}</td>
                  <td className="py-3.5 px-4 font-semibold text-emerald-400">+{margin}%</td>
                  <td className="py-3.5 px-4">
                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                      i.stock < 10 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-slate-800 text-slate-200 border border-slate-700'
                    }`}>
                      {i.stock} {i.unit}s
                    </span>
                  </td>
                  <td className="py-3.5 px-4 text-right">
                    <button className="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-white">
                      <MoreVertical className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-base font-bold text-white">Add Item to Catalog</h2>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleCreate} className="space-y-4 text-xs">
              <div>
                <label className="block text-slate-300 font-medium mb-1">Item Title</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Dedicated Server Hosting"
                  value={form.name}
                  onChange={e => setForm({ ...form, name: e.target.value })}
                  className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">SKU Code</label>
                  <input
                    type="text"
                    placeholder="SRV-01"
                    value={form.sku}
                    onChange={e => setForm({ ...form, sku: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Category</label>
                  <input
                    type="text"
                    value={form.category}
                    onChange={e => setForm({ ...form, category: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Sale Price ($)</label>
                  <input
                    type="number"
                    required
                    value={form.salePrice}
                    onChange={e => setForm({ ...form, salePrice: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
                <div>
                  <label className="block text-slate-300 font-medium mb-1">Stock Count</label>
                  <input
                    type="number"
                    value={form.stock}
                    onChange={e => setForm({ ...form, stock: e.target.value })}
                    className="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-200"
                  />
                </div>
              </div>
              <div className="pt-2 flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 rounded-lg bg-slate-800 text-slate-300">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">
                  Save Item
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
