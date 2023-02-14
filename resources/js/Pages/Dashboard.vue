<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChatSessionItem from "@/Components/ChatSessionItem.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("contact.store"), {
        onFinish: () => form.reset("email"),
    });
};

defineProps({ chatSessions: Object });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
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
                        </form>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-5"
                >
                    <div class="p-6 text-gray-900">
                        <!-- <Link
                            :href="route('chatsession.show', chatSession.id)"
                            v-for="chatSession in chatSessions"
                            class="block"
                        >
                            {{ chatSession.users[0].name }}
                            <div v-if="chatSession.chats[0]">
                                <span class="text-gray-500"
                                    >latest message:</span
                                >
                                {{ chatSession.chats[0].message }}
                            </div>
                        </Link> -->
                        <ChatSessionItem
                            v-for="chatSession in chatSessions"
                            :chatSession="chatSession"
                        ></ChatSessionItem>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
