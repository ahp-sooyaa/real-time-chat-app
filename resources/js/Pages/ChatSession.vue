<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, reactive } from "vue";

const props = defineProps({ chats: Object, sessionId: Number });

let chats = reactive(props.chats);

const form = useForm({
    message: "",
    chatSessionId: props.sessionId,
});

const sentMessage = () => {
    form.post(route("message.store"), {
        preserveScroll: true,
        onSuccess: () => form.reset("message"),
    });
};

onMounted(() => {
    window.Echo.private("chat.session." + props.sessionId).listen(
        "MessageSent",
        (e) => {
            chats.push(e.chat);
        }
    );
});
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
                        <div
                            v-for="chat in chats"
                            :class="[
                                {
                                    'text-right':
                                        chat.sender_id ==
                                        $page.props.auth.user.id,
                                },
                            ]"
                        >
                            {{ chat.message }}
                        </div>
                        <div class="mt-5">
                            <form @submit.prevent="sentMessage">
                                <input
                                    type="text"
                                    placeholder="enter your message"
                                    v-model="form.message"
                                />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
