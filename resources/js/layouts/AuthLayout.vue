<template>
    <div class="flex min-h-screen">
        <!-- Left branded panel - hidden on mobile -->
        <div
            class="relative hidden w-[45%] overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 lg:flex lg:flex-col lg:justify-between"
        >
            <!-- Decorative elements -->
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute -left-20 -top-20 h-80 w-80 rounded-full bg-white/20"
                ></div>
                <div
                    class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-white/10"
                ></div>
                <div
                    class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/5"
                ></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-1 flex-col justify-between p-10">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm"
                    >
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
                    <span class="text-xl font-bold text-white">Resida</span>
                </div>

                <!-- Tagline -->
                <div class="max-w-sm">
                    <h2 class="text-3xl font-bold leading-tight text-white">
                        Smart living starts with better management.
                    </h2>
                    <p class="mt-4 text-base leading-relaxed text-primary-100">
                        The modern platform for condominium communities.
                        Simplify operations, engage residents, and elevate your
                        property.
                    </p>

                    <!-- Feature highlights -->
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15"
                            >
                                <svg
                                    class="h-4 w-4 text-primary-100"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </div>
                            <span class="text-sm text-primary-50"
                                >Visitor management & QR check-ins</span
                            >
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15"
                            >
                                <svg
                                    class="h-4 w-4 text-primary-100"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </div>
                            <span class="text-sm text-primary-50"
                                >Facility booking & scheduling</span
                            >
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15"
                            >
                                <svg
                                    class="h-4 w-4 text-primary-100"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </div>
                            <span class="text-sm text-primary-50"
                                >Real-time notifications & announcements</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-xs text-primary-200">
                    &copy; {{ new Date().getFullYear() }} Resida. All rights
                    reserved.
                </p>
            </div>
        </div>

        <!-- Right form panel -->
        <div
            class="flex flex-1 items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
        >
            <div class="w-full max-w-md">
                <!-- Mobile logo - shown only on mobile/tablet -->
                <div class="mb-8 text-center lg:hidden">
                    <div
                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-600 text-white shadow-sm"
                    >
                        <svg
                            class="h-7 w-7"
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
                    <h1 class="text-2xl font-bold text-gray-900">Resida</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Condominium Management Platform
                    </p>
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
