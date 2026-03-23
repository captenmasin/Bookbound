import type { AppPageProps } from '@/types'
import { usePage } from '@inertiajs/vue3'

export function usePwa () {
    const page = usePage<AppPageProps>()
    const app = page.props.app
    const platform = app.native_platform || app.pwa_platform

    const isPwa = app.is_pwa
    const isNative = app.is_native
    const isAndroid = platform === 'android'
    const isIos = platform === 'ios'
    const isMacos = platform === 'macos'
    const capabilities = app.native_capabilities

    return { isPwa, isNative, isAndroid, isIos, isMacos, capabilities }
}
