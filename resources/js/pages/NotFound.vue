<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12"
    >
        <div
            class="w-full max-w-md rounded-xl border border-gray-200 bg-white px-5 py-8 text-center"
        >
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-dashed border-gray-300 bg-gray-50"
            >
                <svg
                    class="h-8 w-8 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 15.75 21 21m-9.75-3.75a6 6 0 1 1 0-12 6 6 0 0 1 0 12Z"
                    />
                </svg>
            </div>
            <p
                class="text-[11px] font-semibold uppercase tracking-widest text-gray-400"
            >
                404
            </p>
            <h1 class="mt-2 text-base font-semibold text-gray-900">
                Page not found
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Sorry, we couldn't find the page you're looking for.
            </p>
            <router-link
                to="/"
                class="mt-6 inline-flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700 active:scale-[0.98]"
            >
                Go back home
            </router-link>
        </div>
    </div>
</template>
