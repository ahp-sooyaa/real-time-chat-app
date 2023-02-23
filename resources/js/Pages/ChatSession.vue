<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, computed, reactive, ref } from "vue";
import moment from "moment";
import throttle from "lodash/throttle";

const props = defineProps({
    messages: Object,
    chatSession: Object,
    errors: Object,
});

let timer = ref("");
let loading = ref(false);

// let typingParticipant = ref(null);
let typingParticipants = ref([]);
let activeParticipants = ref([]);

let messages = reactive(props.messages);

const messageForm = useForm({
    message: "",
    chatSessionId: props.chatSession.id,
});

const sentMessage = () => {
    if (loading.value || !messageForm.message) return;

    messages.push({
        sender_id: usePage().props.auth.user.id,
        content: messageForm.message,
        user: {
            name: usePage().props.auth.user.name,
        },
        sent_by: "user",
        created_at: moment().format(),
    });

    messageForm.post(route("message.store"), {
        preserveScroll: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
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

const typing = throttle(() => {
    window.Echo.join("chatsession." + props.chatSession.id).whisper("typing", {
        name: usePage().props.auth.user.name,
    });
}, 1000);

function markAsRead() {
    router.reload({
        onSuccess: () => messageForm.reset("message"),
    });
}

onMounted(() => {
    // props.chatSession.users.forEach(user => {
    //     window.Echo.private('chatsession.' + props.chatSession.id)
    //         .listen('LeaveChatSession', e => {
    //             console.log('called');
    //             // props.chatSession.users.splice(props.chatSession.users.indexOf(e.user), 1)
    //         })
    // });

    window.Echo.join("chatsession." + props.chatSession.id)
        .here((users) => {
            console.log(
                "all users: " + users.map((user) => user.name).join(", ")
            );
            activeParticipants.value = users;
        })
        .joining((user) => {
            activeParticipants.value.push({ name: user.name });
        })
        .leaving((user) => {
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
    window.Echo.leave("chatsession." + props.chatSession.id);
});

const chatSessionName = computed(() => {
    if (props.chatSession.type == "group") {
        return props.chatSession.name;
    }

    let otherParticipant = props.chatSession.users.find(
        (user) => user.name != usePage().props.auth.user.name
    );

    return otherParticipant.nickname ?? otherParticipant.name;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <div class="flex items-center space-x-2">
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        <!-- {{
                            props.chatSession.type == "group"
                                ? props.chatSession.name
                                : props.chatSession.users.find(
                                      (user) =>
                                          user.name !=
                                          $page.props.auth.user.name
                                  ).nickname
                        }} -->
                        {{ chatSessionName }}
                    </h2>
                    <Dropdown align="left" width="48">
                        <template #trigger>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="bg-gray-100 h-4 rounded-lg w-6 cursor-pointer hover:text-gray-900 text-gray-600"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"
                                />
                            </svg>
                        </template>

                        <template #content>
                            <DropdownLink
                                v-if="chatSession.type == 'group'"
                                :href="
                                    route(
                                        'chatsession.setting.edit',
                                        chatSession.id
                                    )
                                "
                            >
                                Setting
                            </DropdownLink>
                            <DropdownLink
                                :href="
                                    route('chatsession.member.destroy', [
                                        chatSession.id,
                                        $page.props.auth.user.id,
                                    ])
                                "
                                :data="{
                                    action:
                                        $page.props.auth.user.name +
                                        ' leaved from group',
                                }"
                                method="delete"
                                as="button"
                            >
                                Leave
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
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
                    v-if="chatSession.type == 'group'"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3 p-6"
                >
                    <h1 class="text-gray-900">Group members</h1>
                    <div
                        v-for="participant in chatSession.users"
                        class="text-gray-700 text-sm"
                    >
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
                        >{{ participant.pivot.nickname ?? participant.name }}
                    </div>
                </div>

                <div
                    class="flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3"
                >
                    <div class="flex flex-col p-6 text-gray-900">
                        <div v-if="messages.length">
                            <div
                                v-for="(message, index) in messages"
                                :key="index"
                                class="[&:not(:first-child)]:mt-3"
                            >
                                <div
                                    v-if="message.sent_by == 'user'"
                                    :class="{
                                        'flex flex-col items-end ml-auto':
                                            message.sender_id ==
                                            $page.props.auth.user.id,
                                    }"
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
                                                    : message.nickname ??
                                                      message.user.name
                                            }}
                                        </h1>
                                        <span class="text-xs mx-2">{{
                                            moment(message.created_at).format(
                                                "LT"
                                            )
                                        }}</span>
                                    </div>

                                    <div class="flex items-center">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="w-5 h-5 text-gray-700 hover:text-gray-900 cursor-pointer"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"
                                            />
                                        </svg>

                                        <p
                                            class="text-gray-700 text-sm bg-gray-100 inline-block rounded-lg px-4 py-1"
                                        >
                                            {{ message.content }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-sm text-gray-400 text-center"
                                >
                                    <div class="text-xs">
                                        {{
                                            moment(message.created_at).format(
                                                "LT"
                                            )
                                        }}
                                    </div>
                                    {{ message.content }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-sm text-gray-400">
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
                        <div
                            v-if="typingParticipants.length"
                            class="text-gray-700 text-sm animate-pulse mt-6"
                        >
                            {{ typingParticipants.join(", ") }}
                            {{ typingParticipants.length > 1 ? "are" : "is" }}
                            typing...
                        </div>
                        <div class="mt-6 border-t pt-3">
                            <form @submit.prevent="sentMessage" class="ml-auto">
                                <input
                                    v-model="messageForm.message"
                                    type="text"
                                    placeholder="Type something..."
                                    class="border-transparent rounded-lg text-sm text-gray-500 -mt-3 focus:border-transparent focus:ring-0 px-0 w-full"
                                    @keydown="typing"
                                    autofocus
                                />
                                <button
                                    class="flex items-center ml-auto bg-gray-900 text-white text-sm px-5 py-2 rounded"
                                    type="submit"
                                >
                                    Sent
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-4 h-4 ml-1 -mr-1"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                                        />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
