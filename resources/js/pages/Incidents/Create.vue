<script setup lang="ts">
import { ref, watch, computed, onUnmounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Shield, AlertCircle, Loader2, User, Mail, Briefcase, X, File as FileIcon, Image as ImageIcon } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
}

interface Severity {
    id: number;
    name: string;
    level: number;
}

interface UserProfile {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    categories: Category[];
    severities: Severity[];
    users: UserProfile[];
    csirtUsers: { id: number; name: string }[];
}>();

const page = usePage();
const permissions = computed(() => page.props.auth.permissions);
const currentUser = computed(() => page.props.auth.user);

const breadcrumbs = [
    { title: 'Incidents', href: '/incidents' },
    { title: 'Log Incident', href: '/incidents/create' },
];

// Reporter Mode tracking natively
const reporterMode = ref<'internal' | 'external'>('internal');

// Reusable standard validation structures pushed via API wrapper
const form = useForm({
    subject: '',
    description: '',
    category_id: '' as number | '',
    severity_id: '' as number | '',
    reporter_id: '' as number | '',
    reporter_email: '',
    assigned_to: '' as number | '',
    attachments: [] as File[],
});

// Reactively lock file binary arrays ensuring direct submission capabilities
const isDragging = ref(false);
const previews = ref<Map<File, string>>(new Map());

onUnmounted(() => {
    // Clean up memory leaks from ObjectURLs when unmounting 
    previews.value.forEach(url => URL.revokeObjectURL(url));
});

const getPreviewSource = (file: File) => {
    if (file.type.startsWith('image/')) {
        if (!previews.value.has(file)) {
            previews.value.set(file, URL.createObjectURL(file));
        }
        return previews.value.get(file);
    }
    return undefined;
};

const addFiles = (files: File[]) => {
    // Basic local boundary enforcing 5 limit before backend validates it safely
    const remainingSlots = 5 - form.attachments.length;
    if (remainingSlots > 0) {
        form.attachments.push(...files.slice(0, remainingSlots));
    }
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
    if (e.dataTransfer?.files) {
        addFiles(Array.from(e.dataTransfer.files));
    }
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        addFiles(Array.from(target.files));
    }
    // Reset so same file can trigger change event if removed and re-added
    target.value = '';
};

const removeFile = (index: number) => {
    const file = form.attachments[index];
    if (previews.value.has(file)) {
        URL.revokeObjectURL(previews.value.get(file)!);
        previews.value.delete(file);
    }
    form.attachments.splice(index, 1);
};

// Watch mode closely purging the inactive field so rigid constraints never conflict remotely
watch(reporterMode, (newMode) => {
    if (newMode === 'internal') {
        form.reporter_email = '';
    } else {
        form.reporter_id = '';
    }
});

const submit = () => {
    form.post('/incidents', {
        preserveScroll: true,
        onError: () => {
            // Automatically handled by form.errors in template 
        }
    });
};
</script>

<template>
    <Head title="Log Incident" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
            
            <div class="flex items-center gap-4">
                <Link href="/incidents" class="text-sm font-medium text-muted-foreground hover:text-foreground">
                    &larr; Back
                </Link>
                <h2 class="text-2xl font-bold tracking-tight">Log a New Incident</h2>
            </div>
            
            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <!-- Reporter Section -->
                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <User class="h-5 w-5 text-primary" />
                            <h3 class="text-lg font-medium">Reporter Information</h3>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Specify who reported or raised this security incident initially.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Mode Toggle -->
                        <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                            <label class="text-sm font-medium leading-none whitespace-nowrap">Reporter Source:</label>
                            <div class="inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground w-full sm:w-auto">
                                <button 
                                    type="button" 
                                    @click="reporterMode = 'internal'"
                                    :class="[
                                        'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium transition-all w-1/2 sm:w-auto',
                                        reporterMode === 'internal' ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/50 text-muted-foreground'
                                    ]"
                                >
                                    Internal Employee
                                </button>
                                <button 
                                    type="button" 
                                    @click="reporterMode = 'external'"
                                    :class="[
                                        'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium transition-all w-1/2 sm:w-auto',
                                        reporterMode === 'external' ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/50 text-muted-foreground'
                                    ]"
                                >
                                    External / Anonymous Email
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic Inputs -->
                        <div class="rounded-lg border border-dashed p-4 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                            <!-- Mode 1: Internal User Selection -->
                            <div v-if="reporterMode === 'internal'" class="space-y-2">
                                <label for="reporter_id" class="text-sm font-medium flex items-center gap-1.5">
                                    <User class="h-4 w-4 text-muted-foreground" />
                                    Select Active User Profile
                                </label>
                                <select
                                    id="reporter_id"
                                    v-model="form.reporter_id"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="" disabled>Search or select an internal user...</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground mt-1 select-none">Leave totally empty if you want your own account logged as the default reporter natively via the system.</p>
                                <p v-if="form.errors.reporter_id" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ form.errors.reporter_id }}
                                </p>
                            </div>

                            <!-- Mode 2: External Email Input -->
                            <div v-if="reporterMode === 'external'" class="space-y-2">
                                <label for="reporter_email" class="text-sm font-medium flex items-center gap-1.5">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    External Email Address
                                </label>
                                <input
                                    id="reporter_email"
                                    v-model="form.reporter_email"
                                    type="email"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="e.g., security-alert@partner.com"
                                />
                                <p class="text-xs text-muted-foreground mt-1 select-none">This email will receive updates regarding the incident if external notification logic is enabled.</p>
                                <p v-if="form.errors.reporter_email" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ form.errors.reporter_email }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Main Details Section -->
                <div class="rounded-xl border bg-card shadow-sm dark:border-gray-800">
                    <div class="border-b px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-primary" />
                            <h3 class="text-lg font-medium">Incident Details</h3>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Provide clear, actionable details describing the security event.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Subject Field -->
                        <div class="space-y-2">
                            <label for="subject" class="text-sm font-medium leading-none required:after:text-destructive after:content-['*'] after:ml-0.5">
                                Subject
                            </label>
                            <input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="E.g., Unauthorized access detected on App-Server-02"
                                required
                            />
                            <p v-if="form.errors.subject" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" /> {{ form.errors.subject }}
                            </p>
                        </div>

                        <!-- Categorization Grid -->
                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Category Field -->
                            <div class="space-y-2">
                                <label for="category" class="text-sm font-medium leading-none">
                                    Category Classification
                                </label>
                                <select
                                    id="category"
                                    v-model="form.category_id"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="" disabled>Select a category...</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Severity Field -->
                            <div class="space-y-2">
                                <label for="severity" class="text-sm font-medium leading-none">
                                    Initial Severity Estimate
                                </label>
                                <select
                                    id="severity"
                                    v-model="form.severity_id"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="" disabled>Select a severity level...</option>
                                    <option v-for="severity in severities" :key="severity.id" :value="severity.id">
                                        {{ severity.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.severity_id" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ form.errors.severity_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="space-y-2 mt-4">
                            <label for="description" class="text-sm font-medium leading-none required:after:text-destructive after:content-['*'] after:ml-0.5">
                                Description & Technical Context
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="8"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Include logs, IP addresses, timelines, or steps to reproduce if applicable."
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" /> {{ form.errors.description }}
                            </p>
                        </div>
                        
                        <!-- Attachments Field -->
                        <div class="space-y-2 mt-4 pt-4 border-t dark:border-gray-800">
                            <label for="attachments" class="text-sm font-medium leading-none">
                                Evidence & Logs (Optional)
                            </label>
                            
                            <!-- Dynamic Drag and Drop Zone -->
                            <div class="flex items-center justify-center w-full relative">
                                <label 
                                    for="attachments" 
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                                    :class="isDragging ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'bg-gray-50 border-gray-300 hover:bg-gray-100 dark:bg-gray-950/40 dark:border-gray-800 dark:hover:bg-gray-900/60'"
                                    @dragover="handleDragOver"
                                    @dragleave="handleDragLeave"
                                    @drop="handleDrop"
                                >
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-muted-foreground pointer-events-none">
                                        <svg class="w-8 h-8 mb-3" :class="isDragging ? 'text-primary' : ''" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                        </svg>
                                        <p class="mb-2 text-sm"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs">PNG, JPG, PDF, TXT, CSV, ZIP (Max 5 Files, 10MB each)</p>
                                    </div>
                                    <input 
                                        id="attachments" 
                                        type="file" 
                                        multiple 
                                        class="hidden" 
                                        @change="handleFileChange" 
                                        accept=".jpg,.jpeg,.png,.pdf,.csv,.txt,.zip" 
                                    />
                                </label>
                            </div>
                            
                            <!-- Render selected files securely dynamically with previews -->
                            <div v-if="form.attachments.length > 0" class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div v-for="(file, index) in form.attachments" :key="index" class="relative flex items-center gap-3 p-3 text-sm rounded-md bg-muted/50 border dark:border-gray-800 pr-10 hover:bg-muted/80 transition-colors group">
                                    
                                    <!-- Thumbnail or Icon Wrapper -->
                                    <div class="shrink-0 h-10 w-10 flex items-center justify-center overflow-hidden rounded bg-background border border-border shadow-sm">
                                        <img v-if="getPreviewSource(file)" :src="getPreviewSource(file)" alt="Preview" class="h-full w-full object-cover select-none" />
                                        <ImageIcon v-else-if="file.type.startsWith('image/')" class="h-5 w-5 text-muted-foreground" />
                                        <FileIcon v-else class="h-5 w-5 text-muted-foreground" />
                                    </div>

                                    <div class="flex flex-col overflow-hidden leading-tight">
                                        <span class="truncate font-medium text-foreground" :title="file.name">{{ file.name }}</span>
                                        <span class="text-xs text-muted-foreground font-mono mt-0.5">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
                                    </div>

                                    <!-- Delete File Capability -->
                                    <button 
                                        type="button" 
                                        @click="removeFile(index)"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-md text-muted-foreground hover:bg-destructive/10 hover:text-destructive focus:outline-none focus:ring-2 focus:ring-destructive/40 transition-colors"
                                        title="Remove file"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Errors scoped to files dynamically -->
                            <p v-if="form.errors.attachments" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" /> {{ form.errors.attachments }}
                            </p>
                            <!-- Deep Array validations tracking mapped precisely -->
                            <template v-for="(error, key) in form.errors" :key="key">
                                <p v-if="key.toString().startsWith('attachments.')" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ error }}
                                </p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Assignment Section (Restricted to CSIRT/Admin) -->
                <div v-if="permissions?.can_manage_reports" class="rounded-xl border border-indigo-200 bg-indigo-50/30 shadow-sm dark:border-indigo-900/30 dark:bg-indigo-900/10">
                    <div class="border-b border-indigo-100 px-6 py-4 dark:border-indigo-900/50">
                        <div class="flex items-center gap-2">
                            <Briefcase class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-200">Incident Assignment Tracking</h3>
                        </div>
                        <p class="text-sm text-indigo-600/80 dark:text-indigo-300">Delegate this security incident securely to an active responder (Internal).</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                            <div class="space-y-2 flex-1 relative w-full">
                                <label for="assigned_to" class="text-sm font-medium leading-none text-indigo-900 dark:text-indigo-300">
                                    Primary Handler / Assignee
                                </label>
                                <select
                                    id="assigned_to"
                                    v-model="form.assigned_to"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-indigo-200 bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-950 dark:border-indigo-800"
                                >
                                    <option value="" selected>Unassigned (Pending Triage)</option>
                                    <option v-for="handler in csirtUsers" :key="handler.id" :value="handler.id">
                                        {{ handler.name }} 
                                        {{ currentUser?.id === handler.id ? '(You)' : '' }}
                                    </option>
                                </select>
                                <p v-if="form.errors.assigned_to" class="text-[0.8rem] font-medium text-destructive mt-1 flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> {{ form.errors.assigned_to }}
                                </p>
                            </div>
                            
                            <!-- One-Click Self Assign -->
                            <button
                                type="button"
                                @click="form.assigned_to = currentUser?.id"
                                class="inline-flex h-10 shrink-0 items-center justify-center rounded-md border border-indigo-300 bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-800 transition-colors hover:bg-indigo-200 dark:border-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-800/80 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Self-Assign Incident
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Footer -->
                <div class="flex items-center justify-end px-6 py-4 border-t bg-muted/40 dark:border-gray-800 rounded-xl rounded-t-none -mt-6">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-6 py-2"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Log Incident
                    </button>
                </div>
            </form>
            
        </div>
    </AppLayout>
</template>
