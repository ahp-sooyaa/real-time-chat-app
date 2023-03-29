<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Message from "./Partials/Message.vue";
import CreateMessageForm from "./Partials/CreateMessageForm.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, computed, ref } from "vue";

const props = defineProps({
    messages: Object,
    chatSession: Object,
    participants: Object,
});

const activeParticipants = ref([]);

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
        .listen("MessageSent", ({ message }) => {
            props.messages.push(message);

            // mark as read (!need to think about this more)
            router.reload();
        })
        .listen("MessageUpdated", ({ message }) => {
            props.messages.map((originalMessage) => {
                if (originalMessage.id == message.id) {
                    originalMessage.content = message.content;
                    originalMessage.updated_at = message.updated_at;
                }
            });
        })
        .listen("MessageDeleted", ({ message }) => {
            props.messages.map((originalMessage) => {
                if (originalMessage.id == message.id) {
                    originalMessage.content = `${message.sender_name} deleted message`;
                    originalMessage.updated_at = message.updated_at;
                    originalMessage.deleted_at = message.deleted_at;
                }
            });
        });
});

onUnmounted(() => {
    window.Echo.leave("chatsession." + props.chatSession.id);
});
</script>

<template>
    <Head :title="chatSessionName" />

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
            </div>
        </template>

        <div class="py-12">
            <div
                class="flex flex-col lg:flex-row lg:space-x-5 items-start max-w-7xl mx-auto sm:px-6 lg:px-8"
            >
                <div
                    v-if="chatSession.is_group"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3 p-6 w-full lg:w-1/3"
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

                    <form
                        v-if="chatSession.is_group"
                        @submit.prevent="addMember"
                        class="flex items-start space-x-2 ml-auto mt-5"
                    >
                        <div class="flex-1">
                            <input
                                v-model="memberForm.email"
                                type="email"
                                placeholder="email"
                                class="w-full border-gray-300 rounded-md mr-2 text-sm"
                            />
                            <div
                                v-if="memberForm.errors.email"
                                class="text-sm mt-1 text-red-600"
                            >
                                {{ memberForm.errors.email }}
                            </div>
                        </div>
                        <button
                            class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm"
                        >
                            Add Member
                        </button>
                    </form>
                </div>

                <div
                    class="flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg mt-3 w-full lg:w-2/3"
                >
                    <div class="flex flex-col p-6 text-gray-900">
                        <div v-if="messages.length">
                            <Message
                                v-for="(message, index) in messages"
                                :key="index"
                                :message="message"
                                class="[&:not(:first-child)]:mt-3"
                            />
                        </div>
                        <div v-else class="text-center text-sm text-gray-400">
                            No message yet
                        </div>

                        <CreateMessageForm
                            :chat-session-id="chatSession.id"
                        ></CreateMessageForm>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
