<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    chatSession: Object,
    member: Object,
    show: Boolean,
});
const emit = defineEmits(["close"]);

const form = useForm({
    nickname: props.member?.pivot.nickname,
});

const updateNickname = () => {
    form.patch(
        route("chatsession.member.update", {
            chatSession: props.chatSession.id,
            member: props.member.id,
        }),
        {
            onSuccess: () => emit("close"),
        }
    );
};

const closeModal = () => {
    emit("close");
};
</script>
<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Set Nickname</h2>

            <div class="mt-6">
                <InputLabel for="nickname" value="Nickname" class="sr-only" />

                <TextInput
                    id="nickname"
                    ref="groupNameInput"
                    v-model="form.nickname"
                    type="text"
                    class="mt-1 block w-full lg:w-3/4"
                    :placeholder="member.name + ' nickname'"
                />

                <InputError :message="form.errors.nickname" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{
                        'opacity-25': form.processing,
                    }"
                    :disabled="form.processing"
                    @click="updateNickname"
                >
                    Set
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
