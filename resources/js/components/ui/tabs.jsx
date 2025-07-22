import * as React from "react"
import * as TabsPrimitive from "@radix-ui/react-tabs"

import { cn } from "@/lib/utils"

const Tabs = TabsPrimitive.Root

const TabsList = React.forwardRef(({ className, ...props }, ref) => (
  <TabsPrimitive.List
    ref={ref}
    className={cn(
      "inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground",
      className
    )}
    {...props}
  />
))
TabsList.displayName = TabsPrimitive.List.displayName

const TabsTrigger = React.forwardRef(({ className, ...props }, ref) => (
  <TabsPrimitive.Trigger
    ref={ref}
    className={cn(
      "inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm",
      className
    )}
    {...props}
  />
))
TabsTrigger.displayName = TabsPrimitive.Trigger.displayName

const TabsContent = React.forwardRef(({ className, ...props }, ref) => (
  <TabsPrimitive.Content
    ref={ref}
    className={cn(
      "mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
      className
    )}
    {...props}
  />
))
TabsContent.displayName = TabsPrimitive.Content.displayName

export { Tabs, TabsList, TabsTrigger, TabsContent }

// Browser-like tabs component for navigation
import { X, XCircle, MoreHorizontal, ArrowUp, ArrowDown } from 'lucide-react';
import { Button } from './button';
import { Popover, PopoverContent, PopoverTrigger } from './popover';

export function BrowserTabs({ tabs, activeTab, onTabClick, onTabClose, onCloseAll, onCloseOther, onMoveTab }) {
    const [contextMenuTab, setContextMenuTab] = React.useState(null);
    const [contextMenuOpen, setContextMenuOpen] = React.useState(false);
    const [contextMenuPosition, setContextMenuPosition] = React.useState({ x: 0, y: 0 });

    const handleContextMenu = (e, tab) => {
        e.preventDefault();
        e.stopPropagation();

        setContextMenuTab(tab);
        setContextMenuPosition({ x: e.clientX, y: e.clientY });
        setContextMenuOpen(true);
    };

    const handleCloseOther = () => {
        if (contextMenuTab) {
            onCloseOther(contextMenuTab.id);
        }
        setContextMenuOpen(false);
    };

    const handleMoveUp = () => {
        if (contextMenuTab) {
            onMoveTab(contextMenuTab.id, 'up');
        }
        setContextMenuOpen(false);
    };

    const handleMoveDown = () => {
        if (contextMenuTab) {
            onMoveTab(contextMenuTab.id, 'down');
        }
        setContextMenuOpen(false);
    };

    const handleMoveToStart = () => {
        if (contextMenuTab) {
            onMoveTab(contextMenuTab.id, 'start');
        }
        setContextMenuOpen(false);
    };

    const handleMoveToEnd = () => {
        if (contextMenuTab) {
            onMoveTab(contextMenuTab.id, 'end');
        }
        setContextMenuOpen(false);
    };

    return (
        <div className="flex items-center space-x-1 overflow-x-auto">
            {tabs.map((tab, index) => (
                <div
                    key={tab.id}
                    className={`group flex items-center space-x-2 px-3 py-2 rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer min-w-0 flex-shrink-0 ${
                        activeTab === tab.id
                            ? 'bg-background border-primary text-foreground'
                            : 'bg-muted/50 border-transparent text-muted-foreground hover:bg-muted hover:text-foreground'
                    }`}
                    onClick={() => onTabClick(tab.id)}
                    onContextMenu={(e) => handleContextMenu(e, tab)}
                >
                    <span className="text-sm font-medium truncate max-w-24">
                        {tab.name}
                    </span>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-5 w-5 p-0 opacity-0 group-hover:opacity-100 hover:bg-muted-foreground/20 transition-opacity"
                        onClick={(e) => {
                            e.stopPropagation();
                            onTabClose(tab.id);
                        }}
                    >
                        <X className="h-3 w-3" />
                    </Button>
                </div>
            ))}

            {/* Close All Button */}
            {tabs.length > 1 && (
                <Button
                    variant="ghost"
                    size="sm"
                    className="h-8 px-2 text-xs text-muted-foreground hover:text-foreground hover:bg-muted-foreground/20 transition-colors ml-2"
                    onClick={onCloseAll}
                    title="Close all tabs"
                >
                    <XCircle className="h-3 w-3 mr-1" />
                    Close All
                </Button>
            )}

            {/* Context Menu */}
            <Popover open={contextMenuOpen} onOpenChange={setContextMenuOpen}>
                <PopoverContent
                    className="w-48 p-1"
                    style={{
                        position: 'fixed',
                        left: contextMenuPosition.x,
                        top: contextMenuPosition.y,
                        transform: 'none'
                    }}
                >
                    <div className="space-y-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-sm"
                            onClick={handleCloseOther}
                        >
                            <XCircle className="h-4 w-4 mr-2" />
                            Close Other
                        </Button>

                        <div className="border-t border-border my-1" />

                        <div className="px-2 py-1 text-xs font-medium text-muted-foreground">
                            Move Tab
                        </div>

                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-sm"
                            onClick={handleMoveToStart}
                            disabled={tabs.indexOf(contextMenuTab) === 0}
                        >
                            <ArrowUp className="h-4 w-4 mr-2" />
                            Move to Start
                        </Button>

                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-sm"
                            onClick={handleMoveUp}
                            disabled={tabs.indexOf(contextMenuTab) === 0}
                        >
                            <ArrowUp className="h-4 w-4 mr-2" />
                            Move Up
                        </Button>

                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-sm"
                            onClick={handleMoveDown}
                            disabled={tabs.indexOf(contextMenuTab) === tabs.length - 1}
                        >
                            <ArrowDown className="h-4 w-4 mr-2" />
                            Move Down
                        </Button>

                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start text-sm"
                            onClick={handleMoveToEnd}
                            disabled={tabs.indexOf(contextMenuTab) === tabs.length - 1}
                        >
                            <ArrowDown className="h-4 w-4 mr-2" />
                            Move to End
                        </Button>
                    </div>
                </PopoverContent>
            </Popover>
        </div>
    );
}
