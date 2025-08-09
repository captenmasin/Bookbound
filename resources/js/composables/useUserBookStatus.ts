import { useRequest } from '@/composables/useRequest'
import { useRoute } from '@/composables/useRoute'
import { UserBookStatus } from '@/enums/UserBookStatus'
import { Book, BookApiResult } from '@/types/book'
import { router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { toast } from 'vue-sonner'

// eslint-disable-next-line sonarjs/cognitive-complexity
export function useUserBookStatus () {
    type StatusMap = Record<string, UserBookStatus | null>;

    const page = usePage()
    const authed = page.props.auth.check
    const addedBooks = authed
        ? ref<Record<string, UserBookStatus | string>>({ ...page.props.auth.user?.book_identifiers })
        : ref<Record<string, UserBookStatus | string>>({})

    const selectedStatuses = ref<StatusMap>({})
    const addingBooks = ref<string[]>([])
    const addedBookIdentifiers = computed(() => new Set(Object.keys(addedBooks.value)))

    const possibleStatuses = Object.entries(UserBookStatus).map(([key, value]) => ({
        value,
        slug: key,
        label: value
    }))

    function updateStatus (book: Book | BookApiResult, status: string, successCallback?: () => void, errorCallback?: () => void) {
        if (!possibleStatuses.some((s) => s.value === status)) {
            throw new Error(`Invalid status: ${status}`)
        }

        useRequest(useRoute('api.user.books.update_status', book.identifier), 'PATCH', {
            status
        })
            .then((response) => {
                if (response.message) {
                    router.flushAll()
                    toast.success(response.message)
                    if (successCallback) {
                        successCallback()
                    }
                } else {
                    toast.error(response.message || 'Failed to update book status')
                    if (errorCallback) {
                        errorCallback()
                    }
                }
            })
            .catch((error) => {
                toast.error(error.message)
                if (errorCallback) {
                    errorCallback()
                }
            })
    }

    async function addBookToUser (identifier: string, status: UserBookStatus,
        successCallback?: () => void,
        errorCallback?: () => void
    ) {
        addingBooks.value.push(identifier)

        try {
            const response = await useRequest(useRoute('api.books.fetch_or_create', identifier), 'GET')

            if (response?.book?.identifier) {
                const book = response.book
                useRequest(useRoute('api.user.books.store'), 'POST', {
                    identifier: book.identifier,
                    status: status || 'PlanToRead'
                })
                    .then((response) => {
                        if (response.success) {
                            router.flushAll()
                            toast.success(response.message)
                            addedBooks.value[book.identifier] = status
                            addingBooks.value = addingBooks.value.filter((id) => id !== identifier)

                            if (successCallback) {
                                successCallback()
                            }
                        } else {
                            toast.error(response.message || 'Failed to add book')
                            addingBooks.value = addingBooks.value.filter((id) => id !== identifier)
                            if (errorCallback) {
                                errorCallback()
                            }
                        }
                    })
                    .catch((error) => {
                        toast.error(error.message)
                        addingBooks.value = addingBooks.value.filter((id) => id !== identifier)
                        if (errorCallback) {
                            errorCallback()
                        }
                    })
            } else {
                addingBooks.value = addingBooks.value.filter((id) => id !== identifier)
                toast.error('Failed to create or fetch book')
                console.error('Missing identifier in book data:', response)
                if (errorCallback) {
                    errorCallback()
                }
            }
        } catch (error) {
            addingBooks.value = addingBooks.value.filter((id) => id !== identifier)
            toast.error('Failed to create or fetch book')
            console.error('Error creating/fetching book:', error)
        }
    }

    async function removeBookFromUser (book: Book | BookApiResult, successCallback?: () => void) {
        delete addedBooks.value[book.identifier]
        delete selectedStatuses.value[book.identifier]

        useRequest(useRoute('api.user.books.destroy', book.identifier), 'DELETE')
            .then((response) => {
                if (response.success) {
                    router.flushAll()
                    toast.success(response.message)
                    delete addedBooks.value[book.identifier]
                    delete selectedStatuses.value[book.identifier]

                    if (successCallback) {
                        successCallback()
                    }
                } else {
                    toast.error(response.message || 'Failed to remove book')
                }
            })
            .catch((error) => {
                toast.error(error.message)
            })
    }

    onMounted(() => {
        selectedStatuses.value = {
            ...(addedBooks.value as unknown as Record<string, UserBookStatus>)
        }
    })

    return {
        addedBooks,
        addingBooks,
        addBookToUser,
        removeBookFromUser,
        possibleStatuses,
        addedBookIdentifiers,
        selectedStatuses,
        updateStatus
    }
}
