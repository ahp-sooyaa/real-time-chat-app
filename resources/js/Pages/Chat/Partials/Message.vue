<script setup>
import { usePage, Link, router } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import { ref } from "vue";
import moment from "moment";

const props = defineProps({ message: Object });

const isEditing = ref(false);
const initialMessage = ref(props.message.content);

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

    router.patch(
        route("message.update", message.id), 
        {
            content: message.content,
        },
        {
            onSuccess: () => isEditing.value = false
        }
    );
};
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
                    <span v-if="message.deleted_at" class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                          <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                        </svg>

                        {{ `${message.sender_name} deleted message` }}
                    </span>
                    <span v-else>
                        {{ message.content }}
                    </span>
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
