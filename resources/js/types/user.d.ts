import { UserBookStatus } from '@/enums/UserBookStatus'

export type UserSubscription = {
    subscribed: boolean;
    plan: 'free' | 'pro';
    limits: {
        max_books: number | null;
        private_notes: boolean;
        custom_covers: boolean;
    };
    books: {
        count: number;
        max: number | null;
        remaining: number | null;
    };

    allow_private_notes: boolean;
    allow_custom_covers: boolean;
    can_add_book: boolean;
};

export type UserSettings = {
    profile?: {
        colour?: string;
        is_private?: boolean;
    };
    [key: string]: any;
};

export type User = {
    id: number;
    permissions: string[];
    name: string;
    username: string;
    colour: string;
    email?: string;
    avatar?: string;
    email_verified: boolean;
    settings?: UserSettings;
    book_identifiers?: Record<string, UserBookStatus>;
    subscription: UserSubscription;
};

export type PublicUser = {
    id: number;
    name: string;
    username: string;
    avatar?: string;
    colour: string;
    books_count: number;
    books_read_count: number;
};

export type UserPasskey = {
    id: number;
    name: string;
    last_used_at: string;
};
