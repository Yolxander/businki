import * as React from "react"
import { Circle } from "lucide-react"
import { cn } from "@/lib/utils"

const RadioGroupContext = React.createContext()

const RadioGroup = React.forwardRef(({ className, value, onValueChange, children, ...props }, ref) => {
  return (
    <RadioGroupContext.Provider value={{ value, onValueChange }}>
      <div
        ref={ref}
        className={cn("grid gap-2", className)}
        {...props}
      >
        {children}
      </div>
    </RadioGroupContext.Provider>
  )
})
RadioGroup.displayName = "RadioGroup"

const RadioGroupItem = React.forwardRef(({ className, value, id, children, ...props }, ref) => {
  const { value: selectedValue, onValueChange } = React.useContext(RadioGroupContext)
  const isSelected = selectedValue === value

  return (
    <div
      ref={ref}
      className={cn(
        "flex items-center space-x-3 p-4 border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer",
        isSelected && "border-primary bg-primary/5",
        className
      )}
      onClick={() => onValueChange(value)}
      {...props}
    >
      <div className={cn(
        "aspect-square h-4 w-4 rounded-full border-2 flex items-center justify-center transition-colors",
        isSelected
          ? "border-primary bg-primary"
          : "border-muted-foreground/25 hover:border-primary/50"
      )}>
        {isSelected && <Circle className="h-2.5 w-2.5 fill-current text-primary-foreground" />}
      </div>
      {children}
    </div>
  )
})
RadioGroupItem.displayName = "RadioGroupItem"

export { RadioGroup, RadioGroupItem }
