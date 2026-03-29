<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Shield, BookOpen, AlertTriangle, Inbox, Users, Activity, FileStack, Tags, KeyRound } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

// Extract permissions directly from Inertia payload bound natively via middleware
const page = usePage();
const permissions = computed(() => page.props.auth.permissions);

// Generate nav dynamically reacting to permissions
const mainNavItems = computed(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: Activity,
        },
    ];

    if (permissions.value?.can_manage_reports) {
        items.push({
            title: 'Inbox',
            href: '/inbox',
            icon: Inbox,
        });
        items.push({
            title: 'Incidents',
            href: '/incidents',
            icon: AlertTriangle,
        });
    } else {
        items.push({
            title: 'Viewing Area',
            href: '/incidents',
            icon: BookOpen,
        });
    }

    if (permissions.value?.can_manage_reference_data) {
        items.push({
            title: 'Categories & Severities',
            href: '/admin/reference-data',
            icon: Tags,
        });
    }

    if (permissions.value?.is_admin) {
        items.push({
            title: 'Users',
            href: '/admin/users',
            icon: Users,
        });
        items.push({
            title: 'Roles',
            href: '/admin/roles',
            icon: KeyRound,
        });
        items.push({
            title: 'Audit Log',
            href: '/audit-logs',
            icon: FileStack,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r shadow-sm">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-primary/5">
                        <Link href="/dashboard" class="flex gap-3">
                            <Shield class="text-primary h-6 w-6" />
                            <div class="flex flex-col gap-0.5 mt-0.5 leading-none">
                                <span class="font-bold text-base tracking-tight text-primary">CSIRT</span>
                                <span class="text-[10px] tracking-widest uppercase font-semibold text-muted-foreground">Internal System</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        
        <SidebarSeparator class="opacity-50 mx-2" />

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
