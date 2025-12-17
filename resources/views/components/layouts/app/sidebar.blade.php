<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#F0F7FF] dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-blue-200 bg-blue-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    @if(in_array('diary', $unlockedFeatures ?? []))
                        <flux:navlist.item icon="document-text" :href="route('diary')" :current="request()->routeIs('diary')" wire:navigate>日記</flux:navlist.item>
                    @endif
                    @if(in_array('milestones', $unlockedFeatures ?? []))
                        <flux:navlist.item icon="flag" :href="route('career.milestones')" :current="request()->routeIs('career.milestones')" wire:navigate>マイルストーン</flux:navlist.item>
                    @endif
                </flux:navlist.group>
                
                <flux:navlist.group heading="診断・分析" class="grid">
                    @if(in_array('diagnosis', $unlockedFeatures ?? []))
                        @if(isset($latestDiagnosisId))
                            <flux:navlist.item icon="chart-bar" :href="route('diagnosis.result', $latestDiagnosisId)" :current="request()->routeIs('diagnosis.*')" wire:navigate>現職満足度診断</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="chart-bar" :href="route('diagnosis.start')" :current="request()->routeIs('diagnosis.*')" wire:navigate>現職満足度診断</flux:navlist.item>
                        @endif
                    @endif
                    @if(in_array('life_history', $unlockedFeatures ?? []))
                        <flux:navlist.item icon="clock" :href="route('life-history.timeline')" :current="request()->routeIs('life-history.*')" wire:navigate>人生史</flux:navlist.item>
                    @endif
                    @if(in_array('wcm', $unlockedFeatures ?? []))
                        @if(isset($latestWcmSheetId))
                            <flux:navlist.item icon="light-bulb" :href="route('wcm.sheet', $latestWcmSheetId)" :current="request()->routeIs('wcm.*')" wire:navigate>WCMシート</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="light-bulb" :href="route('wcm.start')" :current="request()->routeIs('wcm.*')" wire:navigate>WCMシート</flux:navlist.item>
                        @endif
                    @endif
                    @if(in_array('assessment', $unlockedFeatures ?? []))
                        <flux:navlist.item icon="user-circle" :href="route('assessments.index')" :current="request()->routeIs('assessments.*')" wire:navigate>自己診断結果</flux:navlist.item>
                    @endif
                </flux:navlist.group>

                <flux:navlist.group heading="相談・サポート" class="grid">
                    <flux:navlist.item icon="calendar" :href="route('consultation.request')" :current="request()->routeIs('consultation.*')" wire:navigate>面談申し込み</flux:navlist.item>
                    <flux:navlist.item icon="chat-bubble-left-right" :href="route('chat.index')" :current="request()->routeIs('chat.*')" wire:navigate>チャット相談</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group heading="プロフィール" class="grid">
                    <flux:navlist.item icon="document-text" :href="route('resume.upload')" :current="request()->routeIs('resume.*')" wire:navigate>履歴書アップロード</flux:navlist.item>
                    <flux:navlist.item icon="document-duplicate" :href="route('career-history.upload')" :current="request()->routeIs('career-history.*')" wire:navigate>職務経歴書アップロード</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>