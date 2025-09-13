@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-border bg-background px-3 py-2 text-foreground placeholder-muted-foreground focus:border-primary focus:ring-primary shadow-sm']) }}>
