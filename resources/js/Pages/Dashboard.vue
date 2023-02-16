<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChatSessionItem from "@/Components/ChatSessionItem.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({ chatSessions: Object, errors: Object });

const contactForm = useForm({
    email: "",
    type: "normal",
});

const addContact = () => {
    contactForm.post(route("chatsession.store"), {
        onSuccess: () => contactForm.reset("email"),
    });
};

const groupForm = useForm({
    name: "",
    type: "group",
});

const createGroup = () => {
    groupForm.post(route("chatsession.store"), {
        onSuccess: () => groupForm.reset("name"),
    });
};

function chatSessionsFilter(sessionType, isActive) {
    return props.chatSessions.filter(
        (chatSession) =>
            !!chatSession.active_at == isActive &&
            chatSession.type == sessionType
    );
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
                <form @submit.prevent="addContact">
                    <input
                        v-model="contactForm.email"
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <form @submit.prevent="createGroup" class="flex">
                    <input
                        v-model="groupForm.name"
                        type="text"
                        placeholder="Group Name"
                        class="ml-auto border-gray-300 rounded-md mr-2 text-sm"
                    />
                    <button
                        class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm"
                    >
                        Create group
                    </button>
                </form>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-gray-700 mb-3 font-bold tracking-wider">
                            Friends
                        </h1>
                        <div
                            v-if="chatSessionsFilter('normal', true).length"
                            class="space-y-3"
                        >
                            <ChatSessionItem
                                v-for="chatSession in chatSessionsFilter(
                                    'normal',
                                    true
                                )"
                                :chatSession="chatSession"
                            ></ChatSessionItem>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Your friend contact will appear here.
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-gray-700 mb-3 font-bold tracking-wider">
                            Unknown people
                        </h1>
                        <div
                            v-if="chatSessionsFilter('normal', false).length"
                            class="space-y-3"
                        >
                            <ChatSessionItem
                                v-for="chatSession in chatSessionsFilter(
                                    'normal',
                                    false
                                )"
                                :chatSession="chatSession"
                            ></ChatSessionItem>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Please add contact to start chat.
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-gray-700 mb-3 font-bold tracking-wider">
                            Groups
                        </h1>
                        <div
                            v-if="chatSessionsFilter('group', true).length"
                            class="space-y-3"
                        >
                            <ChatSessionItem
                                v-for="chatSession in chatSessionsFilter(
                                    'group',
                                    true
                                )"
                                :chatSession="chatSession"
                            ></ChatSessionItem>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Your groups will appear here.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
