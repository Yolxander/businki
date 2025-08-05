import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
  ArrowLeft, Pause, Play, CheckCircle, Send, X, Brain, User, ListTodo
} from 'lucide-react';

export default function ZenMode({ zenTask, auth }) {
  const [zenTimer, setZenTimer] = useState(0);
  const [zenTimerActive, setZenTimerActive] = useState(true);
  const [zenNotes, setZenNotes] = useState('');
  const [zenSubtaskProgress, setZenSubtaskProgress] = useState({});
  const [zenAiTip, setZenAiTip] = useState('');
  const [showExitModal, setShowExitModal] = useState(false);
  const timerRef = useRef(null);

  useEffect(() => {
    if (zenTimerActive) {
      timerRef.current = setInterval(() => {
        setZenTimer(prev => prev + 1);
      }, 1000);
    } else {
      if (timerRef.current) clearInterval(timerRef.current);
    }
    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, [zenTimerActive]);

  useEffect(() => {
    // Generate AI tip on mount
    const tips = [
      "Take a deep breath and start with what feels most natural",
      "Remember to celebrate small wins as you complete subtasks",
      "If you get stuck, try switching to a different subtask",
      "Your progress is valuable - every step counts",
      "Stay hydrated and take gentle breaks when needed",
      "Focus on the process, not just the outcome",
      "You're doing great - trust your ability to figure things out",
      "Break complex tasks into smaller, manageable pieces",
      "Remember why this work matters to you",
      "Be kind to yourself - perfection is not required"
    ];
    setZenAiTip(tips[Math.floor(Math.random() * tips.length)]);
  }, []);

  const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  const toggleSubtask = (subtaskId) => {
    setZenSubtaskProgress(prev => ({
      ...prev,
      [subtaskId]: !prev[subtaskId]
    }));
  };

  if (!zenTask) return <div className="flex items-center justify-center h-screen text-lg">No task selected for Zen Mode.</div>;

  return (
    <>
      <Head title={`Zen Mode - ${zenTask.title}`} />
      <div className="h-screen flex flex-col bg-background">
        {/* Zen Mode Header */}
        <div className="flex-shrink-0 p-6 border-b border-border/50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <Button variant="outline" size="sm" onClick={() => setShowExitModal(true)}>
                <ArrowLeft className="w-4 h-4 mr-2" />
                Back
              </Button>
              <div>
                <h1 className="text-xl font-semibold text-foreground">{zenTask.title}</h1>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              <div className="text-center">
                <div className="text-2xl font-mono font-bold text-foreground">{formatTime(zenTimer)}</div>
                <div className="text-xs text-muted-foreground">Zen Time</div>
              </div>
              <div className="flex items-center space-x-2">
                <Button variant="ghost" size="icon" onClick={() => setZenTimerActive(!zenTimerActive)} title={zenTimerActive ? 'Pause Timer' : 'Resume Timer'}>
                  {zenTimerActive ? <Pause className="w-5 h-5" /> : <Play className="w-5 h-5" />}
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Zen Mode Content */}
        <div className="flex-1 flex overflow-hidden">
          {/* Left Panel - Bobbi AI Chat */}
          <div className="w-2/5 flex flex-col min-h-0">
            <div className="h-full flex flex-col">
              {/* Chat Header */}
              <div className="flex-shrink-0 p-4 border-b border-border/30">
                <div className="flex items-center space-x-3">
                  <div className="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                    <Brain className="w-4 h-4 text-primary" />
                  </div>
                  <div>
                    <h3 className="font-medium text-foreground">Bobbi AI</h3>
                    <p className="text-xs text-muted-foreground">Your Zen companion</p>
                  </div>
                </div>
              </div>
              {/* Chat Messages */}
              <div className="flex-1 p-4 space-y-4 overflow-y-auto">
                {/* Initial AI Message */}
                <div className="flex items-start space-x-3">
                  <div className="flex-shrink-0 w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                    <Brain className="w-4 h-4 text-primary" />
                  </div>
                  <div className="flex-1">
                    <p className="text-sm text-foreground leading-relaxed">"{zenAiTip}"</p>
                  </div>
                </div>
                {/* Second AI Message */}
                <div className="flex items-start space-x-3">
                  <div className="flex-shrink-0 w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                    <Brain className="w-4 h-4 text-primary" />
                  </div>
                  <div className="flex-1">
                    <p className="text-sm text-foreground leading-relaxed">
                      I'm here to support your workflow and help you stay focused. Feel free to share any thoughts, concerns, or ideas as we work through this task together.
                      <br /><br />
                      Remember to celebrate small wins as you complete subtasks. Every step forward is progress, no matter how small.
                    </p>
                  </div>
                </div>
                {/* User Message (if notes exist) */}
                {zenNotes.trim() && (
                  <div className="flex items-start space-x-3 justify-end">
                    <div className="flex-1 max-w-xs">
                      <p className="text-sm text-foreground leading-relaxed bg-primary/10 p-3 rounded-lg">{zenNotes}</p>
                    </div>
                    <div className="flex-shrink-0 w-8 h-8 bg-muted rounded-full flex items-center justify-center">
                      <User className="w-4 h-4 text-muted-foreground" />
                    </div>
                  </div>
                )}
                {/* AI Response to User Notes */}
                {zenNotes.trim() && (
                  <div className="flex items-start space-x-3">
                    <div className="flex-shrink-0 w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                      <Brain className="w-4 h-4 text-primary" />
                    </div>
                    <div className="flex-1">
                      <p className="text-sm text-foreground leading-relaxed">
                        Thank you for sharing your thoughts. I've noted your insights and will keep them in mind as we continue working through this task together.
                      </p>
                    </div>
                  </div>
                )}
              </div>
              {/* Chat Input */}
              <div className="flex-shrink-0 p-4 border-t border-border/30">
                <div className="flex items-center space-x-3">
                  <Textarea
                    placeholder="Share your thoughts with Bobbi..."
                    value={zenNotes}
                    onChange={(e) => setZenNotes(e.target.value)}
                    className="flex-1 min-h-[60px] resize-none border border-border focus:border-primary text-sm"
                  />
                  <Button variant="ghost" size="sm" className="flex-shrink-0">
                    <Send className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </div>
          </div>
          {/* Right Panel - Subtasks */}
          <div className="flex-1 border-l border-border/50 bg-muted/20">
            <div className="p-6">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center space-x-2">
                    <ListTodo className="w-4 h-4 text-primary" />
                    <span>Subtasks</span>
                  </CardTitle>
                  <CardDescription>
                    {Object.values(zenSubtaskProgress).filter(Boolean).length}/{zenTask.subtasks.length} completed
                    {zenTask.dueDate && (
                      <span> • Due in {Math.ceil((new Date(zenTask.dueDate) - new Date()) / (1000 * 60 * 60 * 24))} days</span>
                    )}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {zenTask.subtasks.map((subtask, index) => (
                      <div key={subtask.id} className="flex items-center space-x-3">
                        <Checkbox
                          checked={zenSubtaskProgress[subtask.id] || false}
                          onCheckedChange={() => toggleSubtask(subtask.id)}
                          className="data-[state=checked]:bg-primary data-[state=checked]:border-primary"
                        />
                        <span className={`text-sm ${zenSubtaskProgress[subtask.id] ? 'line-through text-muted-foreground' : 'text-foreground'}`}>{subtask.text}</span>
                      </div>
                    ))}
                    {zenTask.subtasks.length === 0 && (
                      <p className="text-sm text-muted-foreground text-center">No subtasks defined</p>
                    )}
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
        {/* Bottom Controls */}
        <div className="flex-shrink-0 border-t border-border/50 bg-background/95">
          <div className="flex items-center justify-between px-6 py-4">
            <div className="flex items-center space-x-4">
              <Button variant="default" size="lg" className="flex items-center space-x-2 bg-lime-600 hover:bg-lime-700">
                <CheckCircle className="w-4 h-4" />
                <span>Mark Complete</span>
              </Button>
              <Button variant="outline" size="lg" className="flex items-center space-x-2">
                <Send className="w-4 h-4" />
                <span>Send Update</span>
              </Button>
            </div>
            <div className="flex items-center space-x-4">
              <Button variant="ghost" size="sm" onClick={() => setZenTimerActive(!zenTimerActive)} className="flex items-center space-x-2">
                {zenTimerActive ? <Pause className="w-4 h-4" /> : <Play className="w-4 h-4" />}
                <span>{zenTimerActive ? 'Pause' : 'Resume'}</span>
              </Button>
            </div>
          </div>
        </div>
        {/* Exit Modal */}
        {showExitModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="bg-background rounded-lg shadow-xl p-6 w-96 border border-border">
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h3 className="text-lg font-semibold">Exit Zen Mode?</h3>
                  <Button variant="ghost" size="sm" onClick={() => setShowExitModal(false)}>
                    <X className="w-4 h-4" />
                  </Button>
                </div>
                <div className="space-y-3 text-sm">
                  <div className="flex items-center space-x-2">
                    <CheckCircle className="w-4 h-4 text-green-500" />
                    <span>Task: {zenTask.title}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Pause className="w-4 h-4 text-blue-500" />
                    <span>Time Spent: {formatTime(zenTimer)}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Brain className="w-4 h-4 text-purple-500" />
                    <span>AI Summary: "Work in progress, notes saved"</span>
                  </div>
                </div>
                <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                  <span>Notes saved</span>
                </div>
                <div className="flex items-center space-x-3 pt-4">
                  <Button variant="outline" onClick={() => setShowExitModal(false)} className="flex-1">Cancel</Button>
                  <Button variant="default" onClick={() => window.history.back()} className="flex-1">Exit Zen Mode</Button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
