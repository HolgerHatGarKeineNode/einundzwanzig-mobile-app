<x-layouts::mobile :title="__('Meetups')" :heading="__('Meetups')">
    <div class="flex min-h-[60dvh] flex-col items-center justify-center gap-4 text-center">
        <span class="flex size-16 items-center justify-center rounded-2xl bg-brand-500/15 text-brand-600 dark:text-brand-400">
            <flux:icon name="map-pin" class="size-8"/>
        </span>
        <flux:heading size="lg" level="1">{{ __('Meetups kommen bald') }}</flux:heading>
        <flux:text class="max-w-xs">
            {{ __('Hier findest du demnächst alle EINUNDZWANZIG-Meetups und Termine aus dem Portal.') }}
        </flux:text>
        <flux:badge color="orange" size="sm">{{ __('In Arbeit') }}</flux:badge>
    </div>
</x-layouts::mobile>
