<script setup>
import CreateMessageForm from "./CreateMessageForm.vue";
import Message from "./Message.vue";

const props = defineProps({ messages: Object, chatSession: Object });

const updateMessageList = (message) => {
    props.messages.push(message);
};
</script>

<template>
    <div>
        <div v-if="messages.length">
            <Message
                v-for="(message, index) in messages"
                :key="index"
                :initial-message="message.content"
                :message="message"
                class="[&:not(:first-child)]:mt-3"
            />
        </div>
        <div v-else class="text-center text-sm text-gray-400">
            No message yet
        </div>

        <CreateMessageForm
            :chat-session-id="chatSession.id"
            @sent="updateMessageList"
        ></CreateMessageForm>
    </div>
</template>
