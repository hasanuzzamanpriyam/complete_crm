import React, { useState } from 'react'
import { FileText, Search, Plus, BookOpen, ChevronRight, Eye, ThumbsUp, X } from 'lucide-react'

export default function KnowledgebasePage() {
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedArticle, setSelectedArticle] = useState(null)

  const articles = [
    { id: 1, title: 'How to install and pair TimeSync Desktop Tracker on Windows & macOS', category: 'TimeSync & Tracking', views: 842, likes: 64, date: '2026-08-15', content: 'Follow these steps to download the installer from /timesync, generate your personal auth token from Developer API, and paste the Bearer token into the application setup prompt.' },
    { id: 2, title: 'Integrating PipraPay Payment Gateway with Auto-Webhooks in CodeIgniter', category: 'Finance & Payments', views: 1205, likes: 110, date: '2026-07-20', content: 'PipraPay requires your API secret and merchant token. Ensure your public callback URL is set to /api/piprapay/webhook in your merchant portal settings.' },
    { id: 3, title: 'Configuring SMTP Server Settings for Transactional Emails & Payslips', category: 'System Administration', views: 560, likes: 42, date: '2026-06-11', content: 'Go to System Settings > Email SMTP tab. Input your SMTP Host, Port (587 for TLS, 465 for SSL), Username, and Password. Send a test email to verify DNS DKIM and SPF records.' },
    { id: 4, title: 'Double Entry Accounting Guide: Managing Chart of Accounts and Journal Vouchers', category: 'Accounting', views: 720, likes: 58, date: '2026-05-30', content: 'In TIC CRM, debits must strictly equal credits. All asset and expense increases are debited, while liability, equity, and revenue increases are credited.' },
  ]

  const filtered = articles.filter(a =>
    a.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
    a.category.toLowerCase().includes(searchQuery.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <BookOpen className="w-6 h-6 text-indigo-400" />
            <span>Knowledgebase & Support Documentation</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Browse corporate SOPs, API guides, user onboarding manuals, and troubleshooting articles.
          </p>
        </div>
        <button className="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Write Article</span>
        </button>
      </div>

      <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center justify-between">
        <div className="relative w-full sm:w-96">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Search guides, tutorials, keywords..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-200 focus:outline-none focus:border-indigo-500"
          />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {filtered.map(a => (
          <div
            key={a.id}
            onClick={() => setSelectedArticle(a)}
            className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 hover:border-indigo-500/50 cursor-pointer transition-all flex flex-col justify-between group"
          >
            <div>
              <span className="text-[11px] font-semibold text-indigo-400 bg-indigo-500/10 px-2.5 py-0.5 rounded-full border border-indigo-500/20">
                {a.category}
              </span>
              <h3 className="text-base font-bold text-white mt-3 group-hover:text-indigo-300 transition-colors">
                {a.title}
              </h3>
              <p className="text-xs text-slate-400 mt-2 line-clamp-2">{a.content}</p>
            </div>

            <div className="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
              <div className="flex items-center gap-3">
                <span className="flex items-center gap-1"><Eye className="w-3.5 h-3.5" />{a.views}</span>
                <span className="flex items-center gap-1"><ThumbsUp className="w-3.5 h-3.5" />{a.likes}</span>
              </div>
              <span className="text-indigo-400 flex items-center gap-1 font-semibold group-hover:translate-x-1 transition-transform">
                Read Article <ChevronRight className="w-3.5 h-3.5" />
              </span>
            </div>
          </div>
        ))}
      </div>

      {selectedArticle && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <div className="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs font-semibold text-indigo-400">{selectedArticle.category}</span>
              <button onClick={() => setSelectedArticle(null)} className="text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>
            <h2 className="text-lg font-bold text-white mb-3">{selectedArticle.title}</h2>
            <div className="p-4 rounded-xl bg-slate-800/50 text-xs text-slate-300 leading-relaxed space-y-2 mb-4">
              <p>{selectedArticle.content}</p>
              <p>For more detailed technical assistance, you can also submit a ticket from the Tickets & Complaints section.</p>
            </div>
            <div className="flex justify-between items-center text-xs text-slate-400 pt-2 border-t border-slate-800">
              <span>Published on {selectedArticle.date}</span>
              <button onClick={() => setSelectedArticle(null)} className="px-4 py-1.5 rounded-lg bg-indigo-600 text-white font-medium">
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
