import type { LucideIcon } from 'lucide-vue-next'
import { User } from '@/types/user'

export interface Auth {
    check: boolean;
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    secondaryIcon?: LucideIcon;
    isActive?: boolean;
    mobileOnly?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string
    auth: {
        user: User | null
        check: boolean,
        turnstile: {
            enabled: boolean,
            site_key: string,
        }
    }
    app: {
        name: string
        url: string
        route: string
        domain: string
        storage_url: string
    }
    currentUrl: string
    currentPath: string
    flash: {
        success?: string
        error?: string
        warning?: string
        info?: string
        upgrade_success?: boolean
    }
    sidebarOpen: boolean
};

export type BreadcrumbItemType = BreadcrumbItem;
