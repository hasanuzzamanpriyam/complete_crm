import React, { useState } from 'react'
import {
  UserCheck,
  Clock,
  Calendar,
  CheckCircle2,
  AlertCircle,
  MapPin,
  TrendingUp,
} from 'lucide-react'
import { useAuthStore } from '../store/useAuthStore'

export default function AttendancePage() {
  const { user } = useAuthStore()
  const [isClockedIn, setIsClockedIn] = useState(false)
  const [clockInTime, setClockInTime] = useState('09:12 AM')

  const records = [
    {
      date: '2026-09-23',
      day: 'Wednesday',
      in: '09:12 AM',
      out: '—',
      hours: '3h 48m',
      overtime: '0h 00m',
      status: 'Present',
      color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    },
    {
      date: '2026-09-22',
      day: 'Tuesday',
      in: '09:04 AM',
      out: '06:15 PM',
      hours: '9h 11m',
      overtime: '1h 11m',
      status: 'Present',
      color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    },
    {
      date: '2026-09-21',
      day: 'Monday',
      in: '09:28 AM',
      out: '06:00 PM',
      hours: '8h 32m',
      overtime: '0h 00m',
      status: 'Late',
      color: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    },
    {
      date: '2026-09-18',
      day: 'Friday',
      in: '08:58 AM',
      out: '05:30 PM',
      hours: '8h 32m',
      overtime: '0h 00m',
      status: 'Present',
      color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    },
  ]

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <UserCheck className="w-5 h-5 text-indigo-400" />
            <span>Attendance & Biometric Timecard</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Clock-in logs, biometric synchronization, shift hours, and overtime monitoring
          </p>
        </div>
      </div>

      {/* Clock In / Out Banner Card */}
      <div className="p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div className="space-y-1">
          <span className="text-xs font-semibold text-slate-400">Current Session</span>
          <div className="flex items-center gap-3">
            <span className="text-2xl font-bold text-white font-mono">
              {isClockedIn ? `Clocked In at ${clockInTime}` : 'Not Clocked In'}
            </span>
            <span
              className={`w-2.5 h-2.5 rounded-full ${
                isClockedIn ? 'bg-emerald-500 animate-pulse' : 'bg-slate-500'
              }`}
            />
          </div>
          <p className="text-xs text-slate-400 flex items-center gap-1.5">
            <MapPin className="w-3.5 h-3.5 text-indigo-400" />
            <span>HQ Office Network (IP: 192.168.1.104) — Geolocation verified</span>
          </p>
        </div>

        <button
          onClick={() => setIsClockedIn(!isClockedIn)}
          className={`px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg transition-all flex items-center gap-2 cursor-pointer ${
            isClockedIn
              ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-600/30'
              : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30'
          }`}
        >
          <Clock className="w-4 h-4" />
          <span>{isClockedIn ? 'Clock Out Now' : 'Clock In Now'}</span>
        </button>
      </div>

      {/* KPI Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Days Present</span>
          <p className="text-xl font-bold text-white mt-1">21 Days</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Late In Count</span>
          <p className="text-xl font-bold text-amber-400 mt-1">1 Day</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Total Work Hours</span>
          <p className="text-xl font-bold text-indigo-400 mt-1">172.5 hrs</p>
        </div>
        <div className="p-4 rounded-xl bg-slate-900/70 border border-slate-800 backdrop-blur">
          <span className="text-[11px] font-medium text-slate-400">Overtime Logged</span>
          <p className="text-xl font-bold text-emerald-400 mt-1">14.8 hrs</p>
        </div>
      </div>

      {/* Attendance History Table */}
      <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
        <div className="p-4 border-b border-slate-800 flex items-center justify-between">
          <h2 className="text-sm font-bold text-white">September 2026 Timesheet</h2>
          <span className="text-xs text-slate-400">Showing recent work shifts</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Date</th>
                <th className="py-3 px-4">Day</th>
                <th className="py-3 px-4">Clock In</th>
                <th className="py-3 px-4">Clock Out</th>
                <th className="py-3 px-4">Hours Logged</th>
                <th className="py-3 px-4">Overtime</th>
                <th className="py-3 px-4">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {records.map((r, idx) => (
                <tr key={idx} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-mono font-medium text-white">{r.date}</td>
                  <td className="py-3 px-4 text-slate-300">{r.day}</td>
                  <td className="py-3 px-4 text-slate-300">{r.in}</td>
                  <td className="py-3 px-4 text-slate-300">{r.out}</td>
                  <td className="py-3 px-4 font-mono font-bold text-indigo-300">
                    {r.hours}
                  </td>
                  <td className="py-3 px-4 font-mono text-slate-400">{r.overtime}</td>
                  <td className="py-3 px-4">
                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${r.color}`}>
                      {r.status}
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
