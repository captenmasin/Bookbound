import { UserBookStatus } from '@/enums/UserBookStatus'

export type User = {
    id: number;
    permissions: string[];
    name: string;
    username: string;
    email?: string;
    avatar?: string;
    email_verified: boolean,
    settings?: Record<string, any>;
    subscribed: boolean;
    book_identifiers?: Record<string, UserBookStatus>;
}

export type UserPasskey = {
    id: number,
    name: string,
    last_used_at: string,
}
