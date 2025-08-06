import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';

export default function AICrudTester({ auth }) {
    const [message, setMessage] = useState('');
    const [sessionId, setSessionId] = useState('');
    const [conversation, setConversation] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [availableModels, setAvailableModels] = useState({});
    const [sessionData, setSessionData] = useState({});

    useEffect(() => {
        // Generate session ID on component mount
        setSessionId('session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
        loadAvailableModels();
    }, []);

    const loadAvailableModels = async () => {
        try {
            const response = await fetch('/api/ai-crud/models', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            if (data.success) {
                setAvailableModels(data.models);
            }
        } catch (error) {
            console.error('Failed to load models:', error);
        }
    };

    const sendMessage = async () => {
        if (!message.trim()) return;

        const userMessage = message;
        setMessage('');
        setIsLoading(true);

        // Add user message to conversation
        setConversation(prev => [...prev, { role: 'user', content: userMessage, timestamp: new Date() }]);

        try {
            const response = await fetch('/api/ai-crud/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: userMessage,
                    session_id: sessionId
                })
            });

            const data = await response.json();

            // Add AI response to conversation
            setConversation(prev => [...prev, {
                role: 'assistant',
                content: data.message,
                timestamp: new Date(),
                metadata: {
                    success: data.success,
                    needs_more_info: data.needs_more_info,
                    missing_field: data.missing_field,
                    intent: data.intent,
                    session_data: data.session_data
                }
            }]);

            // Update session data
            if (data.session_data) {
                setSessionData(data.session_data);
            }

        } catch (error) {
            console.error('Failed to send message:', error);
            setConversation(prev => [...prev, {
                role: 'assistant',
                content: 'Sorry, I encountered an error. Please try again.',
                timestamp: new Date()
            }]);
        } finally {
            setIsLoading(false);
        }
    };

    const clearSession = async () => {
        try {
            await fetch('/api/ai-crud/session', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ session_id: sessionId })
            });

            setConversation([]);
            setSessionData({});
            setSessionId('session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
        } catch (error) {
            console.error('Failed to clear session:', error);
        }
    };

    const runTest = async () => {
        setIsLoading(true);
        try {
            const response = await fetch('/api/ai-crud/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.success) {
                console.log('Test results:', data.test_results);
                alert('Test completed! Check console for results.');
            }
        } catch (error) {
            console.error('Test failed:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="AI CRUD Tester" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-bold mb-6">AI CRUD System Tester</h1>

                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                {/* Main Chat Interface */}
                                <div className="lg:col-span-2">
                                    <Card>
                                        <CardHeader>
                                            <CardTitle className="flex justify-between items-center">
                                                <span>Conversation</span>
                                                <div className="flex gap-2">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={runTest}
                                                        disabled={isLoading}
                                                    >
                                                        Run Test
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={clearSession}
                                                        disabled={isLoading}
                                                    >
                                                        Clear Session
                                                    </Button>
                                                </div>
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-4">
                                                {/* Conversation History */}
                                                <ScrollArea className="h-96 border rounded-lg p-4">
                                                    <div className="space-y-4">
                                                        {conversation.map((msg, index) => (
                                                            <div key={index} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
                                                                <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                                                    msg.role === 'user'
                                                                        ? 'bg-blue-500 text-white'
                                                                        : 'bg-gray-100 text-gray-900'
                                                                }`}>
                                                                    <div className="text-sm">{msg.content}</div>
                                                                    {msg.metadata && (
                                                                        <div className="mt-2 text-xs opacity-75">
                                                                            {msg.metadata.needs_more_info && (
                                                                                <Badge variant="secondary" className="mr-1">
                                                                                    Needs More Info
                                                                                </Badge>
                                                                            )}
                                                                            {msg.metadata.missing_field && (
                                                                                <Badge variant="outline" className="mr-1">
                                                                                    Missing: {msg.metadata.missing_field}
                                                                                </Badge>
                                                                            )}
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        ))}
                                                        {isLoading && (
                                                            <div className="flex justify-start">
                                                                <div className="bg-gray-100 text-gray-900 px-4 py-2 rounded-lg">
                                                                    <div className="text-sm">Thinking...</div>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </ScrollArea>

                                                {/* Input Area */}
                                                <div className="flex gap-2">
                                                    <Textarea
                                                        value={message}
                                                        onChange={(e) => setMessage(e.target.value)}
                                                        onKeyPress={handleKeyPress}
                                                        placeholder="Type your message here... (e.g., 'I want to create a new client')"
                                                        className="flex-1"
                                                        rows={2}
                                                    />
                                                    <Button
                                                        onClick={sendMessage}
                                                        disabled={isLoading || !message.trim()}
                                                    >
                                                        Send
                                                    </Button>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                {/* Sidebar */}
                                <div className="space-y-4">
                                    {/* Session Info */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Session Info</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-2">
                                                <div className="text-sm">
                                                    <strong>Session ID:</strong>
                                                    <div className="font-mono text-xs bg-gray-100 p-1 rounded">
                                                        {sessionId}
                                                    </div>
                                                </div>
                                                <div className="text-sm">
                                                    <strong>Session Data:</strong>
                                                    <pre className="text-xs bg-gray-100 p-2 rounded mt-1 overflow-auto">
                                                        {JSON.stringify(sessionData, null, 2)}
                                                    </pre>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Available Models */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Available Models</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-2">
                                                {Object.entries(availableModels).map(([modelName, config]) => (
                                                    <div key={modelName} className="border rounded p-2">
                                                        <div className="font-semibold capitalize">{modelName}</div>
                                                        <div className="text-xs text-gray-600">
                                                            Operations: {config.operations?.join(', ')}
                                                        </div>
                                                        <div className="text-xs text-gray-500 mt-1">
                                                            Fields: {Object.keys(config.fields || {}).length}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </CardContent>
                                    </Card>

                                    {/* Example Messages */}
                                    <Card>
                                        <CardHeader>
                                            <CardTitle>Example Messages</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="w-full text-left justify-start"
                                                    onClick={() => setMessage("I want to create a new client")}
                                                >
                                                    "I want to create a new client"
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="w-full text-left justify-start"
                                                    onClick={() => setMessage("The client's name is John Doe")}
                                                >
                                                    "The client's name is John Doe"
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="w-full text-left justify-start"
                                                    onClick={() => setMessage("His email is john.doe@example.com")}
                                                >
                                                    "His email is john.doe@example.com"
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="w-full text-left justify-start"
                                                    onClick={() => setMessage("Show me all my clients")}
                                                >
                                                    "Show me all my clients"
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
