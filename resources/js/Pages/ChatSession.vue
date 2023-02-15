<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, reactive, ref } from "vue";
import moment from "moment";

const props = defineProps({ messages: Object, sessionId: Number });

let timer = ref("");

let typingParticipant = ref(null);

let messages = reactive(props.messages);

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

const typing = () => {
    window.Echo.private("chat.session." + props.sessionId).whisper("typing", {
        name: usePage().props.auth.user.name,
    });
};

function markAsRead() {
    router.reload();
}

onMounted(() => {
    window.Echo.private("chat.session." + props.sessionId)
        .listen("MessageSent", (e) => {
            messages.push(e.message);

            markAsRead();
        })
        .listenForWhisper("typing", (e) => {
            console.log(e.name);

            typingParticipant.value = e.name;

            if (timer.value) clearTimeout(timer.value);

            timer.value = setTimeout(() => {
                typingParticipant.value = null;
            }, 1000);
        });
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
                    <div class="flex flex-col p-6 text-gray-900">
                        <div v-if="messages.length">
                            <div
                                v-for="(message, index) in messages"
                                :key="index"
                                :class="[
                                    {
                                        'flex flex-col items-end ml-auto':
                                            message.sender_id ==
                                            $page.props.auth.user.id,
                                    },
                                    '[&:not(:first-child)]:mt-3',
                                ]"
                            >
                                <div
                                    :class="[
                                        {
                                            'flex-row-reverse':
                                                message.sender_id ==
                                                $page.props.auth.user.id,
                                        },
                                        'flex items-center',
                                    ]"
                                >
                                    <h1 class="font-bold">
                                        {{
                                            message.sender_id ==
                                            $page.props.auth.user.id
                                                ? "me"
                                                : message.user.name
                                        }}
                                    </h1>
                                    <span class="text-xs mx-2">{{
                                        moment(message.created_at).format("LT")
                                    }}</span>
                                </div>

                                <p
                                    class="text-gray-700 text-sm bg-gray-100 inline-block rounded-lg px-4 py-1"
                                >
                                    {{ message.content }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500">
                            No message yet
                        </div>
                        <div class="flex justify-between items-center mt-5">
                            <div
                                v-if="typingParticipant"
                                class="text-gray-700 text-sm animate-pulse"
                            >
                                {{ typingParticipant }} is typing...
                            </div>
                            <form @submit.prevent="sentMessage" class="ml-auto">
                                <input
                                    v-model="form.message"
                                    type="text"
                                    placeholder="enter your message"
                                    class="border-gray-200 rounded-lg text-sm text-gray-500"
                                    @keydown="typing"
                                />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
