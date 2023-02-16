<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, reactive, ref } from "vue";
import moment from "moment";
import debounce from "lodash/debounce";

const props = defineProps({
    messages: Object,
    chatSession: Object,
    errors: Object,
});

let timer = ref("");

// let typingParticipant = ref(null);
let typingParticipants = ref([]);
let activeParticipants = ref([]);

let messages = reactive(props.messages);

const messageForm = useForm({
    message: "",
    chatSessionId: props.chatSession.id,
});

const sentMessage = () => {
    messageForm.post(route("message.store"), {
        preserveScroll: true,
        onSuccess: () => messageForm.reset("message"),
    });
};

const memberForm = useForm({
    email: "",
    chatSessionId: props.chatSession.id,
});

const addMember = () => {
    memberForm.post(route("chatsession.member.store"), {
        preserveScroll: true,
        onSuccess: () => memberForm.reset("email"),
    });
};

const typing = debounce(() => {
    window.Echo.join("chat.session." + props.chatSession.id).whisper("typing", {
        name: usePage().props.auth.user.name,
    });
}, 500);

function markAsRead() {
    router.reload({
        onSuccess: () => messageForm.reset("message"),
    });
}

onMounted(() => {
    window.Echo.join("chat.session." + props.chatSession.id)
        .here((users) => {
            console.log(
                "all users: " + users.map((user) => user.name).join(", ")
            );
            activeParticipants.value = users;
        })
        .joining((user) => {
            console.log("joining " + user.name);
            activeParticipants.value.push({ name: user.name });
        })
        .leaving((user) => {
            console.log("leaving " + user.name);
            activeParticipants.value.splice(
                activeParticipants.value.indexOf(user),
                1
            );
        })
        .listen("MessageSent", (e) => {
            messages.push(e.message);

            markAsRead();
        })
        .listenForWhisper("typing", (e) => {
            if (!typingParticipants.value.includes(e.name)) {
                typingParticipants.value.push(e.name);
            }

            if (timer) clearTimeout(timer);

            timer.value = setTimeout(() => {
                typingParticipants.value = [];
            }, 1000);

            // console.log(typingParticipants.value);
            // this is for two people chat
            // typingParticipant.value = e.name;

            // if (timer.value) clearTimeout(timer.value);

            // timer.value = setTimeout(() => {
            //     typingParticipant.value = null;
            // }, 20000);
        });
});

onUnmounted(() => {
    window.Echo.leave("chat.session." + props.chatSession.id);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{
                        props.chatSession.type == "group"
                            ? props.chatSession.name
                            : props.chatSession.users[0].name
                    }}
                </h2>
                <form @submit.prevent="addMember" class="ml-auto">
                    <input
                        v-model="memberForm.email"
                        type="email"
                        placeholder="email"
                        class="border-gray-300 rounded-md mr-2 text-sm"
                    />
                    <button
                        class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm"
                    >
                        Add Member
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
            <div
                class="flex items-start space-x-5 max-w-7xl mx-auto sm:px-6 lg:px-8"
            >
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3 p-6"
                >
                    <div v-for="participant in chatSession.users">
                        <span
                            v-if="
                                activeParticipants.some(
                                    (activeParticipant) =>
                                        activeParticipant.name ==
                                        participant.name
                                )
                            "
                            class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 mr-1"
                        ></span
                        >{{ participant.name }}
                    </div>
                </div>
                <!-- <div
                        v-if="chatSession.type == 'group'"
                        class="flex justify-between items-end"
                    >
                        group members:
                        {{
                            chatSession.users
                                .map((user) => user.name)
                                .join(", ")
                        }}
                    </div> -->
                <div
                    class="flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3"
                >
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
                        <div
                            v-if="
                                chatSession.creator.id !=
                                    $page.props.auth.user.id &&
                                !chatSession.active_at
                            "
                            class="text-center text-gray-400 text-sm"
                        >
                            (both of you will become friend if you reply)
                        </div>
                        <div class="flex justify-between items-center mt-5">
                            <div
                                v-if="typingParticipants.length"
                                class="text-gray-700 text-sm animate-pulse"
                            >
                                <!-- <span
                                    v-for="typingParticipant in typingParticipants"
                                >
                                    {{ typingParticipant }}
                                </span> -->
                                {{ typingParticipants.join(", ") }}
                                {{
                                    typingParticipants.length > 1 ? "are" : "is"
                                }}
                                typing...
                            </div>
                            <form @submit.prevent="sentMessage" class="ml-auto">
                                <input
                                    v-model="messageForm.message"
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
