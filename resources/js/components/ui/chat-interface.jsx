import React, { useState, useRef, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    ArrowRight,
    Copy,
    Check,
    Paperclip,
    Command,
    Trash2,
    MoreHorizontal,
    FileText,
    Building,
    TrendingUp,
    BarChart3,
    RefreshCw,
    Edit3,
    ChevronDown
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export default function ChatInterface({
    messages = [],
    onSendMessage,
    isLoading = false,
    presetPrompts = [],
    onPresetClick,
    context = 'general',
    presetChatFlow = null,
    presetChatStep = 0,
    recentChats = [],
    onChatSelect = null,
    onDeleteChat = null
}) {
    const [inputValue, setInputValue] = useState('');
    const messagesEndRef = useRef(null);
    const inputRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (inputValue.trim() && !isLoading) {
            onSendMessage(inputValue);
            setInputValue('');
        }
    };

    const handlePresetClick = (prompt) => {
        onPresetClick(prompt);
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    const getContextPrompts = (context) => {
        // Use the presetPrompts passed from parent if available, otherwise fall back to defaults
        if (presetPrompts && presetPrompts.length > 0) {
            return presetPrompts;
        }

        // Fallback prompts if none provided
        const fallbackPrompts = {
            general: [
                "Show me invoices ranked by amount",
                "Generate a project report",
                "Show me all pending tasks for this week",
                "Create a summary of client communications"
            ],
            projects: [
                "List all active projects with their progress",
                "Show me projects due this month",
                "Generate a project timeline report",
                "Find projects with overdue tasks"
            ],
            clients: [
                "List all clients with their contact information",
                "Show me clients with outstanding invoices",
                "Generate a client satisfaction report",
                "Find clients with recent activity"
            ],
            'bobbi-flow': [
                "Show me the current workflow status",
                "List all automated processes",
                "Generate a workflow efficiency report",
                "Find bottlenecks in current processes"
            ],
            calendar: [
                "Show me all meetings this week",
                "List upcoming deadlines",
                "Generate a calendar summary",
                "Find conflicting appointments"
            ],
            analytics: [
                "Generate a revenue report for this quarter",
                "Show me performance metrics",
                "Create a trend analysis report",
                "Find areas for improvement"
            ]
        };
        return fallbackPrompts[context] || fallbackPrompts.general;
    };

    const renderSystemMessage = (message) => {
        if (message.type === 'processing') {
            return (
                <div className="flex items-start space-x-3 mb-6">
                    <div className="w-8 h-8 bg-[#d1ff75] rounded-lg flex items-center justify-center flex-shrink-0">
                        <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                            <span className="text-xs font-bold text-[#d1ff75] orbitron">B</span>
                        </div>
                    </div>
                    <div className="max-w-[70%] bg-card rounded-lg p-4 border border-border">
                        <div className="space-y-3">
                            <div className="flex items-center space-x-2">
                                <Check className="w-4 h-4 text-green-500" />
                                <span className="text-sm font-medium">Intent: {message.intent}</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Check className="w-4 h-4 text-green-500" />
                                <span className="text-sm font-medium">Connecting relevant agent: {message.agent}</span>
                                <div className="w-6 h-6 bg-blue-500 rounded text-white text-xs flex items-center justify-center font-bold">
                                    RS
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Check className="w-4 h-4 text-green-500" />
                                <span className="text-sm font-medium">Reading connected entities:</span>
                                <div className="flex space-x-2">
                                    <div className="w-6 h-6 bg-blue-500 rounded text-white text-xs flex items-center justify-center">X</div>
                                    <div className="w-6 h-6 bg-green-500 rounded text-white text-xs flex items-center justify-center">S</div>
                                    <div className="w-6 h-6 bg-blue-600 rounded text-white text-xs flex items-center justify-center">I</div>
                                    <div className="w-6 h-6 bg-orange-500 rounded text-white text-xs flex items-center justify-center">A</div>
                                    <div className="w-6 h-6 bg-purple-500 rounded text-white text-xs flex items-center justify-center">S</div>
                                    <div className="w-6 h-6 bg-gray-500 rounded text-white text-xs flex items-center justify-center">+5</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        if (message.type === 'response') {
            return (
                <div className="flex items-start space-x-3 mb-6">
                    <div className="w-8 h-8 bg-[#d1ff75] rounded-lg flex items-center justify-center flex-shrink-0">
                        <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                            <span className="text-xs font-bold text-[#d1ff75] orbitron">B</span>
                        </div>
                    </div>
                    <div className="max-w-[70%] bg-card rounded-lg p-4 border border-border">
                        <div className="mb-3">
                            <p className="text-sm text-muted-foreground">{message.summary}</p>
                            <div className="flex space-x-2 mt-2">
                                <div className="w-6 h-6 bg-blue-500 rounded text-white text-xs flex items-center justify-center">X</div>
                                <div className="w-6 h-6 bg-purple-500 rounded text-white text-xs flex items-center justify-center">S</div>
                            </div>
                        </div>

                        {message.data && (
                            <div className="bg-muted rounded-lg p-4">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="border-b border-border">
                                                <th className="text-left py-2 font-medium">Invoice No.</th>
                                                <th className="text-left py-2 font-medium">Client</th>
                                                <th className="text-left py-2 font-medium">Due Date</th>
                                                <th className="text-left py-2 font-medium">Amount</th>
                                                <th className="text-left py-2 font-medium">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {message.data.map((item, index) => (
                                                <tr key={index} className="border-b border-border/50">
                                                    <td className="py-2">
                                                        <div className="flex items-center space-x-2">
                                                            <FileText className="w-4 h-4 text-red-500" />
                                                            <span>{item.invoiceNo}</span>
                                                        </div>
                                                    </td>
                                                    <td className="py-2">{item.client}</td>
                                                    <td className="py-2">{item.dueDate}</td>
                                                    <td className="py-2 font-medium">{item.amount}</td>
                                                    <td className="py-2">
                                                        <div className="flex items-center space-x-2">
                                                            <div className={`w-2 h-2 rounded-full ${item.status === 'Open' ? 'bg-green-500' : 'bg-red-500'}`}></div>
                                                            <span>{item.status}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="flex items-center justify-between mt-4">
                                    <div className="flex space-x-2">
                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                            <RefreshCw className="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                            <Copy className="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                            <Edit3 className="w-4 h-4" />
                                        </Button>
                                    </div>
                                    <Button variant="outline" size="sm" className="text-xs">
                                        Open in app
                                        <ChevronDown className="w-3 h-3 ml-1" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            );
        }

        // Handle error messages
        if (message.type === 'error') {
            return (
                <div className="flex items-start space-x-3 mb-6">
                    <div className="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                            <span className="text-xs font-bold text-red-500">!</span>
                        </div>
                    </div>
                    <div className="max-w-[70%] bg-red-50 rounded-lg p-4 border border-red-200">
                        <p className="text-sm text-red-700 whitespace-pre-wrap break-words">{message.content}</p>
                    </div>
                </div>
            );
        }

        // Handle system messages (preset chat flows)
        if (message.type === 'system') {
            return (
                <div className="flex items-start space-x-3 mb-6">
                    <div className="w-8 h-8 bg-[#d1ff75] rounded-lg flex items-center justify-center flex-shrink-0">
                        <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                            <span className="text-xs font-bold text-[#d1ff75] orbitron">B</span>
                        </div>
                    </div>
                    <div className="max-w-[70%] bg-card rounded-lg p-4 border border-border">
                        <p className="text-sm whitespace-pre-wrap break-words mb-3">{message.content}</p>
                        {message.options && (
                            <div className="flex flex-wrap gap-2 mt-3">
                                {message.options.map((option, index) => (
                                    <Button
                                        key={index}
                                        variant="outline"
                                        size="sm"
                                        onClick={() => {
                                            // Handle option selection
                                            if (onSendMessage) {
                                                onSendMessage(option);
                                            }
                                        }}
                                        className="text-xs px-3 py-1 h-auto"
                                    >
                                        {option}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            );
        }

        // Default system message
        return (
            <div className="flex items-start space-x-3 mb-6">
                <div className="w-8 h-8 bg-[#d1ff75] rounded-lg flex items-center justify-center flex-shrink-0">
                    <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                        <span className="text-xs font-bold text-[#d1ff75] orbitron">B</span>
                    </div>
                </div>
                <div className="max-w-[70%] bg-card rounded-lg p-4 border border-border">
                    <p className="text-sm whitespace-pre-wrap break-words">{message.content}</p>
                </div>
            </div>
        );
    };

    const renderUserMessage = (message) => {
        return (
            <div className="flex items-start space-x-3 mb-6 justify-end">
                <div className="max-w-[70%]">
                    <div className="bg-gradient-to-br from-[#d1ff75]/20 to-[#d1ff75]/10 rounded-lg p-4 border border-[#d1ff75]/20 shadow-sm">
                        <p className="text-sm text-foreground whitespace-pre-wrap break-words">{message.content}</p>
                    </div>
                </div>
                <div className="w-8 h-8 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-sm font-medium text-primary-foreground">
                        {message.user?.charAt(0) || 'U'}
                    </span>
                </div>
            </div>
        );
    };

    return (
        <div className="flex flex-col h-[90vh] bg-background">
            {/* Chat Messages */}
            <div className="flex-1 overflow-y-auto p-6 space-y-4">
                {messages.length === 0 ? (
                    <div className="text-center py-8">
                        <h2 className="text-2xl font-bold text-foreground mb-2">Hi, there!</h2>
                        <p className="text-muted-foreground mb-8">What should we work on today?</p>

                        {/* Suggested Prompts */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto w-full px-4">
                            {getContextPrompts(context).map((prompt, index) => {
                                const icons = [FileText, Building, TrendingUp, BarChart3];
                                const IconComponent = icons[index % icons.length] || FileText;

                                return (
                                    <Button
                                        key={index}
                                        variant="outline"
                                        className="group h-auto min-h-[80px] p-4 justify-start text-left hover:bg-[#d1ff75]/20 hover:text-foreground border-border hover:border-[#d1ff75]/40 transition-all duration-200 cursor-pointer w-full"
                                        onClick={() => handlePresetClick(prompt)}
                                    >
                                        <IconComponent className="w-5 h-5 mr-3 text-muted-foreground group-hover:text-foreground transition-colors duration-200 flex-shrink-0 mt-1" />
                                        <div className="min-w-0 flex-1 text-left flex items-start">
                                            <div className="font-medium text-sm leading-5">{prompt}</div>
                                        </div>
                                    </Button>
                                );
                            })}
                        </div>

                        {/* Recent Chats */}
                        {recentChats.length > 0 && (
                            <div className="mt-8 max-w-4xl mx-auto w-full px-4">
                                <h3 className="text-sm font-semibold text-muted-foreground mb-4">Recent Chats</h3>
                                <div className="space-y-2">
                                    {recentChats.slice(0, 5).map((chat) => (
                                        <div
                                            key={chat.id}
                                            className="group relative"
                                        >
                                            <Button
                                                variant="ghost"
                                                className="w-full justify-start text-left hover:bg-[#d1ff75]/10 hover:text-foreground border-border hover:border-[#d1ff75]/20 transition-all duration-200 cursor-pointer h-auto p-3"
                                                onClick={() => onChatSelect && onChatSelect(chat.id)}
                                            >
                                                <div className="flex items-center justify-between w-full">
                                                    <div className="flex items-center space-x-3 flex-1 min-w-0">
                                                        <div className="w-8 h-8 bg-[#d1ff75]/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                                            <span className="text-xs font-bold text-[#d1ff75]">C</span>
                                                        </div>
                                                        <div className="min-w-0 flex-1 text-left">
                                                            <div className="font-medium text-sm leading-5 truncate max-w-[300px]">{chat.full_title || chat.title}</div>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <div className="text-xs text-muted-foreground flex-shrink-0">
                                                            {chat.last_activity_at || 'N/A'}
                                                        </div>
                                                        {onDeleteChat && (
                                                            <DropdownMenu>
                                                                <DropdownMenuTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        className="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
                                                                        onClick={(e) => e.stopPropagation()}
                                                                    >
                                                                        <MoreHorizontal className="h-3 w-3" />
                                                                    </Button>
                                                                </DropdownMenuTrigger>
                                                                <DropdownMenuContent align="end">
                                                                    <DropdownMenuItem
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            if (confirm('Are you sure you want to delete this chat?')) {
                                                                                onDeleteChat(chat.id);
                                                                            }
                                                                        }}
                                                                        className="text-destructive"
                                                                    >
                                                                        <Trash2 className="h-3 w-3 mr-2" />
                                                                        Delete
                                                                    </DropdownMenuItem>
                                                                </DropdownMenuContent>
                                                            </DropdownMenu>
                                                        )}
                                                    </div>
                                                </div>
                                            </Button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {messages.map((message, index) => (
                            <div key={index}>
                                {message.role === 'user' ? renderUserMessage(message) : renderSystemMessage(message)}
                            </div>
                        ))}
                        {isLoading && (
                            <div className="flex items-start space-x-3 mb-6">
                                <div className="w-8 h-8 bg-[#d1ff75] rounded-lg flex items-center justify-center flex-shrink-0">
                                    <div className="w-4 h-4 bg-background rounded-full flex items-center justify-center">
                                        <span className="text-xs font-bold text-[#d1ff75] orbitron">B</span>
                                    </div>
                                </div>
                                <div className="max-w-[70%] bg-card rounded-lg p-4 border border-border">
                                    <div className="flex space-x-2">
                                        <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce"></div>
                                        <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style={{ animationDelay: '0.1s' }}></div>
                                        <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style={{ animationDelay: '0.2s' }}></div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Input Area */}
            <div className="border-t border-border p-6">
                <form onSubmit={handleSubmit} className="flex items-center space-x-4">
                    <div className="flex-1 relative">
                        <Input
                            ref={inputRef}
                            value={inputValue}
                            onChange={(e) => setInputValue(e.target.value)}
                            placeholder={presetChatFlow ? "Enter your response..." : "Ask a question or make a request..."}
                            className="w-full h-12 pl-4 pr-12 text-lg border-2 border-border focus:border-primary"
                            disabled={isLoading}
                        />
                        <div className="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                <Paperclip className="w-4 h-4" />
                            </Button>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                <Command className="w-4 h-4" />
                            </Button>
                            <Button
                                type="submit"
                                size="sm"
                                className="h-8 w-8 p-0 bg-muted hover:bg-muted/80 rounded-full"
                                disabled={!inputValue.trim() || isLoading}
                            >
                                <ArrowRight className="w-4 h-4 text-muted-foreground" />
                            </Button>
                        </div>
                    </div>
                </form>

                {/* Disclaimer */}
                <div className="mt-4 text-center text-xs text-muted-foreground">
                    Bobbi is currently in Beta. Some information produced may be inaccurate or incomplete.
                </div>
            </div>
        </div>
    );
}
