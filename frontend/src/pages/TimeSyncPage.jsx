import React, { useState, useEffect } from 'react'
import {
  Clock,
  Play,
  Pause,
  Square,
  Monitor,
  CheckCircle2,
  Calendar,
  Apple,
  Download,
  Activity,
  Layers,
} from 'lucide-react'

export default function TimeSyncPage() {
  const [isTracking, setIsTracking] = useState(false)
  const [seconds, setSeconds] = useState(3840) // 1 hr 4 mins demo
  const [currentTask, setCurrentTask] = useState('React UI Navigation Restructuring')
  const [selectedProject, setSelectedProject] = useState('TimeSync v2.4')

  useEffect(() => {
    let interval = null
    if (isTracking) {
      interval = setInterval(() => {
        setSeconds((s) => s + 1)
      }, 1000)
    }
    return () => clearInterval(interval)
  }, [isTracking])

  const formatTime = (totalSec) => {
    const hrs = Math.floor(totalSec / 3600)
    const mins = Math.floor((totalSec % 3600) / 60)
    const secs = totalSec % 60
    return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  const entries = [
    {
      id: 1,
      task: 'React UI Navigation Restructuring',
      project: 'TimeSync v2.4',
      date: 'Today',
      duration: '01:04:00',
      status: 'Running',
    },
    {
      id: 2,
      task: 'PipraPay Webhook Signature Verification',
      project: 'TimeSync v2.4',
      date: 'Today',
      duration: '02:15:30',
      status: 'Completed',
    },
    {
      id: 3,
      task: 'Biometric Attendance Schema Sync',
      project: 'HRM System',
      date: 'Yesterday',
      duration: '03:40:12',
      status: 'Completed',
    },
    {
      id: 4,
      task: 'Database Query Optimization',
      project: 'Cloud Infrastructure',
      date: 'Sep 21, 2026',
      duration: '01:45:00',
      status: 'Completed',
    },
  ]

  return (
    <div className="space-y-6">
      {/* Title */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <Clock className="w-5 h-5 text-indigo-400" />
            <span>TimeSync Desktop & Work Tracker</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Real-time automated time logging, activity telemetry, and desktop client integration
          </p>
        </div>

        {/* Download client shortcuts */}
        <div className="flex items-center gap-2">
          <a
            href="http://localhost/tic_crm/api/updates/timesync/download?platform=windows"
            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-1.5 transition-colors"
          >
            <Download className="w-3.5 h-3.5 text-indigo-400" />
            <span>Windows</span>
          </a>
          <a
            href="http://localhost/tic_crm/api/updates/timesync/download?platform=macos"
            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 flex items-center gap-1.5 transition-colors"
          >
            <Download className="w-3.5 h-3.5 text-purple-400" />
            <span>macOS</span>
          </a>
        </div>
      </div>

      {/* Live Stopwatch Tracker Bar */}
      <div className="p-6 rounded-2xl bg-gradient-to-r from-slate-900/90 via-indigo-950/40 to-slate-900/90 border border-slate-800/80 backdrop-blur shadow-xl">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-1">
            <span className="text-xs font-semibold text-indigo-400 uppercase tracking-wider">
              {isTracking ? 'Active Time Tracker' : 'Tracker Standby'}
            </span>
            <div className="flex items-center gap-3">
              <span className="text-3xl sm:text-4xl font-extrabold font-mono text-white tracking-wider">
                {formatTime(seconds)}
              </span>
              {isTracking && (
                <span className="w-3 h-3 rounded-full bg-emerald-500 animate-ping" />
              )}
            </div>
            <p className="text-xs text-slate-400">
              Task: <strong className="text-slate-200">{currentTask}</strong> ({selectedProject})
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsTracking(!isTracking)}
              className={`px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg transition-all flex items-center gap-2 cursor-pointer ${
                isTracking
                  ? 'bg-amber-600 hover:bg-amber-500 text-white shadow-amber-600/30'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30'
              }`}
            >
              {isTracking ? <Pause className="w-4 h-4" /> : <Play className="w-4 h-4" />}
              <span>{isTracking ? 'Pause Timer' : 'Start Timer'}</span>
            </button>

            <button
              onClick={() => {
                setIsTracking(false)
                setSeconds(0)
              }}
              className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5 cursor-pointer"
            >
              <Square className="w-3.5 h-3.5" />
              <span>Reset</span>
            </button>
          </div>
        </div>
      </div>

      {/* KPI Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Hours Today</span>
          <p className="text-xl font-bold text-white mt-1">4.2 hrs</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Weekly Total</span>
          <p className="text-xl font-bold text-indigo-400 mt-1">28.5 hrs</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Productive Ratio</span>
          <p className="text-xl font-bold text-emerald-400 mt-1">89.4%</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Sync Status</span>
          <p className="text-xl font-bold text-purple-400 mt-1">Connected</p>
        </div>
      </div>

      {/* Time Entries Table */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
        <div className="p-4 border-b border-slate-800 flex items-center justify-between">
          <h2 className="text-sm font-bold text-white">Recent Time Entries</h2>
          <span className="text-xs text-slate-400">Synced across Web & Desktop</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Task Description</th>
                <th className="py-3 px-4">Project</th>
                <th className="py-3 px-4">Date</th>
                <th className="py-3 px-4">Duration</th>
                <th className="py-3 px-4">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {entries.map((e) => (
                <tr key={e.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-semibold text-white">{e.task}</td>
                  <td className="py-3 px-4 text-slate-300">{e.project}</td>
                  <td className="py-3 px-4 text-slate-400">{e.date}</td>
                  <td className="py-3 px-4 font-mono font-bold text-indigo-300">
                    {e.duration}
                  </td>
                  <td className="py-3 px-4">
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                        e.status === 'Running'
                          ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 animate-pulse'
                          : 'bg-slate-800 text-slate-400 border-slate-700'
                      }`}
                    >
                      {e.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
