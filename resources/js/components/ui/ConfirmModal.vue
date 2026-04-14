<script setup lang="ts">
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    danger?: boolean;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

const isOpen = computed({
    get: () => props.open,
    set: (val) => emit('update:open', val),
});

const handleCancel = () => {
    if (props.loading) return;
    emit('cancel');
    isOpen.value = false;
};

const handleConfirm = () => {
    if (props.loading) return;
    emit('confirm');
};

const confirmButtonClass = computed(() => {
    if (props.danger) {
        return 'inline-flex h-10 items-center justify-center rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 ring-offset-background';
    }
    return 'inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 ring-offset-background';
});

const cancelButtonClass = 'inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 ring-offset-background';
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent 
            class="sm:max-w-[425px]" 
            @pointer-down-outside="loading ? $event.preventDefault() : null" 
            @escape-key-down="loading ? $event.preventDefault() : null" 
            @interact-outside="loading ? $event.preventDefault() : null"
        >
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description" class="mt-2 text-sm text-muted-foreground whitespace-pre-wrap">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-5 sm:justify-end flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                <button
                    type="button"
                    :class="cancelButtonClass"
                    @click="handleCancel"
                    :disabled="loading"
                >
                    {{ cancelText || 'Cancel' }}
                </button>
                <button
                    type="button"
                    class="sm:mt-0 mt-2" 
                    :class="confirmButtonClass"
                    @click="handleConfirm"
                    :disabled="loading"
                >
                    <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                    {{ confirmText || 'Confirm' }}
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
