<template>
    <div class="flex min-h-screen">
        <!-- Left branded panel - hidden on mobile -->
        <div
            class="relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col"
        >
            <!-- Deep background -->
            <div class="absolute inset-0 bg-[#0a0f1e]"></div>

            <!-- Gradient mesh background -->
            <div class="absolute inset-0">
                <div
                    class="animate-float-slow absolute -left-[10%] top-[10%] h-[500px] w-[500px] rounded-full opacity-40"
                    style="background: radial-gradient(circle, oklch(55% .23 262 / 0.6) 0%, transparent 70%)"
                ></div>
                <div
                    class="animate-float-slower absolute -right-[5%] top-[30%] h-[600px] w-[600px] rounded-full opacity-30"
                    style="background: radial-gradient(circle, oklch(60% .18 280 / 0.5) 0%, transparent 70%)"
                ></div>
                <div
                    class="animate-float-slow absolute bottom-[5%] left-[20%] h-[400px] w-[400px] rounded-full opacity-25"
                    style="background: radial-gradient(circle, oklch(65% .15 230 / 0.5) 0%, transparent 70%)"
                ></div>
            </div>

            <!-- Subtle noise texture overlay -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E')"></div>

            <!-- Glass decorative shapes -->
            <div class="absolute left-[12%] top-[18%] h-24 w-24 rotate-12 rounded-2xl border border-white/[0.08] bg-white/[0.03] backdrop-blur-sm"></div>
            <div class="absolute bottom-[22%] right-[15%] h-32 w-32 -rotate-6 rounded-3xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm"></div>
            <div class="absolute right-[30%] top-[60%] h-16 w-16 rotate-45 rounded-xl border border-white/[0.05] bg-white/[0.02]"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-1 flex-col justify-between p-12 xl:p-16">
                <!-- Logo -->
                <div class="animate-fade-in-up flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/[0.15]">
                        <svg
                            class="h-5 w-5 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                            />
                        </svg>
                    </div>
                    <span class="text-[15px] font-semibold tracking-tight text-white">Rumi</span>
                </div>

                <!-- Tagline -->
                <div class="max-w-md">
                    <h1 class="animate-fade-in-up text-[2.5rem] font-semibold leading-[1.15] tracking-tight text-white xl:text-[2.75rem]">
                        Smart living starts with better management.
                    </h1>
                    <p class="animate-fade-in-up animation-delay-100 mt-5 text-[15px] leading-relaxed text-white/60">
                        The modern platform for condominium communities.
                        Simplify operations, engage residents, and elevate your property.
                    </p>
                </div>

                <!-- Footer -->
                <p class="animate-fade-in animation-delay-500 text-[12px] text-white/30">
                    &copy; {{ new Date().getFullYear() }} Rumi. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-1 items-center justify-center bg-[#fafafa] px-5 py-12 sm:px-8 lg:px-12">
            <div class="w-full max-w-[420px]">
                <!-- Mobile logo -->
                <div class="mb-10 text-center lg:hidden">
                    <div class="mx-auto mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-primary-600 shadow-lg shadow-primary-600/20">
                        <svg
                            class="h-6 w-6 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                            />
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900">Rumi</h1>
                    <p class="mt-1 text-[13px] text-zinc-500">Condominium Management Platform</p>
                </div>

                <router-view v-slot="{ Component }">
                    <Transition name="page-fade" mode="out-in" appear>
                        <component :is="Component" />
                    </Transition>
                </router-view>
            </div>
        </div>
    </div>
</template>
