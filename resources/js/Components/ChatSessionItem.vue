<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

const props = defineProps({ chatSession: Object });

let latestMessage = ref(props.chatSession.chats[0]?.message);

onMounted(() => {
    window.Echo.private("chat.session." + props.chatSession.id).listen(
        "MessageSent",
        (e) => {
            latestMessage.value = e.chat.message;
        }
    );
});
</script>

<template>
    <Link :href="route('chatsession.show', props.chatSession.id)" class="block">
        {{ props.chatSession.users[0].name }}
        {{ props.chatSession.id }}
        <div v-if="latestMessage">
            <span class="text-gray-500">latest message:</span>
            {{ latestMessage }}
        </div>
    </Link>
</template>
