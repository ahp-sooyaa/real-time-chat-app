<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import throttle from "lodash/throttle";

const props = defineProps({ chatSessionId: Number });
const emit = defineEmits("sent");

const loading = ref(false);
const timer = ref(null);
const typingParticipants = ref([]);

const messageForm = useForm({
    message: "",
    chatSessionId: props.chatSessionId,
});

const sentMessage = () => {
    if (loading.value || !messageForm.message) return;

    emit("sent", messageForm.message);

    messageForm.post(route("message.store"), {
        preserveScroll: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
        onSuccess: () => messageForm.reset("message"),
    });
};

const typing = throttle(() => {
    window.Echo.join("chatsession." + props.chatSessionId).whisper("typing", {
        name: usePage().props.auth.user.name,
    });
}, 1000);

onMounted(() => {
    window.Echo.join("chatsession." + props.chatSessionId).listenForWhisper(
        "typing",
        (e) => {
            if (!typingParticipants.value.includes(e.name)) {
                typingParticipants.value.push(e.name);
            }

            if (timer) clearTimeout(timer);

            timer.value = setTimeout(() => {
                typingParticipants.value = [];
            }, 1000);
        }
    );
});
</script>

<template>
    <div>
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
                    :class="{ 'opacity-25': messageForm.processing }"
                    :disabled="messageForm.processing"
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
</template>
