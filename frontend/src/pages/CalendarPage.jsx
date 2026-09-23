import React, { useState } from 'react'
import {
  Calendar as CalendarIcon,
  ChevronLeft,
  ChevronRight,
  Plus,
  Clock,
  MapPin,
  Users,
  Tag,
  CheckCircle2,
} from 'lucide-react'

export default function CalendarPage() {
  const [currentMonth, setCurrentMonth] = useState('September 2026')
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [showEventModal, setShowEventModal] = useState(false)
  const [events, setEvents] = useState([
    {
      id: 1,
      title: 'Q3 Financial Review Meeting',
      date: '2026-09-24',
      day: 24,
      time: '10:00 AM - 11:30 AM',
      category: 'meeting',
      badgeColor: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
      dotColor: 'bg-indigo-500',
      attendees: ['Forid Mondol', 'Finance Team'],
    },
    {
      id: 2,
      title: 'E-Commerce Platform Deployment',
      date: '2026-09-25',
      day: 25,
      time: '02:00 PM - 04:00 PM',
      category: 'project',
      badgeColor: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
      dotColor: 'bg-purple-500',
      attendees: ['Dev Ops', 'Sarah Jenkins'],
    },
    {
      id: 3,
      title: 'Monthly Executive Payroll Processing',
      date: '2026-09-28',
      day: 28,
      time: '11:00 AM - 01:00 PM',
      category: 'finance',
      badgeColor: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      dotColor: 'bg-emerald-500',
      attendees: ['HR Dept', 'Accounts'],
    },
    {
      id: 4,
      title: 'Client Demo: Apex Global Retail',
      date: '2026-09-29',
      day: 29,
      time: '03:30 PM - 04:30 PM',
      category: 'meeting',
      badgeColor: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
      dotColor: 'bg-amber-500',
      attendees: ['Sales Team', 'Marcus Vance'],
    },
  ])

  const [newEventTitle, setNewEventTitle] = useState('')
  const [newEventDate, setNewEventDate] = useState('2026-09-24')
  const [newEventTime, setNewEventTime] = useState('10:00 AM')
  const [newEventCategory, setNewEventCategory] = useState('meeting')

  const handleCreateEvent = (e) => {
    e.preventDefault()
    if (!newEventTitle) return
    const dayNumber = parseInt(newEventDate.split('-')[2] || '24', 10)
    const newEv = {
      id: Date.now(),
      title: newEventTitle,
      date: newEventDate,
      day: dayNumber,
      time: newEventTime,
      category: newEventCategory,
      badgeColor:
        newEventCategory === 'meeting'
          ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
          : newEventCategory === 'project'
          ? 'bg-purple-500/10 text-purple-400 border-purple-500/20'
          : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
      dotColor:
        newEventCategory === 'meeting'
          ? 'bg-indigo-500'
          : newEventCategory === 'project'
          ? 'bg-purple-500'
          : 'bg-emerald-500',
      attendees: ['Forid Mondol'],
    }
    setEvents([newEv, ...events])
    setNewEventTitle('')
    setShowEventModal(false)
  }

  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  // September 2026 starts on Tuesday (offset 2)
  const calendarDays = Array.from({ length: 30 }, (_, i) => i + 1)

  const filteredEvents =
    selectedCategory === 'all'
      ? events
      : events.filter((e) => e.category === selectedCategory)

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <CalendarIcon className="w-5 h-5 text-indigo-400" />
            <span>Calendar & Schedule</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Organize corporate meetings, project deadlines, and employee leave events
          </p>
        </div>

        <div className="flex items-center gap-3">
          <div className="flex items-center bg-slate-800/80 rounded-xl border border-slate-700/60 p-1">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors ${
                selectedCategory === 'all'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              All
            </button>
            <button
              onClick={() => setSelectedCategory('meeting')}
              className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors ${
                selectedCategory === 'meeting'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              Meetings
            </button>
            <button
              onClick={() => setSelectedCategory('project')}
              className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors ${
                selectedCategory === 'project'
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              Projects
            </button>
          </div>

          <button
            onClick={() => setShowEventModal(true)}
            className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add Event</span>
          </button>
        </div>
      </div>

      {/* Main Grid: Calendar Grid & Upcoming Event List */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Calendar Grid View */}
        <div className="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-slate-800">
            <div className="flex items-center gap-2">
              <h2 className="text-sm font-bold text-white">{currentMonth}</h2>
              <span className="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 font-bold border border-indigo-500/20">
                Today: 23rd
              </span>
            </div>
            <div className="flex items-center gap-1">
              <button className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <ChevronLeft className="w-4 h-4" />
              </button>
              <button className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>
          </div>

          {/* Days of week header */}
          <div className="grid grid-cols-7 gap-1 text-center">
            {daysOfWeek.map((day) => (
              <div key={day} className="text-[11px] font-bold text-slate-400 py-1">
                {day}
              </div>
            ))}
          </div>

          {/* Calendar Day Cells */}
          <div className="grid grid-cols-7 gap-1.5">
            {/* Empty slots for Tuesday start */}
            <div className="h-16 rounded-xl bg-slate-950/30 opacity-20" />
            <div className="h-16 rounded-xl bg-slate-950/30 opacity-20" />

            {calendarDays.map((day) => {
              const dayEvents = events.filter((e) => e.day === day)
              const isToday = day === 23
              return (
                <div
                  key={day}
                  className={`h-16 sm:h-20 p-1.5 rounded-xl border flex flex-col justify-between transition-all ${
                    isToday
                      ? 'bg-indigo-950/40 border-indigo-500/60 ring-1 ring-indigo-500/50'
                      : 'bg-slate-800/40 border-slate-800 hover:border-slate-700'
                  }`}
                >
                  <div className="flex items-center justify-between">
                    <span
                      className={`text-xs font-semibold ${
                        isToday ? 'text-indigo-400 font-bold' : 'text-slate-300'
                      }`}
                    >
                      {day}
                    </span>
                    {dayEvents.length > 0 && (
                      <span className="w-1.5 h-1.5 rounded-full bg-indigo-400" />
                    )}
                  </div>

                  <div className="space-y-0.5 overflow-hidden">
                    {dayEvents.slice(0, 1).map((ev) => (
                      <div
                        key={ev.id}
                        className="truncate text-[9px] font-medium px-1 py-0.2 rounded bg-indigo-500/20 text-indigo-300"
                        title={ev.title}
                      >
                        {ev.title}
                      </div>
                    ))}
                    {dayEvents.length > 1 && (
                      <span className="text-[9px] text-slate-400 font-bold">
                        +{dayEvents.length - 1} more
                      </span>
                    )}
                  </div>
                </div>
              )
            })}
          </div>
        </div>

        {/* Upcoming Events Feed */}
        <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-sm font-bold text-white">Upcoming Events ({filteredEvents.length})</h2>
            <span className="text-xs text-slate-400">Next 7 days</span>
          </div>

          <div className="space-y-3">
            {filteredEvents.map((ev) => (
              <div
                key={ev.id}
                className="p-3.5 rounded-xl bg-slate-800/50 border border-slate-700/40 hover:border-slate-600 transition-all space-y-2"
              >
                <div className="flex items-start justify-between gap-2">
                  <h3 className="text-xs font-semibold text-white leading-snug">
                    {ev.title}
                  </h3>
                  <span
                    className={`text-[9px] font-bold px-2 py-0.5 rounded-full border capitalize ${ev.badgeColor}`}
                  >
                    {ev.category}
                  </span>
                </div>

                <div className="flex items-center gap-3 text-[11px] text-slate-400">
                  <span className="flex items-center gap-1">
                    <Clock className="w-3 h-3 text-slate-400" /> {ev.time}
                  </span>
                </div>

                <div className="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                  <span className="text-slate-400 font-mono">Date: {ev.date}</span>
                  <div className="flex items-center gap-1 text-slate-300 font-medium text-[10px]">
                    <Users className="w-3 h-3 text-slate-400" />
                    <span>{ev.attendees.join(', ')}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Add Event Modal */}
      {showEventModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Schedule New Event</h3>
              <button
                onClick={() => setShowEventModal(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleCreateEvent} className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Event Title
                </label>
                <input
                  type="text"
                  required
                  value={newEventTitle}
                  onChange={(e) => setNewEventTitle(e.target.value)}
                  placeholder="e.g. Stakeholder Sync & Roadmap Review"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Date
                  </label>
                  <input
                    type="date"
                    required
                    value={newEventDate}
                    onChange={(e) => setNewEventDate(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Time
                  </label>
                  <input
                    type="text"
                    value={newEventTime}
                    onChange={(e) => setNewEventTime(e.target.value)}
                    placeholder="10:00 AM - 11:00 AM"
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Category
                </label>
                <select
                  value={newEventCategory}
                  onChange={(e) => setNewEventCategory(e.target.value)}
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="meeting">Executive Meeting</option>
                  <option value="project">Project Milestone</option>
                  <option value="finance">Financial / Payroll</option>
                </select>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowEventModal(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white rounded-xl shadow-sm cursor-pointer"
                >
                  Save Event
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
