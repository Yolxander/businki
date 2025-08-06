import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    MessageSquare,
    Plus,
    MoreHorizontal,
    Clock,
    ChevronRight,
    Trash2,
    Edit3
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export default function RecentChatsSidebar({
    currentChatId,
    onChatSelect,
    onNewChat,
    onDeleteChat,
    onEditChat,
    chatType = 'general',
    collapsed = false
}) {
    const [recentChats, setRecentChats] = useState([]);
    const [loading, setLoading] = useState(false);
    const [hasMore, setHasMore] = useState(false);

    const loadRecentChats = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/api/chats/recent?type=${chatType}&limit=5`);
            const data = await response.json();
            setRecentChats(data.data.chats);
            setHasMore(data.data.has_more);
        } catch (error) {
            console.error('Error loading recent chats:', error);
        } finally {
            setLoading(false);
        }
    };

    const loadMoreChats = async () => {
        try {
            const response = await fetch(`/api/chats/all?type=${chatType}&page=1&per_page=10`);
            const data = await response.json();
            setRecentChats(data.data.data);
            setHasMore(data.data.next_page_url !== null);
        } catch (error) {
            console.error('Error loading more chats:', error);
        }
    };

    useEffect(() => {
        loadRecentChats();
    }, [chatType]);

    const handleChatSelect = (chatId) => {
        onChatSelect(chatId);
    };

    const handleDeleteChat = async (chatId, e) => {
        e.stopPropagation();
        if (confirm('Are you sure you want to delete this chat?')) {
            try {
                await fetch(`/api/chats/${chatId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                loadRecentChats(); // Reload the list
            } catch (error) {
                console.error('Error deleting chat:', error);
            }
        }
    };

    const getTypeIcon = (type) => {
        const icons = {
            general: MessageSquare,
            projects: '📁',
            clients: '👥',
            'bobbi-flow': '🔄',
            calendar: '📅',
            system: '⚙️',
            analytics: '📊'
        };
        return icons[type] || MessageSquare;
    };

    const getTypeLabel = (type) => {
        const labels = {
            general: 'General',
            projects: 'Projects',
            clients: 'Clients',
            'bobbi-flow': 'Bobbi Flow',
            calendar: 'Calendar',
            system: 'System',
            analytics: 'Analytics'
        };
        return labels[type] || 'General';
    };

    if (collapsed) {
        return (
            <div className="w-16 bg-sidebar border-r border-sidebar-border flex flex-col items-center py-4">
                <Button
                    variant="ghost"
                    size="icon"
                    onClick={() => {
                        onNewChat();
                        // Close the sidebar after creating new chat
                        if (typeof window !== 'undefined') {
                            // Trigger a custom event to close sidebar if needed
                            window.dispatchEvent(new CustomEvent('closeChatSidebar'));
                        }
                    }}
                    className="mb-4 text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    title="New Chat"
                >
                    <Plus className="h-4 w-4" />
                </Button>
                <div className="flex-1 flex flex-col items-center space-y-2">
                    {recentChats.slice(0, 3).map((chat) => (
                        <Button
                            key={chat.id}
                            variant={currentChatId === chat.id ? "default" : "ghost"}
                            size="icon"
                            onClick={() => handleChatSelect(chat.id)}
                            className="h-8 w-8 text-xs"
                            title={chat.full_title}
                        >
                            {chat.title.charAt(0).toUpperCase()}
                        </Button>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="w-64 bg-sidebar border-r border-sidebar-border flex flex-col">
            {/* Header */}
            <div className="p-4 border-b border-sidebar-border">
                <div className="flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-sidebar-foreground">
                        {getTypeLabel(chatType)} Chats
                    </h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => {
                            onNewChat();
                            // Close the sidebar after creating new chat
                            if (typeof window !== 'undefined') {
                                // Trigger a custom event to close sidebar if needed
                                window.dispatchEvent(new CustomEvent('closeChatSidebar'));
                            }
                        }}
                        className="h-6 w-6 text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        title="New Chat"
                    >
                        <Plus className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            {/* Chat List */}
            <ScrollArea className="flex-1">
                <div className="p-2 space-y-1">
                    {loading ? (
                        <div className="flex items-center justify-center py-4">
                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-sidebar-foreground"></div>
                        </div>
                    ) : recentChats.length === 0 ? (
                        <div className="text-center py-8">
                            <MessageSquare className="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                            <p className="text-xs text-muted-foreground">No recent chats</p>
                        </div>
                    ) : (
                        recentChats.map((chat) => (
                            <div
                                key={chat.id}
                                className={`group relative flex items-center space-x-2 p-2 rounded-md cursor-pointer transition-colors ${
                                    currentChatId === chat.id
                                        ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                        : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
                                }`}
                                onClick={() => handleChatSelect(chat.id)}
                            >
                                <div className="flex-shrink-0 w-8 h-8 bg-primary/10 rounded-md flex items-center justify-center">
                                    <span className="text-xs font-medium text-primary">
                                        {chat.title.charAt(0).toUpperCase()}
                                    </span>
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium truncate">
                                        {chat.title}
                                    </p>
                                    <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                        <Clock className="h-3 w-3" />
                                        <span>{chat.last_activity_at}</span>
                                    </div>
                                </div>
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
                                        <DropdownMenuItem onClick={() => onEditChat(chat.id)}>
                                            <Edit3 className="h-3 w-3 mr-2" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            onClick={(e) => handleDeleteChat(chat.id, e)}
                                            className="text-destructive"
                                        >
                                            <Trash2 className="h-3 w-3 mr-2" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        ))
                    )}
                </div>
            </ScrollArea>

            {/* See More Button */}
            {hasMore && (
                <div className="p-2 border-t border-sidebar-border">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={loadMoreChats}
                        className="w-full text-xs text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    >
                        See More
                        <ChevronRight className="h-3 w-3 ml-1" />
                    </Button>
                </div>
            )}
        </div>
    );
}
