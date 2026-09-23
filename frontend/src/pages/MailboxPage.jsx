import React, { useState } from 'react'
import {
  Mail,
  Send,
  Inbox,
  FileText,
  Trash2,
  Star,
  Search,
  Plus,
  Paperclip,
  Reply,
  Forward,
  CheckCircle,
} from 'lucide-react'

export default function MailboxPage() {
  const [activeFolder, setActiveFolder] = useState('inbox')
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedEmail, setSelectedEmail] = useState(null)
  const [showCompose, setShowCompose] = useState(false)

  const [emails, setEmails] = useState([
    {
      id: 1,
      sender: 'Marcus Vance',
      email: 'marcus.vance@apexretail.com',
      subject: 'Approval of Q3 Service Level Agreement & Billing Terms',
      snippet: 'Hi Forid, I have reviewed the SLA document and everything looks good. Please find the signed copy...',
      body: `Hello Forid,\n\nI have reviewed the Service Level Agreement for the upcoming quarter. Everything looks completely aligned with our corporate expectations. We are excited to continue partnering with TIC CRM.\n\nPlease find the countersigned document attached, and let us know once the billing schedule is activated in your portal.\n\nWarm regards,\nMarcus Vance\nChief Operating Officer, Apex Global`,
      date: '10:45 AM',
      unread: true,
      starred: true,
      hasAttachment: true,
      folder: 'inbox',
    },
    {
      id: 2,
      sender: 'PipraPay Merchant Services',
      email: 'notifications@piprapay.com',
      subject: 'Weekly Settlement Summary & Gateway Telemetry',
      snippet: 'Your merchant account has processed 148 transactions totaling $34,250.00 this week. Automatic payout scheduled...',
      body: `Dear Merchant,\n\nYour weekly transaction settlement statement is now generated. Total volume: $34,250.00 across 148 successful payments with 0 chargebacks.\n\nPayout has been dispatched to your designated corporate bank account.\n\nThank you for choosing PipraPay.`,
      date: 'Yesterday',
      unread: false,
      starred: false,
      hasAttachment: false,
      folder: 'inbox',
    },
    {
      id: 3,
      sender: 'Sarah Jenkins',
      email: 'sarah.j@enterprise.internal',
      subject: 'TimeSync Desktop 2.4 Regression Testing Complete',
      snippet: 'All 42 test suites for the background idle detection and screenshot blur features passed with 100% score...',
      body: `Hi Team,\n\nThe QA test execution for TimeSync Desktop v2.4 has finished. All edge cases regarding multi-monitor screenshot uploads and idle timeout triggers are resolved.\n\nReady for deployment when approved.\n\nBest,\nSarah`,
      date: 'Sep 21',
      unread: false,
      starred: true,
      hasAttachment: true,
      folder: 'inbox',
    },
  ])

  const [composeTo, setComposeTo] = useState('')
  const [composeSubject, setComposeSubject] = useState('')
  const [composeBody, setComposeBody] = useState('')

  const handleSend = (e) => {
    e.preventDefault()
    if (!composeTo || !composeSubject) return
    const newMail = {
      id: Date.now(),
      sender: 'Forid Mondol (Me)',
      email: 'forid@tic-crm.com',
      subject: composeSubject,
      snippet: composeBody.slice(0, 80) + '...',
      body: composeBody,
      date: 'Just now',
      unread: false,
      starred: false,
      hasAttachment: false,
      folder: 'sent',
    }
    setEmails([newMail, ...emails])
    setShowCompose(false)
    setComposeTo('')
    setComposeSubject('')
    setComposeBody('')
  }

  const currentList = emails.filter((e) => {
    if (activeFolder === 'starred') return e.starred
    return e.folder === activeFolder
  })

  return (
    <div className="space-y-6">
      {/* Page Title */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Mail className="w-5 h-5 text-indigo-400" />
            <span>Corporate Mailbox</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Unified inbox, notifications, and client communications
          </p>
        </div>

        <button
          onClick={() => setShowCompose(true)}
          className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Compose Email</span>
        </button>
      </div>

      {/* Main Mailbox Container */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        {/* Left Sidebar: Folders */}
        <div className="w-full md:w-56 p-4 border-b md:border-b-0 md:border-r border-slate-800 space-y-4 shrink-0">
          <div className="space-y-1">
            <button
              onClick={() => {
                setActiveFolder('inbox')
                setSelectedEmail(null)
              }}
              className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${
                activeFolder === 'inbox'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-300 hover:bg-slate-800/60'
              }`}
            >
              <div className="flex items-center gap-2.5">
                <Inbox className="w-4 h-4" />
                <span>Inbox</span>
              </div>
              <span className="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-slate-800 text-slate-300">
                {emails.filter((e) => e.folder === 'inbox').length}
              </span>
            </button>

            <button
              onClick={() => {
                setActiveFolder('starred')
                setSelectedEmail(null)
              }}
              className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${
                activeFolder === 'starred'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-300 hover:bg-slate-800/60'
              }`}
            >
              <div className="flex items-center gap-2.5">
                <Star className="w-4 h-4" />
                <span>Starred</span>
              </div>
              <span className="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-slate-800 text-slate-300">
                {emails.filter((e) => e.starred).length}
              </span>
            </button>

            <button
              onClick={() => {
                setActiveFolder('sent')
                setSelectedEmail(null)
              }}
              className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${
                activeFolder === 'sent'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-300 hover:bg-slate-800/60'
              }`}
            >
              <div className="flex items-center gap-2.5">
                <Send className="w-4 h-4" />
                <span>Sent</span>
              </div>
              <span className="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-slate-800 text-slate-300">
                {emails.filter((e) => e.folder === 'sent').length}
              </span>
            </button>

            <button
              onClick={() => {
                setActiveFolder('drafts')
                setSelectedEmail(null)
              }}
              className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${
                activeFolder === 'drafts'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-300 hover:bg-slate-800/60'
              }`}
            >
              <div className="flex items-center gap-2.5">
                <FileText className="w-4 h-4" />
                <span>Drafts</span>
              </div>
            </button>
          </div>
        </div>

        {/* Center: Email List */}
        <div className="w-full md:w-80 lg:w-96 border-b md:border-b-0 md:border-r border-slate-800 flex flex-col shrink-0">
          <div className="p-3 border-b border-slate-800">
            <div className="relative">
              <Search className="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search mail..."
                className="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-slate-800/80 border border-slate-700/60 text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
          </div>

          <div className="flex-1 overflow-y-auto divide-y divide-slate-800/60">
            {currentList.length === 0 ? (
              <p className="p-6 text-center text-xs text-slate-400">
                No emails in {activeFolder}.
              </p>
            ) : (
              currentList.map((m) => (
                <div
                  key={m.id}
                  onClick={() => setSelectedEmail(m)}
                  className={`p-3.5 cursor-pointer transition-colors ${
                    selectedEmail?.id === m.id
                      ? 'bg-indigo-950/40 border-l-2 border-indigo-500'
                      : 'hover:bg-slate-800/40'
                  }`}
                >
                  <div className="flex items-center justify-between gap-1 mb-1">
                    <span
                      className={`text-xs truncate ${
                        m.unread ? 'font-bold text-white' : 'font-medium text-slate-300'
                      }`}
                    >
                      {m.sender}
                    </span>
                    <span className="text-[10px] text-slate-400 shrink-0">
                      {m.date}
                    </span>
                  </div>

                  <h3
                    className={`text-xs truncate mb-1 ${
                      m.unread ? 'font-semibold text-indigo-300' : 'text-slate-200'
                    }`}
                  >
                    {m.subject}
                  </h3>

                  <p className="text-[11px] text-slate-400 line-clamp-2">
                    {m.snippet}
                  </p>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Right: Email Reading Pane */}
        <div className="flex-1 p-6 flex flex-col justify-between">
          {selectedEmail ? (
            <div className="space-y-6">
              <div className="flex items-start justify-between pb-4 border-b border-slate-800 gap-4">
                <div>
                  <h2 className="text-base sm:text-lg font-bold text-white">
                    {selectedEmail.subject}
                  </h2>
                  <div className="mt-1 flex items-center gap-2 text-xs text-slate-400">
                    <span className="font-semibold text-slate-200">
                      {selectedEmail.sender}
                    </span>
                    <span>&lt;{selectedEmail.email}&gt;</span>
                  </div>
                </div>
                <span className="text-xs text-slate-400 font-mono">
                  {selectedEmail.date}
                </span>
              </div>

              <div className="text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                {selectedEmail.body}
              </div>

              {selectedEmail.hasAttachment && (
                <div className="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 flex items-center gap-3">
                  <Paperclip className="w-4 h-4 text-indigo-400" />
                  <div className="text-xs flex-1">
                    <p className="font-medium text-slate-200">
                      Signed_SLA_ApexRetail_2026.pdf
                    </p>
                    <p className="text-[10px] text-slate-400">1.4 MB — Document</p>
                  </div>
                  <button className="px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-600/20 text-indigo-300 hover:bg-indigo-600/30">
                    Download
                  </button>
                </div>
              )}

              <div className="pt-4 border-t border-slate-800 flex items-center gap-3">
                <button className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white flex items-center gap-1.5 transition-colors">
                  <Reply className="w-3.5 h-3.5" />
                  <span>Reply</span>
                </button>
                <button className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-1.5 transition-colors">
                  <Forward className="w-3.5 h-3.5" />
                  <span>Forward</span>
                </button>
              </div>
            </div>
          ) : (
            <div className="py-24 text-center text-slate-400 text-xs flex flex-col items-center justify-center gap-2">
              <Mail className="w-8 h-8 text-slate-600" />
              <p>Select an email from the list to view its full contents.</p>
            </div>
          )}
        </div>
      </div>

      {/* Compose Modal */}
      {showCompose && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Compose Message</h3>
              <button
                onClick={() => setShowCompose(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleSend} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Recipient (To)
                </label>
                <input
                  type="email"
                  required
                  value={composeTo}
                  onChange={(e) => setComposeTo(e.target.value)}
                  placeholder="client@example.com"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Subject
                </label>
                <input
                  type="text"
                  required
                  value={composeSubject}
                  onChange={(e) => setComposeSubject(e.target.value)}
                  placeholder="e.g. Invoice #2026-089 Payment Receipt"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Message Body
                </label>
                <textarea
                  rows={6}
                  required
                  value={composeBody}
                  onChange={(e) => setComposeBody(e.target.value)}
                  placeholder="Write your email message..."
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="flex items-center justify-between pt-2">
                <button
                  type="button"
                  className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                  title="Attach file"
                >
                  <Paperclip className="w-4 h-4" />
                </button>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => setShowCompose(false)}
                    className="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white"
                  >
                    Discard
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white rounded-xl shadow-sm flex items-center gap-1.5 cursor-pointer"
                  >
                    <Send className="w-3.5 h-3.5" />
                    <span>Send Mail</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
