import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { UserPermission } from '@/enums/UserPermission'
import { useRoute } from '@/composables/useRoute'
import { useMemoize } from '@vueuse/core'

export function useAuthedUser () {
    const page = usePage()

    const auth = computed(() => page.props.auth || {})
    const authedUser = computed(() => auth.value.user || null)
    const authed = computed(() => auth.value.check || false)
    const subscribedToPro = computed(() => authedUser.value.subscription?.subscribed)

    const permissions = useMemoize(() => authedUser.value?.permissions || [])

    function hasPermission (permission: string | UserPermission): boolean {
        return permissions().includes(permission)
    }

    function logout () {
        router.flushAll()
        router.post(useRoute('logout'))
        window.location.href = useRoute('login')
    }

    return {
        hasPermission,
        subscribedToPro,
        authedUser,
        authed,
        logout
    }
}
