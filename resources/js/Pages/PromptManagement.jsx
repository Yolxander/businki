import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Star, StarOff, Sparkles, Plus, Edit, X, Check, MoreVertical, Save, Brain, Copy, CheckCircle } from 'lucide-react';

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
  const [copiedPromptId, setCopiedPromptId] = useState(null);

  // Dummy optimize function
  const handleOptimize = () => {
    setIsOptimizing(true);
    setTimeout(() => {
      setOptimizedContent(convertContent + '\n\n[Optimized for clarity and detail!]');
      setIsOptimizing(false);
    }, 1000);
  };

  // Copy prompt content function
  const handleCopyPrompt = async (prompt) => {
    try {
      await navigator.clipboard.writeText(prompt.content);
      setCopiedPromptId(prompt.id);
      setTimeout(() => setCopiedPromptId(null), 2000);
    } catch (err) {
      console.error('Failed to copy prompt:', err);
    }
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
            <Plus className="w-4 h-4 mr-2" /> Add Prompt
          </Button>
        </div>

        {/* Prompt Library */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                      <button
                        onClick={() => handleCopyPrompt(prompt)}
                        className="ml-1 p-1 rounded hover:bg-muted transition-colors"
                        title={copiedPromptId === prompt.id ? 'Copied!' : 'Copy prompt content'}
                      >
                        {copiedPromptId === prompt.id ? (
                          <CheckCircle className="w-4 h-4 text-green-500" />
                        ) : (
                          <Copy className="w-4 h-4 text-muted-foreground hover:text-foreground" />
                        )}
                      </button>
                    </div>
                    <CardTitle className="text-lg font-semibold truncate" title={prompt.title}>{prompt.title}</CardTitle>
                    <CardDescription className="truncate text-xs">{prompt.description}</CardDescription>
                  </div>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="ml-2 mt-1"><MoreVertical className="w-5 h-5" /></Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => handleDropdownAction('optimize', prompt)}>
                      <Sparkles className="w-4 h-4 mr-2" /> Optimized
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => handleDropdownAction('convert', prompt)}>
                      <Plus className="w-4 h-4 mr-2" /> Make Reusable
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => handleDropdownAction('edit', prompt)}>
                      <Edit className="w-4 h-4 mr-2" /> Edit
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </CardHeader>
              <CardContent className="pt-0 pb-3">
                <div className="mb-2 min-h-[140px] max-h-72 overflow-auto rounded bg-muted/40 px-3 py-2 text-sm font-mono text-foreground whitespace-pre-line border border-muted">
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

        {/* Add Prompt Modal */}
        <Dialog open={showConvertModal} onOpenChange={setShowConvertModal}>
          <DialogContent className="sm:max-w-3xl">
            <DialogHeader className="text-left">
              <div className="flex items-center gap-3 mb-2">
                <div className="p-2 bg-purple-100 rounded-lg">
                  <Brain className="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <DialogTitle className="text-xl font-semibold">AI Prompt Creation</DialogTitle>
                  <DialogDescription className="text-sm text-muted-foreground">
                    Create a new prompt for your AI workflow. Focus on the content first, then add details.
                  </DialogDescription>
                </div>
              </div>
            </DialogHeader>

            <Tabs defaultValue="content" className="w-full">
              <TabsList className="grid w-full grid-cols-2">
                <TabsTrigger value="content">Prompt Content</TabsTrigger>
                <TabsTrigger value="details">Prompt Details</TabsTrigger>
              </TabsList>

              <TabsContent value="content" className="space-y-6 mt-6">
                {/* Main Content Section */}
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="prompt-content" className="block text-sm font-medium text-foreground mb-2">
                      Prompt Content *
                    </Label>
                    <Textarea
                      id="prompt-content"
                      value={convertContent}
                      onChange={e => setConvertContent(e.target.value)}
                      placeholder="Describe what you want the AI to do or generate..."
                      className="min-h-[200px] resize-none"
                    />
                  </div>

                  {/* Optimization Section */}
                  <div className="space-y-3">
                    <Label className="block text-sm font-medium text-foreground">
                      AI Optimization
                    </Label>
                    <div className="flex gap-2">
                      <Button variant="outline" onClick={handleOptimize} disabled={isOptimizing || !convertContent}>
                        <Sparkles className="w-4 h-4 mr-2" />
                        {isOptimizing ? 'Optimizing...' : 'Optimize Prompt'}
                      </Button>
                      {optimizedContent && (
                        <Button variant="secondary" onClick={() => setConvertContent(optimizedContent)}>
                          <Check className="w-4 h-4 mr-2" /> Use Optimized
                        </Button>
                      )}
                    </div>
                    {optimizedContent && (
                      <div className="mt-3">
                        <Label className="block text-sm font-medium text-foreground mb-2">Optimized Version:</Label>
                        <Textarea
                          value={optimizedContent}
                          readOnly
                          className="min-h-[120px] bg-muted/50 border-muted"
                        />
                      </div>
                    )}
                  </div>
                </div>
              </TabsContent>

              <TabsContent value="details" className="space-y-6 mt-6">
                {/* Prompt Details Section */}
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="prompt-title" className="block text-sm font-medium text-foreground mb-2">
                      Prompt Title
                    </Label>
                    <Input
                      id="prompt-title"
                      placeholder="Enter a descriptive title"
                      className="w-full"
                    />
                  </div>

                  <div>
                    <Label htmlFor="prompt-description" className="block text-sm font-medium text-foreground mb-2">
                      Description
                    </Label>
                    <Input
                      id="prompt-description"
                      placeholder="Brief description of what this prompt does"
                      className="w-full"
                    />
                  </div>

                  <div>
                    <Label htmlFor="prompt-context" className="block text-sm font-medium text-foreground mb-2">
                      Context
                    </Label>
                    <Input
                      id="prompt-context"
                      placeholder="e.g., Project, Task, General"
                      className="w-full"
                    />
                  </div>

                  <div>
                    <Label htmlFor="prompt-tags" className="block text-sm font-medium text-foreground mb-2">
                      Tags
                    </Label>
                    <Input
                      id="prompt-tags"
                      placeholder="Tags (comma separated)"
                      className="w-full"
                    />
                  </div>
                </div>
              </TabsContent>
            </Tabs>

            <DialogFooter className="mt-6">
              <Button variant="ghost" onClick={() => setShowConvertModal(false)}>
                <X className="w-4 h-4 mr-2" /> Cancel
              </Button>
              <Button onClick={handleConvertToPrompt} disabled={!convertContent}>
                <Save className="w-4 h-4 mr-2" /> Create Prompt
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        {/* Edit Prompt Modal */}
        <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
          <DialogContent className="sm:max-w-3xl">
            <DialogHeader className="text-left">
              <div className="flex items-center gap-3 mb-2">
                <div className="p-2 bg-purple-100 rounded-lg">
                  <Brain className="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <DialogTitle className="text-xl font-semibold">Edit Prompt</DialogTitle>
                  <DialogDescription className="text-sm text-muted-foreground">
                    Update your prompt content and details. Focus on the content first, then adjust metadata.
                  </DialogDescription>
                </div>
              </div>
            </DialogHeader>

            {selectedPrompt && (
              <Tabs defaultValue="content" className="w-full">
                <TabsList className="grid w-full grid-cols-2">
                  <TabsTrigger value="content">Prompt Content</TabsTrigger>
                  <TabsTrigger value="details">Prompt Details</TabsTrigger>
                </TabsList>

                <TabsContent value="content" className="space-y-6 mt-6">
                  {/* Main Content Section */}
                  <div className="space-y-4">
                    <div>
                      <Label htmlFor="edit-prompt-content" className="block text-sm font-medium text-foreground mb-2">
                        Prompt Content *
                      </Label>
                      <Textarea
                        id="edit-prompt-content"
                        value={selectedPrompt.content}
                        onChange={e => setSelectedPrompt({ ...selectedPrompt, content: e.target.value })}
                        placeholder="Describe what you want the AI to do or generate..."
                        className="min-h-[200px] resize-none"
                      />
                    </div>

                    {/* Optimization Section */}
                    <div className="space-y-3">
                      <Label className="block text-sm font-medium text-foreground">
                        AI Optimization
                      </Label>
                      <div className="flex gap-2">
                        <Button
                          variant="outline"
                          onClick={() => {
                            setConvertContent(selectedPrompt.content);
                            setShowConvertModal(true);
                            setShowEditModal(false);
                          }}
                        >
                          <Sparkles className="w-4 h-4 mr-2" />
                          Optimize Prompt
                        </Button>
                      </div>
                    </div>
                  </div>
                </TabsContent>

                <TabsContent value="details" className="space-y-6 mt-6">
                  {/* Prompt Details Section */}
                  <div className="space-y-4">
                    <div>
                      <Label htmlFor="edit-prompt-title" className="block text-sm font-medium text-foreground mb-2">
                        Prompt Title
                      </Label>
                      <Input
                        id="edit-prompt-title"
                        value={selectedPrompt.title}
                        onChange={e => setSelectedPrompt({ ...selectedPrompt, title: e.target.value })}
                        placeholder="Enter a descriptive title"
                        className="w-full"
                      />
                    </div>

                    <div>
                      <Label htmlFor="edit-prompt-description" className="block text-sm font-medium text-foreground mb-2">
                        Description
                      </Label>
                      <Input
                        id="edit-prompt-description"
                        value={selectedPrompt.description}
                        onChange={e => setSelectedPrompt({ ...selectedPrompt, description: e.target.value })}
                        placeholder="Brief description of what this prompt does"
                        className="w-full"
                      />
                    </div>

                    <div>
                      <Label htmlFor="edit-prompt-context" className="block text-sm font-medium text-foreground mb-2">
                        Context
                      </Label>
                      <Input
                        id="edit-prompt-context"
                        value={selectedPrompt.context}
                        onChange={e => setSelectedPrompt({ ...selectedPrompt, context: e.target.value })}
                        placeholder="e.g., Project, Task, General"
                        className="w-full"
                      />
                    </div>

                    <div>
                      <Label htmlFor="edit-prompt-tags" className="block text-sm font-medium text-foreground mb-2">
                        Tags
                      </Label>
                      <Input
                        id="edit-prompt-tags"
                        value={selectedPrompt.tags.join(', ')}
                        onChange={e => setSelectedPrompt({ ...selectedPrompt, tags: e.target.value.split(',').map(t => t.trim()) })}
                        placeholder="Tags (comma separated)"
                        className="w-full"
                      />
                    </div>
                  </div>
                </TabsContent>
              </Tabs>
            )}

            <DialogFooter className="mt-6">
              <Button variant="ghost" onClick={() => setShowEditModal(false)}>
                <X className="w-4 h-4 mr-2" /> Cancel
              </Button>
              <Button onClick={handleSaveEdit}>
                <Save className="w-4 h-4 mr-2" /> Update Prompt
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AuthenticatedLayout>
  );
}
