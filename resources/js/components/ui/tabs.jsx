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
import { X } from 'lucide-react';
import { Button } from './button';

export function BrowserTabs({ tabs, activeTab, onTabClick, onTabClose }) {
    return (
        <div className="flex items-center space-x-1 overflow-x-auto">
            {tabs.map((tab) => (
                <div
                    key={tab.id}
                    className={`group flex items-center space-x-2 px-3 py-2 rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer min-w-0 flex-shrink-0 ${
                        activeTab === tab.id
                            ? 'bg-background border-primary text-foreground'
                            : 'bg-muted/50 border-transparent text-muted-foreground hover:bg-muted hover:text-foreground'
                    }`}
                    onClick={() => onTabClick(tab.id)}
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
        </div>
    );
}
