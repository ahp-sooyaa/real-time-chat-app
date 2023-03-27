<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div
            v-show="
                $page.props.flash.success_message ||
                $page.props.flash.error_message
            "
            class="absolute bottom-10 right-10 bg-white shadow px-5 py-3 text-sm rounded-lg"
            :class="
                $page.props.flash.success_message
                    ? 'text-green-500'
                    : 'text-red-500'
            "
        >
            {{
                $page.props.flash.success_message ??
                $page.props.flash.error_message
            }}
        </div>
        
        <div>
            <Link href="/">
                <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
