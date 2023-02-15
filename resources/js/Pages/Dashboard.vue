<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChatSessionItem from "@/Components/ChatSessionItem.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({ chatSessions: Object, errors: Object });

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("chatsession.store"), {
        onSuccess: () => form.reset("email"),
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
                <form @submit.prevent="submit">
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="email"
                        class="border-gray-300 rounded-md mr-2 text-sm"
                    />
                    <button
                        class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm"
                    >
                        add contact
                    </button>
                    <div
                        v-if="props.errors.email"
                        class="text-sm mt-1 text-red-600"
                    >
                        {{ props.errors.email }}
                    </div>
                </form>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="chatSessions.length" class="space-y-3">
                            <ChatSessionItem
                                v-for="chatSession in chatSessions"
                                :chatSession="chatSession"
                            ></ChatSessionItem>
                        </div>
                        <div v-else>Please add contact to start chat.</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
