import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { ContextMenu, ContextMenuTrigger, ContextMenuContent, ContextMenuItem } from '@/components/ui/context-menu';
import { Star, StarOff, Sparkles, Plus, Edit, X, Check, MoreVertical } from 'lucide-react';

// Dummy data for prompts
const dummyPrompts = [
  {
    id: 1,
    title: 'Client Email Follow-up',
    description: 'A polite follow-up email template for clients after a meeting.',
    content: 'Hi [Client],\nJust following up on our recent meeting... [rest of prompt]',
    tags: ['Client Emails', 'Communication'],
    context: 'Project: Acme Website',
    favorite: true,
  },
  {
    id: 2,
    title: 'UX Audit Checklist',
    description: 'Checklist for auditing a website UX.',
    content: 'Review navigation, check mobile responsiveness, ...',
    tags: ['UX Audit', 'Design'],
    context: 'General',
    favorite: false,
  },
  {
    id: 3,
    title: 'Creative Brainstorm',
    description: 'Prompt for generating creative ideas for branding.',
    content: 'Generate 10 unique branding ideas for a new eco-friendly product.',
    tags: ['Brainstorm', 'Copywriting'],
    context: 'Task: Logo Design',
    favorite: false,
  },
];

export default function PromptManagement({ auth }) {
  const [prompts, setPrompts] = useState(dummyPrompts);
  const [showConvertModal, setShowConvertModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [selectedPrompt, setSelectedPrompt] = useState(null);
  const [convertContent, setConvertContent] = useState('');
  const [optimizedContent, setOptimizedContent] = useState('');
  const [isOptimizing, setIsOptimizing] = useState(false);

  // Dummy optimize function
  const handleOptimize = () => {
    setIsOptimizing(true);
    setTimeout(() => {
      setOptimizedContent(convertContent + '\n\n[Optimized for clarity and detail!]');
      setIsOptimizing(false);
    }, 1000);
  };

  // Dummy convert to prompt
  const handleConvertToPrompt = () => {
    setPrompts([
      ...prompts,
      {
        id: prompts.length + 1,
        title: 'New Prompt',
        description: 'Converted from note/task.',
        content: optimizedContent || convertContent,
        tags: ['General'],
        context: 'General',
        favorite: false,
      },
    ]);
    setShowConvertModal(false);
    setConvertContent('');
    setOptimizedContent('');
  };

  // Dummy favorite toggle
  const toggleFavorite = (id) => {
    setPrompts(prompts.map(p => p.id === id ? { ...p, favorite: !p.favorite } : p));
  };

  // Dummy edit
  const handleEditPrompt = (prompt) => {
    setSelectedPrompt(prompt);
    setShowEditModal(true);
  };

  const handleSaveEdit = () => {
    setPrompts(prompts.map(p => p.id === selectedPrompt.id ? selectedPrompt : p));
    setShowEditModal(false);
    setSelectedPrompt(null);
  };

  // Dropdown action handlers
  const handleDropdownAction = (action, prompt) => {
    if (action === 'optimize') {
      setConvertContent(prompt.content);
      setShowConvertModal(true);
    } else if (action === 'convert') {
      setConvertContent(prompt.content);
      setShowConvertModal(true);
    } else if (action === 'edit') {
      handleEditPrompt(prompt);
    }
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Prompt Management" />
      <div className="space-y-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold text-foreground">🧠 Prompt Management</h1>
            <p className="text-muted-foreground mt-1">Save, optimize, and reuse your best AI prompts. Convert notes or tasks into prompts in one click!</p>
          </div>
          <Button onClick={() => setShowConvertModal(true)}>
            <Plus className="w-4 h-4 mr-2" /> Convert Note/Task to Prompt
          </Button>
        </div>

        {/* Prompt Library */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {prompts.map(prompt => (
            <Card key={prompt.id} className="relative group shadow-lg border border-primary/10 bg-background/90 hover:shadow-xl transition-all">
              <CardHeader className="flex flex-row items-start justify-between pb-2">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <span className="font-mono text-xs bg-muted px-2 py-1 rounded">{prompt.context}</span>
                    <button onClick={() => toggleFavorite(prompt.id)} className="ml-1" title={prompt.favorite ? 'Unfavorite' : 'Favorite'}>
                      {prompt.favorite ? (
                        <Star className="w-4 h-4 text-yellow-400 fill-yellow-400" />
                      ) : (
                        <StarOff className="w-4 h-4 text-muted-foreground" />
                      )}
                    </button>
                  </div>
                  <CardTitle className="text-lg font-semibold truncate" title={prompt.title}>{prompt.title}</CardTitle>
                  <CardDescription className="truncate text-xs">{prompt.description}</CardDescription>
                </div>
                <ContextMenu>
                  <ContextMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="ml-2 mt-1"><MoreVertical className="w-5 h-5" /></Button>
                  </ContextMenuTrigger>
                  <ContextMenuContent align="end">
                    <ContextMenuItem onClick={() => handleDropdownAction('optimize', prompt)}>
                      <Sparkles className="w-4 h-4 mr-2" /> Optimize Prompt
                    </ContextMenuItem>
                    <ContextMenuItem onClick={() => handleDropdownAction('convert', prompt)}>
                      <Plus className="w-4 h-4 mr-2" /> Convert to Reusable Prompt
                    </ContextMenuItem>
                    <ContextMenuItem onClick={() => handleDropdownAction('edit', prompt)}>
                      <Edit className="w-4 h-4 mr-2" /> Edit
                    </ContextMenuItem>
                  </ContextMenuContent>
                </ContextMenu>
              </CardHeader>
              <CardContent className="pt-0 pb-3">
                <div className="mb-2 min-h-[80px] max-h-40 overflow-auto rounded bg-muted/40 px-3 py-2 text-sm font-mono text-foreground whitespace-pre-line border border-muted">
                  {prompt.content}
                </div>
                <div className="flex flex-wrap gap-2 mb-1">
                  {prompt.tags.map(tag => (
                    <Badge key={tag}>{tag}</Badge>
                  ))}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Convert to Prompt Modal */}
        <Dialog open={showConvertModal} onOpenChange={setShowConvertModal}>
          <DialogContent className="sm:max-w-lg">
            <DialogHeader>
              <DialogTitle>Convert Note/Task to Prompt</DialogTitle>
              <DialogDescription>
                Refine your note or task into a clean, AI-usable prompt. Optionally, optimize it for clarity and effectiveness.
              </DialogDescription>
            </DialogHeader>
            <div className="mb-4">
              <Textarea
                value={convertContent}
                onChange={e => setConvertContent(e.target.value)}
                placeholder="Paste your note or task here..."
                className="min-h-[80px]"
              />
            </div>
            <div className="flex gap-2 mb-4">
              <Button variant="outline" onClick={handleOptimize} disabled={isOptimizing || !convertContent}>
                <Sparkles className="w-4 h-4 mr-1" />
                {isOptimizing ? 'Optimizing...' : 'Optimize Prompt'}
              </Button>
              {optimizedContent && (
                <Button variant="secondary" onClick={() => setConvertContent(optimizedContent)}>
                  <Check className="w-4 h-4 mr-1" /> Use Optimized
                </Button>
              )}
            </div>
            {optimizedContent && (
              <div className="mb-4">
                <label className="block text-xs font-semibold mb-1">Optimized Prompt:</label>
                <Textarea value={optimizedContent} readOnly className="min-h-[60px] bg-muted" />
              </div>
            )}
            <DialogFooter>
              <Button onClick={handleConvertToPrompt} disabled={!convertContent && !optimizedContent}>
                <Plus className="w-4 h-4 mr-2" /> Save as Prompt
              </Button>
              <Button variant="ghost" onClick={() => setShowConvertModal(false)}>
                <X className="w-4 h-4 mr-2" /> Cancel
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        {/* Edit Prompt Modal */}
        <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
          <DialogContent className="sm:max-w-lg">
            <DialogHeader>
              <DialogTitle>Edit Prompt</DialogTitle>
            </DialogHeader>
            {selectedPrompt && (
              <div className="space-y-3">
                <Input
                  value={selectedPrompt.title}
                  onChange={e => setSelectedPrompt({ ...selectedPrompt, title: e.target.value })}
                  placeholder="Prompt Title"
                />
                <Input
                  value={selectedPrompt.description}
                  onChange={e => setSelectedPrompt({ ...selectedPrompt, description: e.target.value })}
                  placeholder="Description"
                />
                <Textarea
                  value={selectedPrompt.content}
                  onChange={e => setSelectedPrompt({ ...selectedPrompt, content: e.target.value })}
                  placeholder="Prompt Content"
                  className="min-h-[80px]"
                />
                <Input
                  value={selectedPrompt.context}
                  onChange={e => setSelectedPrompt({ ...selectedPrompt, context: e.target.value })}
                  placeholder="Context (e.g., Project, Task, General)"
                />
                <Input
                  value={selectedPrompt.tags.join(', ')}
                  onChange={e => setSelectedPrompt({ ...selectedPrompt, tags: e.target.value.split(',').map(t => t.trim()) })}
                  placeholder="Tags (comma separated)"
                />
              </div>
            )}
            <DialogFooter>
              <Button onClick={handleSaveEdit}>
                <Check className="w-4 h-4 mr-2" /> Save
              </Button>
              <Button variant="ghost" onClick={() => setShowEditModal(false)}>
                <X className="w-4 h-4 mr-2" /> Cancel
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AuthenticatedLayout>
  );
}
