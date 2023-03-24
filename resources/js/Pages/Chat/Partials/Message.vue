<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import { ref } from "vue";
import moment from "moment";
import axios from "axios";

const props = defineProps({ initialMessage: String, message: Object });
// const emit = defineEmits(["deleted"]);

const isEditing = ref(false);
const initialMessage = ref(props.initialMessage);

const isCurrentUser = (message) => {
    return message.sender_id == usePage().props.auth.user.id;
};

const senderName = (message) => {
    return message.sender_nickname ?? message.sender_name;
};

const updateMessage = (message) => {
    if (!message.content) {
        props.message.content = initialMessage.value;

        isEditing.value = false;
        return;
    }

    isEditing.value = false;

    axios.patch(route("message.update", message.id), {
        content: message.content,
    });
};

// const deleteMessage = (message) => {
//     axios.delete(route("message.destroy", message.id)).then((message) => {
//         emit("deleted", message);
//     });
// };
</script>

<template>
    <div>
        <div
            v-if="message.sent_by == 'user'"
            class="flex flex-col"
            :class="isCurrentUser(message) ? 'items-end' : 'items-start'"
        >
            <div
                class="flex items-center"
                :class="{ 'flex-row-reverse': isCurrentUser(message) }"
            >
                <h1 class="font-bold">
                    {{ isCurrentUser(message) ? "me" : senderName(message) }}
                </h1>
                <span class="text-xs mx-2 text-gray-500">
                    {{
                        message.created_at != message.updated_at
                            ? "edited "
                            : ""
                    }}
                    {{ moment(message.updated_at).format("LT") }}
                </span>
            </div>

            <div
                :class="[
                    { 'flex-row-reverse': !isCurrentUser(message) },
                    'flex items-center',
                ]"
            >
                <Dropdown
                    v-if="isCurrentUser(message) && !message.deleted_at"
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
                        <div
                            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                            @click="isEditing = true"
                        >
                            Edit
                        </div>
                        <Link
                            :href="route('message.destroy', message.id)"
                            method="delete"
                            as="button"
                            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                        >
                            Delete
                        </Link>
                    </template>
                </Dropdown>

                <input
                    v-if="isEditing"
                    v-model="message.content"
                    type="text"
                    @keyup.enter="updateMessage(message)"
                />
                <p
                    v-else
                    class="bg-gray-100 inline-block rounded-lg px-4 py-1"
                    :class="
                        message.deleted_at
                            ? 'text-gray-500 tracking-wider text-sm'
                            : 'text-gray-700'
                    "
                >
                    {{
                        message.deleted_at
                            ? `${message.sender_name} deleted message`
                            : message.content
                    }}
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
</template>
