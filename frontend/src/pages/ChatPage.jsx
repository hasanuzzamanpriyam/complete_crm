import React, { useState } from 'react'
import {
  MessageSquare,
  Hash,
  User,
  Send,
  Paperclip,
  Smile,
  Search,
  Sparkles,
  CheckCheck,
} from 'lucide-react'
import { useAuthStore } from '../store/useAuthStore'

export default function ChatPage() {
  const { user } = useAuthStore()
  const [activeChannel, setActiveChannel] = useState('#general')
  const [inputText, setInputText] = useState('')

  const channels = [
    { name: '#general', unread: 0 },
    { name: '#development', unread: 2 },
    { name: '#sales-leads', unread: 0 },
    { name: '#timesync-support', unread: 1 },
  ]

  const directMessages = [
    { name: 'Sarah Jenkins', role: 'Dev Lead', status: 'online' },
    { name: 'Marcus Vance', role: 'COO Apex', status: 'offline' },
    { name: 'David Chen', role: 'Finance Head', status: 'online' },
  ]

  const [messages, setMessages] = useState([
    {
      id: 1,
      sender: 'Sarah Jenkins',
      time: '10:14 AM',
      text: 'Good morning everyone! Just deployed the v2.4 build of TimeSync to testing.',
      isMe: false,
    },
    {
      id: 2,
      sender: 'David Chen',
      time: '10:20 AM',
      text: 'Great work! All financial reports for September are now aggregated in the Accounting tab.',
      isMe: false,
    },
    {
      id: 3,
      sender: 'Forid Mondol',
      time: '10:32 AM',
      text: 'Excellent. I am reviewing the dashboard widgets and will sign off on the production release today.',
      isMe: true,
    },
  ])

  const handleSendMessage = (e) => {
    e.preventDefault()
    if (!inputText.trim()) return

    const newMsg = {
      id: Date.now(),
      sender: user?.full_name || 'Forid Mondol',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      text: inputText.trim(),
      isMe: true,
    }

    setMessages([...messages, newMsg])
    setInputText('')
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
          <MessageSquare className="w-5 h-5 text-indigo-400" />
          <span>Team Collaboration & Chat</span>
        </h1>
        <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
          Real-time internal messaging, project discussions, and instant notifications
        </p>
      </div>

      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden flex flex-col md:flex-row h-[620px]">
        {/* Left Sidebar: Channels & DMs */}
        <div className="w-full md:w-64 border-b md:border-b-0 md:border-r border-slate-800 p-4 flex flex-col justify-between shrink-0">
          <div className="space-y-5">
            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2 px-1">
                Channels
              </span>
              <div className="space-y-1">
                {channels.map((c) => (
                  <button
                    key={c.name}
                    onClick={() => setActiveChannel(c.name)}
                    className={`w-full flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors ${
                      activeChannel === c.name
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-slate-300 hover:bg-slate-800/60'
                    }`}
                  >
                    <div className="flex items-center gap-2">
                      <Hash className="w-4 h-4 text-slate-400" />
                      <span>{c.name}</span>
                    </div>
                    {c.unread > 0 && (
                      <span className="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-rose-500 text-white">
                        {c.unread}
                      </span>
                    )}
                  </button>
                ))}
              </div>
            </div>

            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2 px-1">
                Direct Messages
              </span>
              <div className="space-y-1">
                {directMessages.map((dm) => (
                  <button
                    key={dm.name}
                    onClick={() => setActiveChannel(dm.name)}
                    className={`w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors ${
                      activeChannel === dm.name
                        ? 'bg-indigo-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800/60'
                    }`}
                  >
                    <div className="flex items-center gap-2 truncate">
                      <div className="relative">
                        <div className="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-bold text-white">
                          {dm.name.charAt(0)}
                        </div>
                        <span
                          className={`absolute bottom-0 right-0 w-2 h-2 rounded-full ring-2 ring-slate-900 ${
                            dm.status === 'online' ? 'bg-emerald-500' : 'bg-slate-500'
                          }`}
                        />
                      </div>
                      <span className="truncate">{dm.name}</span>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* Right Pane: Chat Thread */}
        <div className="flex-1 flex flex-col justify-between bg-slate-950/40">
          {/* Channel Header */}
          <div className="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/50">
            <div className="flex items-center gap-2">
              <Hash className="w-4 h-4 text-indigo-400" />
              <h2 className="text-sm font-bold text-white">{activeChannel}</h2>
              <span className="text-xs text-slate-400">| Project discussions & sync</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold">
                Connected
              </span>
            </div>
          </div>

          {/* Messages Flow */}
          <div className="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            {messages.map((m) => (
              <div
                key={m.id}
                className={`flex gap-3 max-w-xl ${
                  m.isMe ? 'ml-auto flex-row-reverse text-right' : ''
                }`}
              >
                <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                  {m.sender.charAt(0)}
                </div>
                <div className="space-y-1">
                  <div
                    className={`flex items-center gap-2 text-[11px] ${
                      m.isMe ? 'justify-end' : ''
                    }`}
                  >
                    <span className="font-semibold text-slate-200">{m.sender}</span>
                    <span className="text-slate-400">{m.time}</span>
                  </div>
                  <div
                    className={`p-3 rounded-2xl text-xs sm:text-sm leading-relaxed ${
                      m.isMe
                        ? 'bg-indigo-600 text-white rounded-tr-none'
                        : 'bg-slate-800 text-slate-200 border border-slate-700/60 rounded-tl-none'
                    }`}
                  >
                    {m.text}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Message Input Box */}
          <form
            onSubmit={handleSendMessage}
            className="p-3 border-t border-slate-800 bg-slate-900/80 flex items-center gap-2"
          >
            <button
              type="button"
              className="p-2 text-slate-400 hover:text-white rounded-lg transition-colors"
              title="Attach File"
            >
              <Paperclip className="w-4 h-4" />
            </button>
            <input
              type="text"
              value={inputText}
              onChange={(e) => setInputText(e.target.value)}
              placeholder={`Send message to ${activeChannel}...`}
              className="flex-1 px-4 py-2 text-xs sm:text-sm rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <button
              type="submit"
              className="p-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-sm transition-colors cursor-pointer"
            >
              <Send className="w-4 h-4" />
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}
