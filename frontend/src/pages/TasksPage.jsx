import React, { useState, useEffect } from 'react'
import {
  CheckSquare,
  Plus,
  Search,
  Filter,
  Clock,
  User,
  AlertCircle,
  CheckCircle2,
  MoreVertical,
  Kanban,
  List,
} from 'lucide-react'
import api from '../api/client'
import { useAuthStore } from '../store/useAuthStore'

export default function TasksPage() {
  const { user } = useAuthStore()
  const [viewMode, setViewMode] = useState('kanban')
  const [showModal, setShowModal] = useState(false)
  const [loading, setLoading] = useState(false)

  const [tasks, setTasks] = useState([
    {
      id: 1,
      task_name: 'Implement PipraPay Dynamic Gateway Discovery',
      status: 'in_progress',
      priority: 'urgent',
      project: 'TimeSync v2.4',
      due_date: '2026-09-25',
      assignee: 'Forid M.',
      comments_count: 5,
    },
    {
      id: 2,
      task_name: 'Optimize CodeIgniter Database Session Locks',
      status: 'not_started',
      priority: 'high',
      project: 'Cloud Infrastructure',
      due_date: '2026-09-28',
      assignee: 'Sarah J.',
      comments_count: 2,
    },
    {
      id: 3,
      task_name: 'UI Redesign: Sidebar Navigation Restructuring',
      status: 'completed',
      priority: 'high',
      project: 'React Modernization',
      due_date: '2026-09-23',
      assignee: 'Forid M.',
      comments_count: 8,
    },
    {
      id: 4,
      task_name: 'Audit Client Biometric Timecard Sync',
      status: 'review',
      priority: 'medium',
      project: 'HRM Attendance',
      due_date: '2026-09-27',
      assignee: 'David C.',
      comments_count: 3,
    },
    {
      id: 5,
      task_name: 'Stripe & PayPal Webhook Error Logging',
      status: 'in_progress',
      priority: 'medium',
      project: 'TimeSync v2.4',
      due_date: '2026-09-29',
      assignee: 'Forid M.',
      comments_count: 1,
    },
  ])

  const [taskName, setTaskName] = useState('')
  const [taskPriority, setTaskPriority] = useState('medium')
  const [taskStatus, setTaskStatus] = useState('not_started')
  const [taskDueDate, setTaskDueDate] = useState('')

  const columns = [
    { key: 'not_started', label: 'To Do / Backlog', color: 'border-slate-700' },
    { key: 'in_progress', label: 'In Progress', color: 'border-indigo-500/50' },
    { key: 'review', label: 'In Review', color: 'border-purple-500/50' },
    { key: 'completed', label: 'Completed', color: 'border-emerald-500/50' },
  ]

  const handleCreateTask = async (e) => {
    e.preventDefault()
    if (!taskName.trim()) return

    const newTask = {
      id: Date.now(),
      task_name: taskName.trim(),
      status: taskStatus,
      priority: taskPriority,
      project: 'General Workspace',
      due_date: taskDueDate || '2026-10-05',
      assignee: user?.full_name || 'Forid M.',
      comments_count: 0,
    }

    setTasks([newTask, ...tasks])
    setShowModal(false)
    setTaskName('')
    setTaskDueDate('')

    // Attempt API save in background if live
    try {
      await api.post('/tasks', {
        task_name: newTask.task_name,
        task_status: newTask.status,
        priority: newTask.priority,
        due_date: newTask.due_date,
      })
    } catch (e) {
      // Gracefully retained in state
    }
  }

  const updateStatus = (id, newStatus) => {
    setTasks((prev) =>
      prev.map((t) => (t.id === id ? { ...t, status: newStatus } : t))
    )
  }

  const getPriorityBadge = (p) => {
    switch (p) {
      case 'urgent':
        return 'bg-rose-500/10 text-rose-400 border-rose-500/20'
      case 'high':
        return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
      case 'medium':
        return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
      default:
        return 'bg-slate-800 text-slate-400 border-slate-700'
    }
  }

  return (
    <div className="space-y-6">
      {/* Title & Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
            <CheckSquare className="w-5 h-5 text-indigo-400" />
            <span>Tasks & Kanban Board</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-0.5">
            Sprint planning, priority execution, and real-time task status tracking
          </p>
        </div>

        <div className="flex items-center gap-3">
          <div className="flex items-center bg-slate-800/80 rounded-xl border border-slate-700/60 p-1">
            <button
              onClick={() => setViewMode('kanban')}
              className={`p-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 ${
                viewMode === 'kanban'
                  ? 'bg-indigo-600 text-white'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <Kanban className="w-3.5 h-3.5" />
              <span>Board</span>
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`p-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 ${
                viewMode === 'list'
                  ? 'bg-indigo-600 text-white'
                  : 'text-slate-400 hover:text-white'
              }`}
            >
              <List className="w-3.5 h-3.5" />
              <span>List</span>
            </button>
          </div>

          <button
            onClick={() => setShowModal(true)}
            className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>New Task</span>
          </button>
        </div>
      </div>

      {/* Kanban Board */}
      {viewMode === 'kanban' ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 overflow-x-auto pb-4">
          {columns.map((col) => {
            const colTasks = tasks.filter((t) => t.status === col.key)
            return (
              <div
                key={col.key}
                className="flex flex-col rounded-2xl bg-slate-900/60 border border-slate-800/80 p-4 space-y-3 min-w-[260px]"
              >
                <div className="flex items-center justify-between pb-2 border-b border-slate-800">
                  <span className="text-xs font-bold text-white">{col.label}</span>
                  <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">
                    {colTasks.length}
                  </span>
                </div>

                <div className="space-y-3 flex-1">
                  {colTasks.length === 0 ? (
                    <div className="h-28 rounded-xl border border-dashed border-slate-800/80 flex items-center justify-center text-[11px] text-slate-500">
                      Empty column
                    </div>
                  ) : (
                    colTasks.map((task) => (
                      <div
                        key={task.id}
                        className="p-4 rounded-xl bg-slate-800/60 border border-slate-700/50 hover:border-slate-600 space-y-2.5 shadow-sm transition-all"
                      >
                        <div className="flex items-start justify-between gap-2">
                          <span
                            className={`text-[9px] font-bold px-2 py-0.5 rounded-full border uppercase ${getPriorityBadge(
                              task.priority
                            )}`}
                          >
                            {task.priority}
                          </span>
                          <span className="text-[10px] text-slate-400 font-medium">
                            {task.project}
                          </span>
                        </div>

                        <h3 className="text-xs font-semibold text-white leading-snug">
                          {task.task_name}
                        </h3>

                        <div className="pt-2 border-t border-slate-700/60 flex items-center justify-between text-[11px] text-slate-400">
                          <span className="flex items-center gap-1">
                            <Clock className="w-3 h-3 text-slate-400" /> {task.due_date}
                          </span>
                          <div className="flex items-center gap-1.5 text-slate-300 text-[10px] font-medium">
                            <User className="w-3 h-3 text-slate-400" />
                            <span>{task.assignee}</span>
                          </div>
                        </div>

                        <div className="pt-1 flex items-center justify-between">
                          <span className="text-[10px] text-slate-400">
                            Move:
                          </span>
                          <select
                            value={task.status}
                            onChange={(e) => updateStatus(task.id, e.target.value)}
                            className="bg-slate-900 border border-slate-700 text-slate-200 text-[10px] rounded px-1.5 py-0.5 focus:outline-none"
                          >
                            <option value="not_started">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">In Review</option>
                            <option value="completed">Completed</option>
                          </select>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )
          })}
        </div>
      ) : (
        /* List View */
        <div className="rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-bold tracking-wider bg-slate-950/20">
                <th className="py-3 px-4">Task Name</th>
                <th className="py-3 px-4">Project</th>
                <th className="py-3 px-4">Priority</th>
                <th className="py-3 px-4">Due Date</th>
                <th className="py-3 px-4">Assignee</th>
                <th className="py-3 px-4">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60">
              {tasks.map((t) => (
                <tr key={t.id} className="hover:bg-slate-800/40 transition-colors">
                  <td className="py-3 px-4 font-semibold text-white">{t.task_name}</td>
                  <td className="py-3 px-4 text-slate-300">{t.project}</td>
                  <td className="py-3 px-4">
                    <span
                      className={`text-[9px] font-bold px-2 py-0.5 rounded-full border uppercase ${getPriorityBadge(
                        t.priority
                      )}`}
                    >
                      {t.priority}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-slate-400">{t.due_date}</td>
                  <td className="py-3 px-4 text-slate-300">{t.assignee}</td>
                  <td className="py-3 px-4">
                    <select
                      value={t.status}
                      onChange={(e) => updateStatus(t.id, e.target.value)}
                      className="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1"
                    >
                      <option value="not_started">To Do</option>
                      <option value="in_progress">In Progress</option>
                      <option value="review">In Review</option>
                      <option value="completed">Completed</option>
                    </select>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Create Task Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="text-base font-bold text-white">Create New Task</h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-white text-lg font-bold"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleCreateTask} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Task Name / Description
                </label>
                <input
                  type="text"
                  required
                  value={taskName}
                  onChange={(e) => setTaskName(e.target.value)}
                  placeholder="e.g. Audit API authentication headers"
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Priority
                  </label>
                  <select
                    value={taskPriority}
                    onChange={(e) => setTaskPriority(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1">
                    Initial Status
                  </label>
                  <select
                    value={taskStatus}
                    onChange={(e) => setTaskStatus(e.target.value)}
                    className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  >
                    <option value="not_started">To Do</option>
                    <option value="in_progress">In Progress</option>
                    <option value="review">In Review</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-300 mb-1">
                  Due Date
                </label>
                <input
                  type="date"
                  value={taskDueDate}
                  onChange={(e) => setTaskDueDate(e.target.value)}
                  className="w-full px-3 py-2 text-xs rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <div className="flex items-center justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-300 hover:text-white"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white rounded-xl shadow-sm cursor-pointer"
                >
                  Create Task
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
