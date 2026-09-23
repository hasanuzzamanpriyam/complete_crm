import React, { useState } from 'react'
import { Folder, File, Upload, Plus, Download, Trash2, Search, FileText, Image, HardDrive } from 'lucide-react'

export default function FileManagerPage() {
  const [currentFolder, setCurrentFolder] = useState('Root')

  const files = [
    { name: 'Contracts & Agreements', type: 'folder', items: '12 items', size: '42 MB', modified: '2026-09-21' },
    { name: 'Invoices & Receipts PDF', type: 'folder', items: '48 items', size: '115 MB', modified: '2026-09-20' },
    { name: 'Client Project Assets', type: 'folder', items: '24 items', size: '340 MB', modified: '2026-09-19' },
    { name: 'TimeSync_v2.4_Setup.exe', type: 'file', ext: 'exe', size: '64.2 MB', modified: '2026-09-18' },
    { name: 'Master_Brand_Guidelines_2026.pdf', type: 'file', ext: 'pdf', size: '18.5 MB', modified: '2026-09-15' },
    { name: 'Company_Financial_Audit_Q2.xlsx', type: 'file', ext: 'xlsx', size: '4.2 MB', modified: '2026-09-10' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <Folder className="w-6 h-6 text-indigo-400" />
            <span>Enterprise Cloud File Manager</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Store documents, share deliverables with clients, and manage company digital assets.
          </p>
        </div>
        <div className="flex gap-2">
          <button className="px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>New Folder</span>
          </button>
          <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <Upload className="w-4 h-4" />
            <span>Upload Files</span>
          </button>
        </div>
      </div>

      {/* Storage quota pill */}
      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="p-2.5 rounded-lg bg-indigo-500/10 text-indigo-400">
            <HardDrive className="w-5 h-5" />
          </div>
          <div>
            <p className="text-xs font-semibold text-white">Storage Cloud Capacity</p>
            <p className="text-[11px] text-slate-400">583.9 MB of 100 GB Used (0.5%)</p>
          </div>
        </div>
        <span className="text-xs text-emerald-400 font-medium">99.5 GB Available</span>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {files.map((f, i) => (
          <div key={i} className="p-5 rounded-2xl bg-slate-900/70 border border-slate-800 hover:border-slate-700 transition-all flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className={`p-3 rounded-xl ${f.type === 'folder' ? 'bg-amber-500/10 text-amber-400' : 'bg-indigo-500/10 text-indigo-400'}`}>
                {f.type === 'folder' ? <Folder className="w-5 h-5" /> : <FileText className="w-5 h-5" />}
              </div>
              <div>
                <p className="font-semibold text-xs text-white max-w-[170px] truncate">{f.name}</p>
                <p className="text-[10px] text-slate-400 mt-0.5">{f.type === 'folder' ? f.items : f.size} • {f.modified}</p>
              </div>
            </div>
            <button className="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
              <Download className="w-4 h-4" />
            </button>
          </div>
        ))}
      </div>
    </div>
  )
}
