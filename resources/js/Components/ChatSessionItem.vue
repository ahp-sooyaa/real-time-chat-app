<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";

const props = defineProps({ chatSession: Object });

let latestMessage = ref(props.chatSession.messages[0]);
let newMessageCount = ref(props.chatSession.messages_count);

onMounted(() => {
    window.Echo.join("chatsession." + props.chatSession.id).listen(
        "MessageSent",
        (e) => {
            latestMessage.value = e.message;
            newMessageCount.value++;
        }
    );
});

onUnmounted(() => {
    window.Echo.leave("chatsession." + props.chatSession.id);
});
</script>

<template>
    <Link
        :href="route('chatsession.show', chatSession.id)"
        class="block bg-gray-100 p-3 rounded-xl"
    >
        <div class="flex space-x-1">
            <span
                v-if="
                    latestMessage &&
                    !latestMessage.read_at &&
                    latestMessage.sender_id != $page.props.auth.user.id
                "
                class="relative flex h-2 w-2 mt-2"
            >
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"
                ></span>
                <span
                    class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"
                ></span>
            </span>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <h1 class="text-gray-900 font-semibold">
                        {{
                            chatSession.type == "group"
                                ? chatSession.name
                                : chatSession.users[0].name
                        }}
                    </h1>
                    <span
                        class="bg-gray-900 rounded-full text-xs text-white px-2 py-0.5"
                    >
                        {{ newMessageCount ?? "0" }}
                    </span>
                </div>
                <div v-if="latestMessage" class="text-gray-500 text-sm">
                    {{ latestMessage?.content }}
                </div>
                <div v-else class="text-gray-500 text-sm">No message yet!</div>
            </div>
        </div>
    </Link>
</template>
