export type GoodreadsImportFailure = {
    id: number,
    row_number: number,
    title?: string | null,
    author?: string | null,
    reason: string,
    created_at?: string | null,
}

export type GoodreadsImport = {
    id: number,
    source: string,
    status: 'pending' | 'processing' | 'completed' | 'failed',
    original_filename: string,
    batch_id?: string | null,
    total_rows: number,
    processed_rows: number,
    imported_rows: number,
    merged_rows: number,
    skipped_rows: number,
    failed_rows: number,
    blocked_rows: number,
    error_message?: string | null,
    progress_percent: number,
    started_at?: string | null,
    completed_at?: string | null,
    failed_at?: string | null,
    created_at?: string | null,
    failures?: GoodreadsImportFailure[],
    links: {
        show: string,
    },
}
