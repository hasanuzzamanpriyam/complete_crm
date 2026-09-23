import React, { useState, useEffect } from 'react'
import { CheckSquare, Plus, Trash2, CheckCircle2, Circle } from 'lucide-react'

export default function TodoListWidget() {
  const [todos, setTodos] = useState(() => {
    const saved = localStorage.getItem('tic_crm_todos')
    if (saved) {
      try {
        return JSON.parse(saved)
      } catch (e) {}
    }
    return [
      { id: 1, text: 'Review PipraPay payment gateway settlement logs', completed: false, priority: 'high' },
      { id: 2, text: 'Prepare Q3 project progress report for stakeholders', completed: false, priority: 'medium' },
      { id: 3, text: 'Audit employee biometric clock-in exemptions', completed: true, priority: 'normal' },
      { id: 4, text: 'Approve pending annual leave requests for engineering team', completed: false, priority: 'high' },
    ]
  })

  const [newTodo, setNewTodo] = useState('')

  useEffect(() => {
    localStorage.setItem('tic_crm_todos', JSON.stringify(todos))
  }, [todos])

  const toggleTodo = (id) => {
    setTodos((prev) =>
      prev.map((t) => (t.id === id ? { ...t, completed: !t.completed } : t))
    )
  }

  const addTodo = (e) => {
    e.preventDefault()
    if (!newTodo.trim()) return
    setTodos((prev) => [
      {
        id: Date.now(),
        text: newTodo.trim(),
        completed: false,
        priority: 'normal',
      },
      ...prev,
    ])
    setNewTodo('')
  }

  const deleteTodo = (id) => {
    setTodos((prev) => prev.filter((t) => t.id !== id))
  }

  const pendingCount = todos.filter((t) => !t.completed).length

  return (
    <div className="p-5 sm:p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 backdrop-blur space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-sm sm:text-base font-bold text-white flex items-center gap-2">
            <CheckSquare className="w-4 h-4 text-emerald-400" />
            <span>Personal Action Items & To-Dos</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            {pendingCount} pending task{pendingCount !== 1 ? 's' : ''} remaining
          </p>
        </div>
        <span className="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
          Interactive
        </span>
      </div>

      {/* Add New Todo Input */}
      <form onSubmit={addTodo} className="flex gap-2">
        <input
          type="text"
          value={newTodo}
          onChange={(e) => setNewTodo(e.target.value)}
          placeholder="Add a new action item..."
          className="flex-1 px-3 py-2 text-xs rounded-xl bg-slate-800/80 border border-slate-700/60 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
        />
        <button
          type="submit"
          className="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-sm transition-colors flex items-center gap-1 cursor-pointer"
        >
          <Plus className="w-3.5 h-3.5" />
          <span>Add</span>
        </button>
      </form>

      {/* Todo List */}
      <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
        {todos.length === 0 ? (
          <p className="text-center text-xs text-slate-400 py-6">
            All caught up! No active tasks.
          </p>
        ) : (
          todos.map((todo) => (
            <div
              key={todo.id}
              className={`flex items-center justify-between p-2.5 rounded-xl border transition-all ${
                todo.completed
                  ? 'bg-slate-900/40 border-slate-800/40 text-slate-400 opacity-60'
                  : 'bg-slate-800/40 border-slate-700/40 text-slate-200 hover:border-slate-600/60'
              }`}
            >
              <div
                onClick={() => toggleTodo(todo.id)}
                className="flex items-center gap-2.5 flex-1 cursor-pointer select-none"
              >
                {todo.completed ? (
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                ) : (
                  <Circle className="w-4 h-4 text-slate-400 hover:text-indigo-400 shrink-0" />
                )}
                <span
                  className={`text-xs ${
                    todo.completed ? 'line-through text-slate-400' : ''
                  }`}
                >
                  {todo.text}
                </span>
              </div>

              <button
                onClick={() => deleteTodo(todo.id)}
                className="text-slate-400 hover:text-rose-400 p-1 rounded transition-colors ml-2"
                title="Delete"
              >
                <Trash2 className="w-3.5 h-3.5" />
              </button>
            </div>
          ))
        )}
      </div>
    </div>
  )
}
