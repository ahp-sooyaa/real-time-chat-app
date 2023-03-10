<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Messages from "./Partials/Messages.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, computed, reactive, ref } from "vue";

const props = defineProps({
    messages: Object,
    chatSession: Object,
    participants: Object,
});

let activeParticipants = ref([]);

let messages = reactive(props.messages);

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

function markAsRead() {
    router.reload();
}

const chatSessionName = computed(() => {
    if (props.chatSession.is_group) {
        return props.chatSession.name;
    }

    let otherParticipant = props.participants.find(
        (user) => user.name != usePage().props.auth.user.name
    );

    return otherParticipant.nickname ?? otherParticipant.name;
});

const isActive = (participant) => {
    return activeParticipants.value.some(
        (activeParticipant) => activeParticipant.name == participant.name
    );
};

onMounted(() => {
    window.Echo.join("chatsession." + props.chatSession.id)
        .here((users) => {
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
        });
});

onUnmounted(() => {
    window.Echo.leave("chatsession." + props.chatSession.id);
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
                        {{ chatSessionName }}
                    </h2>
                    <Dropdown
                        v-if="chatSession.is_group"
                        align="left"
                        width="48"
                    >
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
                                <!-- this should block button for normal chat and record that people in blocked table -->
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
                <form
                    v-if="chatSession.is_group"
                    @submit.prevent="addMember"
                    class="ml-auto"
                >
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
                        v-if="memberForm.errors.email"
                        class="text-sm mt-1 text-red-600"
                    >
                        {{ memberForm.errors.email }}
                    </div>
                </form>
            </div>
        </template>

        <div class="py-12">
            <div
                class="flex items-start space-x-5 max-w-7xl mx-auto sm:px-6 lg:px-8"
            >
                <div
                    v-if="chatSession.is_group"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3 p-6"
                >
                    <h1 class="text-gray-900">Group members</h1>
                    <div
                        v-for="participant in participants"
                        class="text-gray-700 text-sm"
                    >
                        <span
                            v-if="isActive(participant)"
                            class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 mr-1"
                        ></span
                        >{{ participant.nickname ?? participant.name }}
                    </div>
                </div>

                <div
                    class="flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3"
                >
                    <div class="flex flex-col p-6 text-gray-900">
                        <Messages
                            :messages="messages"
                            :chat-session="chatSession"
                        ></Messages>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
