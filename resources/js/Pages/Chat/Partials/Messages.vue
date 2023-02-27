<script setup>
import CreateMessageForm from "./CreateMessageForm.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { usePage } from "@inertiajs/vue3";
import moment from "moment";

const props = defineProps({ messages: Object, chatSession: Object });

const updated = (messageContent) => {
    props.messages.push({
        sender_id: usePage().props.auth.user.id,
        sender_name: usePage().props.auth.user.name,
        content: messageContent,
        sent_by: "user",
        created_at: moment().format(),
    });
};

const isCurrentUser = (message) => {
    return message.sender_id == usePage().props.auth.user.id;
};

const senderName = (message) => {
    return message.sender_nickname ?? message.sender_name;
};
</script>

<template>
    <div>
        <div v-if="messages.length">
            <div
                v-for="(message, index) in messages"
                :key="index"
                class="[&:not(:first-child)]:mt-3"
            >
                <div
                    v-if="message.sent_by == 'user'"
                    class="flex flex-col"
                    :class="
                        isCurrentUser(message) ? 'items-end' : 'items-start'
                    "
                >
                    <div
                        class="flex items-center"
                        :class="{ 'flex-row-reverse': isCurrentUser(message) }"
                    >
                        <h1 class="font-bold">
                            {{
                                isCurrentUser(message)
                                    ? "me"
                                    : senderName(message)
                            }}
                        </h1>
                        <span class="text-xs mx-2">
                            {{ moment(message.created_at).format("LT") }}
                        </span>
                    </div>

                    <div
                        :class="[
                            { 'flex-row-reverse': !isCurrentUser(message) },
                            'flex items-center',
                        ]"
                    >
                        <Dropdown
                            :align="isCurrentUser(message) ? 'right' : 'left'"
                            width="48"
                        >
                            <template #trigger>
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
                                    Edit
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
                                    Delete
                                </DropdownLink>
                            </template>
                        </Dropdown>

                        <p
                            class="text-gray-700 text-sm bg-gray-100 inline-block rounded-lg px-4 py-1"
                        >
                            {{ message.content }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 text-center">
                    <div class="text-xs">
                        {{ moment(message.created_at).format("LT") }}
                    </div>
                    {{ message.content }}
                </div>
            </div>
        </div>
        <div v-else class="text-center text-sm text-gray-400">
            No message yet
        </div>

        <CreateMessageForm
            :chat-session-id="chatSession.id"
            @sent="updated"
        ></CreateMessageForm>
    </div>
</template>
